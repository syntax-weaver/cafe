<?php 

class ProductVariantsRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllByProductId(int $product_id): ?array {
        $query = "select v.*, p.`name` as `product_name`, p.`id` as `product_id` 
                    from `product_variants` v join `products` p on v.`product_id` = p.`id` 
                    where v.`product_id` = :product_id 
                            and v.`deleted_at` is null 
                            and p.`deleted_at` is null 
                            order by v.`id`";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':product_id', $product_id);
        $prep_statement->execute();
        $product_variants = $prep_statement->fetchAll();
        return $product_variants ?: null;
    }

    public function findAllDeletedByProductId(int $product_id): ?array {
        $query = "select v.*, p.`name` as `product_name` 
                    from `product_variants` v join `products` p on v.`product_id` = p.`id` 
                    where v.`product_id` = :v_p_id
                        and v.`deleted_at` is not null 
                        and p.`deleted_at` is null 
                    order by v.`id`";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':v_p_id', $product_id);
        $prep_statement->execute();
        $product_variants = $prep_statement->fetchAll();
        return $product_variants ?: null;
    }


    public function findById(int $id): ?array {
        $query = "select v.* from `product_variants` v 
                    join `products` p on v.`product_id` = p.`id` 
                    join `categories` c on p.`category_id` = c.`id` 
                    where v.`id` = :id 
                    and c.`deleted_at` is null 
                    and p.`deleted_at` is null 
                    and v.`deleted_at` is null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $variant = $prep_statement->fetch();
        return $variant ?: null;
    }

    public function findByNameAndProductId(string $name, int $product_id): ?array {
        $query = "select * from `product_variants` where `name` = :name and `product_id` = :product_id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':name', $name);
        $prep_statement->bindValue(':product_id', $product_id);
        $prep_statement->execute();
        $variant = $prep_statement->fetch();
        return $variant ?: null;
    }

    public function create(int $product_id, string $variant_name, float $variant_price, int $available_quantity): ?int {
        $query = "insert into product_variants (`product_id`, `name`, `price`, `available_quantity`) 
                    values (:product_id, :name, :price, :available_quantity)";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':product_id', $product_id);
        $prep_statement->bindValue(':name', $variant_name);
        $prep_statement->bindValue(':price', $variant_price);
        $prep_statement->bindValue(':available_quantity', $available_quantity);
        $prep_statement->execute();
        $id = $this->pdo->lastInsertId();
        return (int)$id ?: null;
    }

    public function update(int $variant_id,string $variant_name, float $variant_price, int $available_quantity): bool {
        $query = "update `product_variants`
                    set `name`= :n, `price` = :p, `available_quantity` = :q 
                    where `id` = :id and `deleted_at` is null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':n', $variant_name);
        $prep_statement->bindValue(':p', $variant_price);
        $prep_statement->bindValue(':q', $available_quantity);
        $prep_statement->bindValue(':id', $variant_id);
        $executed = $prep_statement->execute();
        return $executed;
    }

    public function softDelete(int $id): bool {
        $query = "update product_variants set `deleted_at` = NOW() where `id` = :id and `deleted_at` is null";
        $prep_statement =  $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $executed = $prep_statement->execute();
        return $executed;
    }

    public function findDeletedById(int $id): ?array {
        $query = "select * from `product_variants` where `id` = :id and `deleted_at` is not null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $deleted_variant = $prep_statement->fetch();
        return $deleted_variant ?: null;
    }

    public function restore(int $id): bool {
        $query = "update `product_variants` set `deleted_at` = null where `id` = :id and `deleted_at` is not null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $executed = $prep_statement->execute();
        return $executed;
    }
}