<?php 

class OrderRepository {
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = db();
    }

    public function getAll(): array{
        $user_id = user()['id'];
        $query = "select o.*, p.`payment_status`, p.`payment_method` 
                    from orders o 
                    left join payments p on o.`id` = p.`order_id`
                    where `user_id` = :user_id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':user_id', $user_id);
        $prep_statement->execute();
        $orders = $prep_statement->fetchAll();
        return $orders;
    }

    public function getOrderById(int $id): ?array {
        $query = "select * from `orders` where `id` = :id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $order = $prep_statement->fetch();
        return $order ?: null;
    }

    public function updateOrderNotes(int $order_id, string $new_notes): bool {
        $query = "update `orders` set `notes` = :notes where `id` = :id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $order_id);
        $prep_statement->bindValue(':notes', $new_notes);
        $updated = $prep_statement->execute();
        return $updated;
    }

    public function getOrderItems(int $order_id): array {
        
        $query = "select o.`id` as `order_id`, o.`status` as `order_status`, o.`total_price` as `order_total_price`, o.`notes` as `order_notes`, 
                    oi.`id` as `item_id`, oi.`quantity`, oi.`unit_price`, oi.`variant_id`, 
                    v.`name` as `variant_name`,
                    p.`name` as `product_name`, 
                    c.`name` as `category_name`,
                    case 
                        when p.`deleted_at` is not null then 0 
                        when v.`deleted_at` is not null then 0 
                        when v.`available_quantity` <= 0 then 0
                        else 1
                    end as `is_available` 
                    from `orders` o  
                    left join `order_items` oi on o.`id` = oi.`order_id` 
                    left join `product_variants` v on oi.`variant_id` = v.`id` 
                    left join `products` p on v.`product_id` = p.`id` 
                    left join `categories` c on p.`category_id` = c.`id`
                    where `order_id` = :order_id 
                    ";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':order_id', $order_id);
        $prep_statement->execute();
        $items = $prep_statement->fetchAll();
        return $items;
    }

    public function getItemById(int $id): ?array {
        $query = "select oi.`id`, oi.`order_id`, oi.`quantity`, oi.`unit_price`, oi.`variant_id`, 
                    v.`price` as `current_price`, 
                    case 
                        when v.`deleted_at` is not null then 0 
                        when p.`deleted_at` is not null then 0 
                        when c.`deleted_at` is not null then 0 
                        when v.available_quantity <= 0 then 0 
                        else 1
                    end as `is_available`
                    from `order_items` oi 
                    left join `product_variants` v on oi.`variant_id` = v.`id` 
                    left join `products` p on v.`product_id` = p.`id` 
                    left join `categories` c on p.`category_id` = c.`id` 
                    where oi.`id` = :id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $item = $prep_statement->fetch();
        return $item ?: null;
    }

    public function incrementItem(int $id): bool {
        try {
            $this->pdo->beginTransaction();

            // get the item 
            $item = $this->getItemById($id);
            if(!$item) {
                throw new Exception();
            }

            // check the availability 
            if(!$item['is_available']) {
                throw new Exception('item unavailable currently');
            }

            // in case of different prices, create new item
            if((float)$item['unit_price'] !== (float)$item['current_price']) {
                $created = $this->createNewItem($item['order_id'], 1, $item['current_price'], $item['variant_id']);
                if(!$created){
                    throw new Exception();
                }   
            } else {
                // increment
                $increment_query = "update `order_items` set `quantity` = `quantity` + 1 where `id` = :id";
                $prep_statement = $this->pdo->prepare($increment_query);
                $prep_statement->bindValue(':id', $id);
                $executed = $prep_statement->execute();
                if(!$executed) {
                    throw new Exception();
                }
            }

            // update the total price
            $update_total_query = "update `orders` set `total_price` = `total_price` + :current_price where `id` = :id";
            $prep_statement = $this->pdo->prepare($update_total_query);
            $prep_statement->bindValue(':id', $item['order_id']);
            $prep_statement->bindValue(':current_price', $item['current_price']);
            $updated = $prep_statement->execute();
            if(!$updated) {
                throw new Exception();
            }
            // update variant stock
            $update_query = "update `product_variants` set `available_quantity` = `available_quantity` - 1 where `id` = :id and `available_quantity` > 0";
            $prep_statement = $this->pdo->prepare($update_query);
            $prep_statement->bindValue(':id', $item['variant_id']);
            $updated = $prep_statement->execute();
            if($prep_statement->rowCount() !== 1) {
                throw new Exception();
            }
            $this->pdo->commit();
            return true;
        } catch(Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function decrementItem(int $id): bool {
        try {
            $this->pdo->beginTransaction();

            $item = $this->getItemById($id);
            if(!$item) {
                throw new Exception();
            }
            
            // if quantity = 1 , remove this item
            if($item['quantity'] === 1) {
                $query = "delete from `order_items` where id = :id";
                $prep_statement = $this->pdo->prepare($query);
                $prep_statement->bindValue(':id', $id);
                $removed = $prep_statement->execute();
                
                if(!$removed) {
                    throw new Exception();
                }
            }else {
                // decrement the item
                $query = "update `order_items` set `quantity` = `quantity` - 1 where id = :id";
                $prep_statement = $this->pdo->prepare($query);
                $prep_statement->bindValue(':id', $id);
                $decremented = $prep_statement->execute();
                if(!$decremented) {
                    throw new Exception();
                }
            }

            // update the total price
            $update_total_query = "update `orders` set `total_price` = `total_price` - :unit_price where `id` = :id";
            $prep_statement = $this->pdo->prepare($update_total_query);
            $prep_statement->bindValue(':id', $item['order_id']);
            $prep_statement->bindValue(':unit_price', $item['unit_price']);
            $updated = $prep_statement->execute();
            if(!$updated) {
                throw new Exception();
            }

            // increment the variant stock
            $query = "update `product_variants` set `available_quantity` = `available_quantity` + 1 where id = :id";
            $prep_statement = $this->pdo->prepare($query);
            $prep_statement->bindValue(':id', $item['variant_id']);
            $incremented = $prep_statement->execute();
            if(!$incremented) {
                throw new Exception();
            }

            $this->pdo->commit();
            return true;
        }catch(Exception $e){
            $this->pdo->rollBack();
            return false;
        }
        
    }

    public function createNewItem(int $order_id, int $quantity, float $current_price, int $variant_id): bool {
        $query = "insert into `order_items` (`order_id`, `quantity`, `unit_price`, `variant_id`) 
                    values (:order_id, :q, :current_price, :variant_id)";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':order_id', $order_id);
        $prep_statement->bindValue(':q', $quantity);
        $prep_statement->bindValue(':current_price', $current_price);
        $prep_statement->bindValue(':variant_id', $variant_id);
        $executed = $prep_statement->execute();
        return $executed;
    }

    public function removeItem(int $item_id, int $order_id): bool {
        try{
            $this->pdo->beginTransaction();

            $item = $this->getItemById($item_id);
            if(!$item) {
                throw new Exception();
            }

            // remove item
            $query = "delete from `order_items` where `id` = :id";
            $prep_statement = $this->pdo->prepare($query);
            $prep_statement->bindValue(':id', $item_id);
            $removed = $prep_statement->execute();
            if(!$removed) {
                throw new Exception();
            }

            // update the total price
            $update_total_query = "update `orders` set `total_price` = `total_price` - (:unit_price * :quantity) where `id` = :id";
            $prep_statement = $this->pdo->prepare($update_total_query);
            $prep_statement->bindValue(':id', $item['order_id']);
            $prep_statement->bindValue(':unit_price', $item['unit_price']);
            $prep_statement->bindValue(':quantity', $item['quantity']);
            $updated = $prep_statement->execute();
            if(!$updated) {
                throw new Exception();
            }

            // update the variant stock
            $query = "update `product_variants` set `available_quantity` = `available_quantity` + :q where `id` =:id";
            $prep_statement = $this->pdo->prepare($query);
            $prep_statement->bindValue(':id', $item['variant_id']);
            $prep_statement->bindValue(':q', $item['quantity']);
            $updated = $prep_statement->execute();
            if(!$updated) {
                throw new Exception();
            }

            // if the order is empty , remove the order
            $num_of_items_in_order = $this->getNumberOfItemsInOrderByOrderId($order_id);
            if($num_of_items_in_order <= 0) {
                $removed = $this->removeOrder($order_id);
                if(!$removed) {
                    throw new Exception();
                }
            }

            $this->pdo->commit();
            return true;
        }catch(Exception $e){
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getNumberOfItemsInOrderByOrderId(int $order_id): int {
        $query = " select count(*) as `total` from `order_items` where `order_id` = :order_id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':order_id', $order_id);
        $prep_statement->execute();  
        $items_num = $prep_statement->fetch()['total'];
        return (int)$items_num;
    }

    public function removeOrder(int $id): bool {
        $query = "delete from `orders` where `id` = :id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $removed = $prep_statement->execute();
        return $removed;
    }

    public function getOrderOwner(int $id): ?int{
        $query = "select `user_id` from `orders` where `id` = :id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $data = $prep_statement->fetch();
        if(!$data) {
            return null;
        }
        return (int)$data['user_id'];
    }

    public function getOrderStatus(int $id): ?string{
        $query = "select `status` from `orders` where `id` = :id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $data = $prep_statement->fetch();
        if(!$data) {
            return null;
        }
        return $data['status'];
    }
}