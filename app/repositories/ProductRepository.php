<?php 

class ProductRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): ?array {
        $query = "select p.*, c.`name` as `category_name`, c.`id` as `category_id` from `products` p 
                    join `categories` c on p.`category_id` = c.`id` 
                    where p.`deleted_at` is null and c.`deleted_at` is null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->execute();
        $all_products = $prep_statement->fetchAll();
        return $all_products ?: null;
    }

    public function findById(int $id): ?array {
        $query = "select * from products where `id` = :id and `deleted_at` is null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $product = $prep_statement->fetch();
        return $product ?: null;
    }

    public function findByName(string $name): ?array {
        $query = "select * from products where `name` = :name and `deleted_at` is null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':name', $name);
        $prep_statement->execute();
        $product = $prep_statement->fetch();
        return $product ?: null;
    }

    public function create(
        int $category_id, 
        string $product_name, 
        string $product_description, 
        string $product_image = ''
    ): ?int {
        $query = "insert into products (`category_id`, `name`, `description`, `image`) values (:category_id
                    , :name, :description, :image)";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':category_id', $category_id);
        $prep_statement->bindValue(':name', $product_name);
        $prep_statement->bindValue(':description', $product_description);
        $prep_statement->bindValue(':image', $product_image);
        $prep_statement->execute();
        $id = $this->pdo->lastInsertId();
        return (int)$id ?: null;
    }

    public function update(
        int $id, 
        int $new_category_id, 
        string $new_product_name, 
        string $new_product_description, 
        string $new_product_image = ''
    ): bool {
        $query = "update products set `category_id` = :new_category_id, `name` = :new_name, 
                    `description` = :new_description, `image` = :new_image where `id` = :id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->bindValue(':new_category_id', $new_category_id);
        $prep_statement->bindValue(':new_name', $new_product_name);
        $prep_statement->bindValue(':new_description', $new_product_description);
        $prep_statement->bindValue(':new_image', $new_product_image);
        $executed = $prep_statement->execute();
        return $executed;
    }

    public function softDelete(int $id): bool {
        $query = "update products set `deleted_at` = NOW() where `id` = :id and `deleted_at` is null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $executed = $prep_statement->execute();
        return $executed;
    }

    public function restore(int $id): bool {
        $query = "update products set `deleted_at` = null where `id` = :id and `deleted_at` is not null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $executed = $prep_statement->execute();
        return $executed;
    }

    public function findAllDeleted(): ?array {
        $query = "select p.*, c.`name` as `category_name`, c.`id` as `category_id` from `products` p join `categories` c on p.`category_id` = c.`id` where p.`deleted_at` is not null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->execute();
        $all_deleted_products = $prep_statement->fetchAll();
        return $all_deleted_products ?: null;
    }

    public function findDeletedById(int $id): ?array {
        $query = "select p.*, c.`deleted_at` as `category_deleted_at` from `products` p join `categories` c on p.`category_id` = c.`id` where p.`id` = :id and p.`deleted_at` is not null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $deleted_product = $prep_statement->fetch();
        return $deleted_product ?: null;
    }

    public function findDeletedByName(string $name): ?array {
        $query = "select * from products where `name` = :name and `deleted_at` is not null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':name', $name);
        $prep_statement->execute();
        $deleted_product = $prep_statement->fetch();
        return $deleted_product ?: null;
    }

    public function findByCategoryId(int $id): ?array {
        $query = "select * from products where `category_id` = :category_id and `deleted_at` is null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':category_id', $id);
        $prep_statement->execute();
        $all_products = $prep_statement->fetchAll();
        return $all_products ?: null;
    }

    public function softDeleteByCategoryId(int $category_id): bool {
        $query = "update products set `deleted_at` = NOW() where `category_id` = :category_id and `deleted_at` is null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':category_id', $category_id);
        $executed = $prep_statement->execute();
        return $executed;
    }

    public function findAllVisibleForCustomers(): array {
        $query = "select p.`id`, p.`category_id`, p.`name`, p.`description`, p.`image`, p.`created_at`, p.`updated_at`, p.`deleted_at`, 
                    c.`name` as `category_name`, 
                    MIN(v.`price`) as `lowest_price`, 
                    case 
                        when MAX(v.`available_quantity`) > 0 then 0 
                        else 1 
                    end as `is_out_of_stock` 
                    from `products` p 
                    join `categories` c on p.`category_id` = c.`id`  
                    join `product_variants` v on v.`product_id` = p.`id` 
                    where p.`deleted_at` is null 
                    and c.`deleted_at` is null  
                    and v.`deleted_at` is null 
                    group by p.`id`
                    ";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->execute();
        $visible_products = $prep_statement->fetchAll();
        return $visible_products;
    }

    public function findVisibleProductForCustomerById(int $id): ?array {
        $query = "select p.`id`, p.`category_id`, p.`name`, p.`description`, p.`image`, p.`created_at`, p.`updated_at`, p.`deleted_at`, 
                    c.`name` as `category_name`
                    from `products` p 
                    join `categories` c on p.`category_id` = c.`id` 
                    where p.`id` = :id 
                    and p.`deleted_at` is null 
                    and c.`deleted_at` is null
                    "; 
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $product = $prep_statement->fetch();
        if (!$product) {
            return null;
        }
        $query = "select * from `product_variants` where `product_id` = :id and `deleted_at` is null";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $variants = $prep_statement->fetchAll();
        if (empty($variants)) {
            return null;
        }
        return [
            'product' => $product,
            'variants' => $variants
        ];
    }
}