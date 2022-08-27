<?php

require_once "Model/Database.php";
require_once "Model/Secret.php";

Class BaseController
{
    private $database = null;

    function __construct() {
        $this->database = new Database();
    }

    function createSecret($secretText, $expireAfterViews, $expireAfter)
    {
        // Get current date
        $createdAt = date('Y-m-d H:i:s', time());
        // Datetime to seconds
        $expiresAt = strtotime($createdAt);
        // Add required minutes (*60 because expiresAt is in seconds)
        $expiresAt = $expiresAt + (60*$expireAfter);
        // Convert it back to datetime
        $expiresAt = date("Y-m-d H:i:s", $expiresAt);

        // Hash is empty because we will generate it later
        $secret = new Secret('', $secretText, $createdAt, $expiresAt, $expireAfterViews);
        try {
            $this->database->createSecret($secret);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());   
        } 
    }

    function getData($httpHeaders=array())
    {
        // stuff
    }
}