<?php

require_once __DIR__ . "/../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(dirname($_SERVER['DOCUMENT_ROOT']));
$dotenv->load();

class Connect
{
    private $hostname;
    private $database;
    private $username;
    private $password;
    public $connect;

    public function __construct()
    {
        $this->hostname = $_ENV['DB_HOST'];
        $this->database = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];

        $this->connection();
    }

    /**
     * @return void
     */
    private function connection(): void
    {
        $this->connect = new mysqli(
            $this->hostname,
            $this->username,
            $this->password,
            $this->database
        );

        if ($this->connect->connect_error) {
            http_response_code(403);
            exit();
        }
    }
}