<?php

require_once "../config.php";
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
            echo "<br>Caught Exception: ", $e -> getMessage(), "<br>";   
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
            echo "<br>Caught Exception: ", $e -> getMessage(), "<br>";   
        }        
    }

    // Create new Secret
    public function createSecret($secret)
    {
        // Generate hash (database prevents duplicates)
        $hash = hash("sha256", $secret->getCreatedAt());
        $secretText = $secret->getSecretText();
        $createdAt = $secret->getCreatedAt();
        $expiresAt = $secret->getExpiresAt();
        $remainingViews = $secret->getRemainingViews();

        $query = "INSERT INTO `secret` SET hash = '$hash', secretText = '$secretText', createdAt = '$createdAt', 
                                            expiresAt = $expiresAt, remainingViews = $remainingViews;";
        try {
            $result = $this -> connection -> query($query);
        } catch (Exception $e) {
            echo "<br>Caught Exception: ", $e -> getMessage(), "<br>";   
        } 
    }
}