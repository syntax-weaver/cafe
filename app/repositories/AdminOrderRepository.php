<?php 

class AdminOrderRepository{
    private PDO $pdo;

    public function __construct() {
        $this->pdo = db();
    }

    public function getAllOrders(): array {
        $query = "select o.* , 
                    u.`name` as `customer_name`, 
                    p.`payment_status`, p.`payment_method` 
                    from `orders` o 
                    join `users` u on o.`user_id` = u.`id` 
                    left join `payments` p on o.`id` = p.`order_id`";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->execute();
        $orders = $prep_statement->fetchAll();
        return $orders;
    }

    public function getAllOrdersByStatus(string $status): array {
        $query = "select o.* , 
                    u.`name` as `customer_name`, 
                    p.`payment_status`, p.`payment_method` 
                    from `orders` o 
                    join `users` u on o.`user_id` = u.`id` 
                    left join `payments` p on o.`id` = p.`order_id` 
                    where o.`status` = :status";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':status', $status);
        $prep_statement->execute();
        $orders = $prep_statement->fetchAll();
        return $orders;
    }


    public function getOrderById(int $order_id): array {
        $query = "select o.`id` as `order_id`, o.`status`, o.`total_price`, o.`notes`, o.`created_at`, o.`updated_at`, 
                    u.`name` as `customer_name`, u.`email` as `customer_email`, 
                    oi.`id` as `item_id`, oi.`quantity`, oi.`unit_price`, 
                    v.`name` as `variant_name`, 
                    p.`name` as `product_name`, 
                    c.`name` as `category_name`, 
                    payments.`payment_status`, 
                    (oi.`quantity` * oi.`unit_price`) as `sub_total`
                    from `orders` o 
                    join `users` u on o.`user_id` = u.`id`
                    left join `payments` on o.`id` = payments.`order_id` 
                    left join `order_items` oi on oi.`order_id` = o.`id` 
                    left join `product_variants` v on oi.`variant_id` = v.`id` 
                    left join `products` p on v.`product_id` = p.`id` 
                    left join `categories` c on p.`category_id` = c.`id` 
                    where o.`id` = :order_id
                    ";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':order_id', $order_id);
        $prep_statement->execute();
        $orders = $prep_statement->fetchAll();
        return $orders;
    }

    public function updateOrderStatus(int $order_id, string $new_status): array {
        if($new_status === 'cancelled') {
            
            try{
                $this->pdo->beginTransaction();
                // change the status
                $query = "update `orders` set `status` = :new_status where `id` = :order_id and `status` = 'pending'";
                $prep_statement = $this->pdo->prepare($query);
                $prep_statement->bindValue(':new_status', $new_status);
                $prep_statement->bindValue(':order_id', $order_id);
                $executed = $prep_statement->execute();
                if(!$executed || $prep_statement->rowCount() !== 1) {
                    throw new Exception('failed to update the status');
                }
                
                // increase the stock again
                $items_quantity_and_variant_id = $this->getItemQuantityAndVariantId($order_id);
                if(!$items_quantity_and_variant_id) {
                    throw new Exception('failed to fetch order item quantity and variant');
                }
                
                $query = "update `product_variants` set `available_quantity` = `available_quantity` + :q where `id` = :variant_id";
                $prep_statement = $this->pdo->prepare($query);
                foreach($items_quantity_and_variant_id as $i) {
                    $prep_statement->bindValue(':q', $i['quantity']);
                    $prep_statement->bindValue(':variant_id', $i['variant_id']);
                    $executed = $prep_statement->execute();
                    if(!$executed) {
                        throw new Exception('failed to update the stock');
                    }   
                }

                // if the order payment was paid then you should refund
                $payment_repo = new PaymentRepository();
                $order_payment_status = $payment_repo->getPaymentStatusByOrderId($order_id);
                if($order_payment_status === 'paid') {
                    $query = "update `payments` set `payment_status` = 'refunded' where `order_id` = :order_id and `payment_status` = 'paid'";
                    $prep_statement = $this->pdo->prepare($query);
                    $prep_statement->bindValue(':order_id', $order_id);
                    $executed = $prep_statement->execute();
                    if(!$executed || $prep_statement->rowCount() !== 1) {
                        throw new Exception('failed to refund. try again');
                    }
                }
                
                $this->pdo->commit();
                return [
                    'status' => true, 
                    'message' => 'order status updated successfully'
                ];
            }catch(Exception $e) {
                $this->pdo->rollBack();
                return [
                    'status' => false, 
                    'message' => $e->getMessage()
                ];
            }
        } else {
            $query = "update `orders` set `status` = :new_status where `id` = :order_id";
            $prep_statement = $this->pdo->prepare($query);
            $prep_statement->bindValue(':new_status', $new_status);
            $prep_statement->bindValue(':order_id', $order_id);
            $executed = $prep_statement->execute();
            if(!$executed || $prep_statement->rowCount() !== 1) {
                return ['status' => false, 'message' => 'unable to update the order status'];
            } 
            return ['status' => true, 'message' => 'order status updated successfully'];
        }
    }

    public function getItemQuantityAndVariantId(int $order_id): ?array {
        $query = "select o.`status`, 
                    oi.`quantity`, `variant_id` 
                    from `orders` o 
                    join `order_items` oi on oi.`order_id` = o.`id`
                    where o.`id` = :order_id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':order_id', $order_id);
        $prep_statement->execute();
        $data = $prep_statement->fetchAll();
        if(!$data) {
            return null;
        }
        return $data;
    }

    public function getOrderStatus(int $order_id): ?string {
        $query = "select `status` from orders where `id` = :order_id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':order_id', $order_id);
        $prep_statement->execute();
        $status_col = $prep_statement->fetch();
        if(!$status_col) {
            return null;
        }
        return $status_col['status'];
    }



}