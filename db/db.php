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
                    $conn = new PDO("mysql:host={$this->host};dbname={$db}", $this->user, $this->password);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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