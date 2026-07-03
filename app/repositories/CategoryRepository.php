<?php 

class CategoryRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll (): ?array {
        $select_query = "select * from categories where `deleted_at` is null";
        $prep_statement = $this->pdo->prepare($select_query);
        $prep_statement->execute();
        $categories = $prep_statement->fetchAll();
        return $categories ?: null;
    }

    public function findById (int $id): ?array {
        $select_query = "select * from categories where `id` = :id and `deleted_at` is null";
        $prep_statement = $this->pdo->prepare($select_query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $category = $prep_statement->fetch();
        return $category ?: null; // if category is truthy return it, else null
    }

    public function findByName (string $name): ?array {
        $select_query = "select * from categories where `name` = :name and`deleted_at` is null";
        $prep_statement = $this->pdo->prepare($select_query);
        $prep_statement->bindValue(':name', $name);
        $prep_statement->execute();
        $category = $prep_statement->fetch();
        return $category ?: null;
    }


    public function create (string $name): ?int {
        $create_query = "insert into categories (`name`) values (:name)";
        $prep_statement = $this->pdo->prepare($create_query);
        $prep_statement->bindValue(':name', $name);
        $prep_statement->execute();
        $id = $this->pdo->lastInsertId();
        if (! $id) {
            return null;
        } else {
            return (int)$id;
        }
    }

    public function update (int $id, string $new_name): bool {
        $update_query = "update categories set `name` = :new_name where `id` = :id";
        $prep_statement = $this->pdo->prepare($update_query);
        $prep_statement->bindValue(':new_name', $new_name);
        $prep_statement->bindValue(':id', $id);
        $executed = $prep_statement->execute();
        return $executed;
    }

    public function softDelete (int $id): bool {
        $update_query = "update categories set `deleted_at` = NOW() where `id` = :id and `deleted_at` is null";
        $prep_statement = $this->pdo->prepare($update_query);
        $prep_statement->bindValue(':id', $id);
        $executed = $prep_statement->execute();
        return $executed;
    }

    public function restore (int $id): bool {
        $restore_query = "update categories set `deleted_at` = null where `id` = :id and `deleted_at` is not null";
        $prep_statement = $this->pdo->prepare($restore_query);
        $prep_statement->bindValue(':id', $id);
        $executed = $prep_statement->execute();
        return $executed;
    }

    public function findAllDeleted(): ?array {
        $select_query = "select * from categories where `deleted_at` is not null";
        $prep_statement = $this->pdo->prepare($select_query);
        $prep_statement->execute();
        $result = $prep_statement->fetchAll();
        return $result ?: null;
    }

    public function findDeletedById (int $id): ?array {
        $select_query = "select * from categories where `id` = :id and `deleted_at` is not null";
        $prep_statement = $this->pdo->prepare($select_query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $category = $prep_statement->fetch();
        return $category ?: null;
    }

    public function findDeletedByName (string $name): ?array {
        $select_query = "select * from categories where `name` = :name and `deleted_at` is not null";
        $prep_statement = $this->pdo->prepare($select_query);
        $prep_statement->bindValue(':name', $name);
        $prep_statement->execute();
        $category = $prep_statement->fetch();
        return $category ?: null;
    }

    public function canDelete (int $id): bool {
        $query = "select * from products where `category_id` = :id";
        $prep_statement = $this->pdo->prepare($query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $products = $prep_statement->fetchAll();
        return empty($products) ? true : false;
    }

    public function softDeleteConnectedProducts(int $id): bool {
        $update_query = "update products set `deleted_at` = NOW() where `category_id` = :id and `deleted_at` is null";
        $prep_statement = $this->pdo->prepare($update_query);
        $prep_statement->bindValue(':id', $id);
        $executed = $prep_statement->execute();
        return $executed;
    }
    public function restoreDeletedConnectedProducts(int $id): bool {
        $restore_query = "update products set `deleted_at` = null where `category_id` = :id and `deleted_at` is not null";
        $prep_statement = $this->pdo->prepare($restore_query);
        $prep_statement->bindValue(':id', $id);
        $executed = $prep_statement->execute();
        return $executed;
    }

}