<?php
require './vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

class Database{
    
    // CHANGE THE DB INFO ACCORDING TO YOUR DATABASE
    public function dbConnection(){
        
        try{
            $db_username = $_ENV['DB_USERNAME'];
            $db_password = $_ENV['DB_PASSWORD'];
            $db_name = $_ENV['DB_NAME'];
            $db_host = $_ENV['DB_HOST'];
            $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_username, $db_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        }
        catch(PDOException $e){
            echo "Connection error ".$e->getMessage(); 
            exit;
        }
    }
}