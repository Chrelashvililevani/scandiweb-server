<?php
namespace Scandiweb;

require 'vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

class DatabaseConnection {
    private $conn;

    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $connectionParams = [
            'dbname' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASS'],
            'host' => $_ENV['DB_HOST'],
            'driver' => 'pdo_mysql',
        ];

        try {
            $this->conn = DriverManager::getConnection($connectionParams);
        } catch (\Exception $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}

// Create an instance of the DatabaseConnection class and get the connection
$db = new DatabaseConnection();
$conn = $db->getConnection();
