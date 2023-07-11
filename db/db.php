<?php
    class DbConnect {
        private $host = 'localhost';
        private $user = 'root';
        private $password = '';
        private $databases = array('api_rest', 'tanya');
        private $connections = array();
    
        public function __construct() {
            foreach ($this->databases as $db) {
                try {
                    $conn = new PDO("mysql:host={$this->host};dbname={$db};charset=utf8", $this->user, $this->password, array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", // Définit le jeu de caractères lors de la connexion
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ));
                    $this->connections[$db] = $conn;
                } catch (PDOException $e) {
                    die("Error: " . $e->getMessage());
                }
            }
        }
    
        public function getDb($database) {
            return $this->connections[$database];
        }
    }
       
    

?>