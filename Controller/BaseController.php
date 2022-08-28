<?php

require_once "Model/Database.php";
require_once "Model/Secret.php";

Class BaseController
{
    private $database = null;

    function __construct() {
        $this->database = new Database();
    }

    // Create new Secret
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

    // Get and print data according to Content-type
    function getData($hash, $httpHeaders=array())
    {
        $secret = $this->database->selectSecret($hash);
        // If secret not exists
        if(empty($secret)) {
            header("HTTP/1.1 404 Not Found");
            exit();
        } else {
            // Set Content-type
            if (is_array($httpHeaders) && count($httpHeaders)) {
                foreach ($httpHeaders as $httpHeader) {
                    header($httpHeader);
                }
            }
            // Print data
            if(in_array("Content-Type: application/json", $httpHeaders)) {
                echo json_encode($secret);
                exit();
            } else if(in_array("Content-Type: application/xml", $httpHeaders)) {
                echo xml_encode($secret);
                exit();
            }
        }
    }
}