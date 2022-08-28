<?php
require_once "config.php";
require_once "Secret.php";

Class Database 
{
    private $connection = null;
    
    // Connect to the database
    public function __construct() 
    {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE_NAME);
        } catch (Exception $e) {
            echo '<div class="error">Caught Exception: ', $e -> getMessage(), "</div>";   
        }     
    }
    
    // Select secret from database by unique hash
    public function selectSecret($hash) 
    {
        $query = "SELECT * FROM secret WHERE hash='$hash';";
        try {
            $result = $this->connection->query($query);
            return $result->fetch_assoc();
        } catch (Exception $e) {
            echo '<div class="error">Caught Exception: ', $e -> getMessage(), "</div>";   
        }        
    }

    // Create new Secret
    public function createSecret($secret)
    {        
        $hash = $secret->getHash();
        $secretText = $secret->getSecretText();
        $createdAt = $secret->getCreatedAt();
        $expiresAt = $secret->getExpiresAt();
        $remainingViews = $secret->getRemainingViews();

        $query = "INSERT INTO `secret` SET hash = '$hash', secretText = '$secretText', createdAt = '$createdAt', 
                                            expiresAt = '$expiresAt', remainingViews = $remainingViews;";
        try {
            $result = $this -> connection -> query($query);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());   
        } 
    }

    // Reduce view count
    public function decreaseViewCount($hash) 
    {
        $query = "UPDATE `secret` SET remainingViews = remainingViews - 1 WHERE hash = '$hash';";
        try {
            $result = $this -> connection -> query($query);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());   
        }
    }
}