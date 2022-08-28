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
                echo $this->xmlEncode($secret);
                exit();
            }
        }
    }

    // Encode data to xml
    function xmlEncode($secret)
    {
        $secretXml = new SimpleXMLElement("<Secret></Secret>");
        $secretXml->addChild('hash', $secret["hash"]);
        $secretXml->addChild('secretText', $secret["secretText"]);
        $secretXml->addChild('createdAt', $secret["createdAt"]);
        $secretXml->addChild('expiresAt', $secret["expiresAt"]);
        $secretXml->addChild('remainingViews', $secret["remainingViews"]);                           
            
        return $secretXml->asXML();
    }

    // Print getSecret form
    function echoGetSecretForm()
    {
        echo '
        <form method="POST" action="">
            hash <input type="text" name="hash" required>
            Response content type: <select name="options" required>
            <option value="application/json">application/json</option>
            <option value="application/xml">application/xml</option>
            <input type="submit" name="getSecret" value="Confirm" >
        <form>';
    }

    // Print new Secret form
    function printNewSecretForm()
    {
        echo '
        <form method="POST" action="">
            secret <input type="text" name="secretText" required>
            expireAfterViews <input type="number" name="expireAfterViews" min="1" required>
            expireAfter <input type="number" name="expireAfter" required>
            <input type="submit" name="submit" value="Submit">
        </form>';
    }
}