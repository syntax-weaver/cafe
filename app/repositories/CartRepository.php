<?php 

class CartRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getOrCreateByUserId(int $user_id): ?array {
        $select_query = "select * from `carts` where `user_id` = :user_id";
        $prep_statement = $this->pdo->prepare($select_query);
        $prep_statement->bindValue(':user_id', $user_id);
        $prep_statement->execute();
        $cart = $prep_statement->fetch();
        if(!$cart) {
            $insert_query = "insert into `carts` (`user_id`) values (:user_id)";
            $prep_statement = $this->pdo->prepare($insert_query);
            $prep_statement->bindValue(':user_id', $user_id);
            $executed = $prep_statement->execute();
            if(!$executed) {
                return null;
            }
            $prep_statement = $this->pdo->prepare($select_query);
            $prep_statement->bindValue(':user_id', $user_id);
            $prep_statement->execute();
            $cart = $prep_statement->fetch();
        }
        return $cart ?: null;
    }

    public function getCartByUserId(int $user_id): ?array {
        $select_query = "select * from `carts` where `user_id` = :user_id";
        $prep_statement = $this->pdo->prepare($select_query);
        $prep_statement->bindValue(':user_id', $user_id);
        $prep_statement->execute();
        $cart = $prep_statement->fetch();
        return $cart ?: null;
    }

    public function getCartById(int $id): ?array {
        $select_query = "select * from `carts` where `id` = :id";
        $prep_statement = $this->pdo->prepare($select_query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $cart = $prep_statement->fetch();
        return $cart ?: null;
    }

    public function itemExists(int $cart_id, int $variant_id): ?array {
        $query = "select * from `cart_items` where `cart_id` = :cart_id and `variant_id` = :variant_id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':cart_id', $cart_id);
        $prep_statement->bindValue(':variant_id', $variant_id);
        $prep_statement->execute();
        $item = $prep_statement->fetch();
        
        return $item ?: null;
    }



    public function addItem(int $cart_id, int $variant_id): bool {
        $query = "insert into `cart_items` (`cart_id`, `variant_id`, `quantity`) values (:cart_id, :variant_id, :q)";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':cart_id', $cart_id);
        $prep_statement->bindValue(':variant_id', $variant_id);
        $prep_statement->bindValue(':q', 1, PDO::PARAM_INT);
        $executed = $prep_statement->execute();
        return $executed;
    }

    public function incrementItem(int $cart_id, int $variant_id): bool {
        $query = "update `cart_items` set `quantity` = `quantity` + 1 where `cart_id` = :cart_id and `variant_id` = :variant_id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':cart_id', $cart_id);
        $prep_statement->bindValue(':variant_id', $variant_id);
        $executed = $prep_statement->execute();
        return $executed;
    }

    public function incrementByItemId(int $id): bool {
        $query = "update `cart_items` set `quantity` = `quantity` + 1 where `id` = :id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $executed = $prep_statement->execute();
        return $executed;
    }
    public function decrementByItemId(int $id): bool {
        $query = "update `cart_items` set `quantity` = `quantity` - 1 where `id` = :id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $executed = $prep_statement->execute();
        return $executed;
    }

    public function getCartDetailsByCartId(int $cart_id): array {
        $query = "select 
                    ci.`id` as `cart_item_id`, ci.`quantity`, 
                    pv.`id` as `variant_id`, pv.`name` as `variant_name`, pv.`price` as `unit_price`, pv.`product_id` as `product_id`, 
                    p.`name` as `product_name`, 
                    case 
                        when p.`deleted_at` is not null then 0 
                        when pv.`deleted_at` is not null then 0 
                        when pv.`available_quantity` <= 0 then 0
                        else 1
                    end as `is_available`, 
                    case 
                        when ci.`quantity` > pv.`available_quantity` then 0 
                        else 1
                    end as `can_buy`, 
                    (ci.`quantity` * pv.`price`) as `subtotal` 
                    from `cart_items` ci 
                    left join `product_variants` pv on ci.`variant_id` = pv.`id` 
                    left join `products` p on pv.`product_id` = p.`id`
                    where ci.`cart_id` = :cart_id 
                    ";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':cart_id', $cart_id);
        $prep_statement->execute();
        $items = $prep_statement->fetchAll();
        $total = 0;
        if($items) {
            foreach($items as $i) {
                $total += $i['is_available'] ? $i['subtotal'] : 0;
            }
        }
        return [
            'items' => $items, 
            'total' => $total
        ];
    }

    public function getCartItemById(int $id): ?array {
        $query = "select * from `cart_items` where `id` = :id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $item = $prep_statement->fetch();
        return $item ?: null;
    }

    public function deleteItemById(int $id): bool {
        $query = "delete from `cart_items` where `id` = :id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $deleted = $prep_statement->execute();
        return $deleted;
    }
}









