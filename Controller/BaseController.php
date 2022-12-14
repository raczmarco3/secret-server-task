<?php

require_once "Model/Database.php";
require_once "Model/Secret.php";

Class BaseController
{
    private $database = null;

    function __construct() 
    {
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
        // Generate hash (database prevents duplicates)        
        $hash = hash("sha256", $createdAt);

        $secret = new Secret($hash, $secretText, $createdAt, $expiresAt, $expireAfterViews);
        try {
            $this->database->createSecret($secret);
            return $hash;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());   
        } 
    }

    // Get and print data according to Content-type
    function getData($hash, $httpHeaders=array())
    {
        $secret = $this->database->selectSecret($hash);
        // If secret not exists or there are no more views left
        if(empty($secret) || $secret["remainingViews"]<=0 
        || ($secret["expiresAt"] < date('Y-m-d H:i:s', time()) && $secret["expiresAt"]!=$secret["createdAt"])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        } else {
            // Decrease viewCount by 1
            $this->decreaseViewCount($hash);
            // Set Content-type
            if (is_array($httpHeaders) && count($httpHeaders)) {
                foreach ($httpHeaders as $httpHeader) {
                    header($httpHeader);
                }
            }
            // Print data according to Content-Type
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
            <label for="hash">Hash:</label>
            <input type="text" name="hash" placeholder="Enter your Hash" required>
            <label for="options">Chose Content-type:</label>
            <select name="options" required>
                <option value="application/json">application/json</option>
                <option value="application/xml">application/xml</option>
            <input type="submit" name="getSecret" value="Confirm" >
        </form>';
    }

    // Print new Secret form
    function printNewSecretForm()
    {
        echo '
        <form method="POST" action="">
            <label for="secretText">Secret:</label> 
            <input type="text" name="secretText" placeholder="What is your secret?" required>
            <label for="expireAfterViews">View count:</label>
            <input type="number" name="expireAfterViews" min="1" placeholder="How many times would you like to visit your secret?" required>
            <label for="expireAfter">Expire (in minutes):</label>
            <input type="number" name="expireAfter" placeholder="When should your secret expire?" required>
            <input type="submit" name="submit" value="Submit">
        </form>';
    }

    // Reduce view count
    function decreaseViewCount($hash)
    {
        $this->database->decreaseViewCount($hash);
    }

    // Print html
    function printHtml()
    {
        echo '
        <!DOCTYPE html>
        <head>
            <title>Secret Server</title>
            <link rel="stylesheet" href="style.css">
        </head>
        <body>';
    }

    // Check Content-type for proper response
    function checkContentType($contentType, $uri)
    {
        if ($contentType == 'application/json') {
            $this->getData($uri[3], array('Content-Type: application/json', 'HTTP/1.1 200 OK'));
        } else if($contentType == 'application/xml') {
            $this->getData($uri[3], array('Content-Type: application/xml', 'HTTP/1.1 200 OK'));
        } 
    }
}