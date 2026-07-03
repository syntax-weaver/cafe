<?php


class Database {
    private string $db_host;
    private string $db_name;
    private string $db_port;
    private string $db_user;
    private string $db_password;
    private string $dsn;
    private PDO $connection;

    public function __construct(array $credentials)
    {
        $this->db_host = $credentials['DB_HOST'];
        $this->db_name = $credentials['DB_NAME'];
        $this->db_port = $credentials['DB_PORT'];
        $this->db_user = $credentials['DB_USER'];
        $this->db_password = $credentials['DB_PASSWORD'];
        $this->dsn = "mysql:host={$this->db_host};dbname={$this->db_name};port={$this->db_port};";
        
        try {
            $this->connection = new PDO($this->dsn, $this->db_user, $this->db_password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
                PDO::ATTR_EMULATE_PREPARES   => false, 
                
            ]);
            update_logs('Successfully connected to database.');
        } catch (PDOException $e) {
            update_logs($e->getMessage());
            abort(500, 'Database connection failed.');
            
        }

    }

    public function connect(): PDO {
        return $this->connection;
    }
}
