<?php

namespace System;

use mysqli;
use Dotenv;

class Connect
{
    private $hostname;
    private $database;
    private $username;
    private $password;
    public $connect;

    public function __construct()
    {
        $dotenv = Dotenv\Dotenv::createImmutable(dirname($_SERVER['DOCUMENT_ROOT']));
        $dotenv->load();

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

    /**
     * @param $query
     * @param $params
     * @param $param_types
     * @return false
     */
    public function query($query, $params = [], $param_types = "")
    {
        $stmt = $this->connect->prepare($query);
        if ($stmt === false) {
            return false;
        }

        if (!empty($params)) {
            $stmt->bind_param($param_types, ...$params);
            if ($stmt->errno) {
                $stmt->close();
                return false;
            }
        }

        $stmt->execute();
        if ($stmt->errno) {
            $stmt->close();
            return false;
        }

        if (stripos(trim($query), 'SELECT') === 0) {
            $result = $stmt->get_result();
            if ($result === false) {
                $stmt->close();
                return false;
            }
            $stmt->close();
            return $result;
        } else {
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            return $affectedRows;
        }

    }

    /**
     * @param $result
     * @return array|null
     */
    public function fetchArray($result): null|array
    {
        return $res = $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * @param $result
     * @return array|null
     */
    public function fetch($result): null|array
    {
        return $res = $result->fetch_assoc();
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->connect->close();
    }
}