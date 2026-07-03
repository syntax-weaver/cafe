<?php 


class UserRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail(string $email): ?array {
        $select_query = "select * from users where email = :email";
        $prep_statement = $this->pdo->prepare($select_query);
        $prep_statement->bindValue(':email', $email);
        $prep_statement->execute();
        $result = $prep_statement->fetch();
        return $result ?: null;
    }

    public function findById(int $id): ?array {
        $select_query = "select * from users where id = :id";
        $prep_statement = $this->pdo->prepare($select_query);
        $prep_statement->bindValue(':id', $id);
        $prep_statement->execute();
        $result = $prep_statement->fetch();
        return $result ?: null;
    }

    public function create(
        string $name, 
        string $email, 
        string $password, 
        string $role = 'client', 
        string $profile_image = ''
    ): int {
        $create_statement = "insert into users (`name`, `email`, `password`, `role`, `profile_image`)
                                values (:name, :email, :password, :role, :profile_image)";
        $prep_statement = $this->pdo->prepare($create_statement);
        $prep_statement->bindValue(':name', $name);
        $prep_statement->bindValue(':email', $email);
        $prep_statement->bindValue(':password', $password);
        $prep_statement->bindValue(':role', $role);
        $prep_statement->bindValue(':profile_image', $profile_image);
        $prep_statement->execute();
        $id = $this->pdo->lastInsertId();
        return (int)$id;
    }

}