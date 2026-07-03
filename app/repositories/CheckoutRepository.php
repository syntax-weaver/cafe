<?php 

class CheckoutRepository{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createOrder(int $user_id, string $notes, float $total, string $status = 'pending'): ?int {
        $query = "insert into `orders` (`user_id`, `status`, `total_price`, `notes`) 
                    values (:user_id, :status, :total_price, :notes)";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':user_id', $user_id);
        $prep_statement->bindValue(':status', $status);
        $prep_statement->bindValue(':total_price', $total);
        $prep_statement->bindValue(':notes', $notes);
        $prep_statement->execute();
        $id = $this->pdo->lastInsertId();
        return $id ?: null;
    }

    public function createOrderItems(int $order_id, array $items): bool {
        $query = "insert into `order_items` (`order_id`, `variant_id`, `quantity`, `unit_price`) 
                    values (:order_id, :variant_id, :q, :unit_price)";
        $prep_statement = $this->pdo->prepare($query);
        foreach($items as $i) {
            $prep_statement->bindValue(':order_id', $order_id);
            $prep_statement->bindValue(':variant_id', $i['variant_id']);
            $prep_statement->bindValue(':q', $i['quantity']);
            $prep_statement->bindValue(':unit_price', $i['unit_price']);
            $executed = $prep_statement->execute();
            if(!$executed) {
                return false;
            }
        }
        return true;
    }

    public function placeOrder(int $user_id, string $payment_method , string $notes = '', string $status = 'pending'): array {
        $order_query = "insert into `orders` (`user_id`, `status`, `total_price`, `notes`) 
                    values (:user_id, :status, :total_price, :notes)";
        $order_item_query = "insert into `order_items` (`order_id`, `variant_id`, `quantity`, `unit_price`) 
                    values (:order_id, :variant_id, :q, :unit_price)";
        
        try {
            $this->pdo->beginTransaction();

            $cart_repo = new CartRepository($this->pdo);
            $cart = $cart_repo->getCartByUserId($user_id);
            if(!$cart) {
                throw new Exception('no cart for the current user');
                
            }
            
            $details = $cart_repo->getCartDetailsByCartId($cart['id']);
            if(empty($details['items'])) {
                throw new Exception('cart is already empty');
            }

            // check availability and stock
            foreach($details['items'] as $i) {
                if(!$i['is_available']) {
                    throw new Exception('Some cart items are unavailable.Remove them before checkout');
                }
                if(!$i['can_buy']) {
                    throw new Exception($i['variant_name'] . '(' . $i['product_name'] . ') - maximum available quantity reached');
                }
            }

            // create the order row
            $prep_statement = $this->pdo->prepare($order_query);
            $prep_statement->bindValue(':user_id', $user_id);
            $prep_statement->bindValue(':status', $status);
            $prep_statement->bindValue(':total_price', $details['total']);
            $prep_statement->bindValue(':notes', $notes);
            $executed = $prep_statement->execute();
            if(!$executed) {
                throw new Exception('failed to place order, try again');
            }
            $order_id = (int)$this->pdo->lastInsertId();
            
            // create order items rows
            $prep_statement = $this->pdo->prepare($order_item_query);
            foreach($details['items'] as $i) {
                $prep_statement->bindValue(':order_id', $order_id);
                $prep_statement->bindValue(':variant_id', $i['variant_id']);
                $prep_statement->bindValue(':q', $i['quantity']);
                $prep_statement->bindValue(':unit_price', $i['unit_price']);
                $executed = $prep_statement->execute();
                if(!$executed) {
                    throw new Exception('failed to place order, try again');
                }
            }

            // decrease stock
            $variant_repo = new ProductVariantsRepository($this->pdo);

            foreach($details['items'] as $i) {
                $variant = $variant_repo->findById($i['variant_id']);
                $new_quantity = $variant['available_quantity'] - $i['quantity'];
                $updated = $variant_repo->update($variant['id'],$variant['name'],$variant['price'], $new_quantity);
                if(!$updated) {
                    throw new Exception('failed to place order, try again');
                }

                // remove the cart item
                $removed = $cart_repo->deleteItemById($i['cart_item_id']);
                if(!$removed) {
                    throw new Exception('failed to place order, try again');
                }

            }

            // now create a row for payment. required: order_id = $order_id, total_price=>amount = $details['total'], 
            $create_payment_row = $this->createPaymentEntry($order_id, $details['total'], $payment_method, 'pending');
            if(!$create_payment_row) {
                throw new Exception('failed to create payment entry');
            }

            $this->pdo->commit();
            return [
                'status' => true, 
                'message' => 'order placed successfully'
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function createPaymentEntry(int $order_id, float $amount, string $method, string $status): bool {
        $query = "insert into `payments` (`order_id`, `amount`, `payment_method`, `payment_status`) 
                    values (:order_id, :amount, :method, :status) ";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':order_id', $order_id);
        $prep_statement->bindValue(':amount', $amount);
        $prep_statement->bindValue(':method', $method);
        $prep_statement->bindValue(':status', $status);
        $executed = $prep_statement->execute();
        if(!$executed || $prep_statement->rowCount() !== 1) {
            return false;
        }
        return true;
    }
}

