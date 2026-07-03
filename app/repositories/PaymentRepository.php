<?php 

class PaymentRepository{
    private PDO $pdo; 

    public function __construct() {
        $this->pdo = db();
    }

    public function getPaymentStatusByOrderId(int $order_id): ?string {
        $query = "select `payment_status` from payments where `order_id` = :order_id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':order_id', $order_id);
        $executed = $prep_statement->execute();
        if(!$executed || $prep_statement->rowCount() <= 0) {
            return null;
        }
        $status = $prep_statement->fetch()['payment_status'];
        return $status;
    }         

    public function getPaymentByOrderId(int $order_id): ?array {
        $query = "select * from payments where `order_id` = :order_id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':order_id', $order_id);
        $prep_statement->execute();
        $payment = $prep_statement->fetch();
        return $payment?: null;
    }

    public function payOrderByOrderId(int $order_id): bool {
        $transaction_reference = 'TXN' . uniqid();
        $query = "update `payments` 
                    set `payment_status` = 'paid', 
                    `paid_at` = NOW(), 
                    `transaction_reference`=case 
                                                when `payment_method` = 'card' then :tr 
                                                else null
                                            end 
                    where `order_id` = :order_id
                    and `payment_status` = 'pending'";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':order_id', $order_id);
        $prep_statement->bindValue(':tr', $transaction_reference);
        $executed = $prep_statement->execute();
        if(!$executed || $prep_statement->rowCount() !== 1) {
            return false;
        }
        return true;
    }


}