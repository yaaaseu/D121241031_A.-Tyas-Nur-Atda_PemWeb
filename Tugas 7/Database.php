<?php
declare(strict_types=1);

class Database
{
    private ?PDO $pdo = null;

    public function __construct(private array $config)
    {
    }

    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $this->config['host'],
                $this->config['db']
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                $this->pdo = new PDO(
                    $dsn,
                    $this->config['user'],
                    $this->config['pass'],
                    $options
                );
            } catch (PDOException $e) {
                error_log('Database Connection Error: ' . $e->getMessage());
                die('Sistem mengalami kegagalan teknis.');
            }
        }

        return $this->pdo;
    }
}