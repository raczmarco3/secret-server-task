<?php

require_once "Controller/BaseController.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri);
$baseController = new BaseController();

if(isset($_GET["secret"]) && !isset($uri[3])) {    
?>
    <form method="POST" action="">
        secret <input type="text" name="secretText" required>
        expireAfterViews <input type="number" name="expireAfterViews" min="1" required>
        expireAfter <input type="number" name="expireAfter" required>
        <input type="submit" name="submit" value="Submit">
    </form>

    <form method="POST" action="">
        hash <input type="text" name="hash" required>
        Response content type: <select name="options" class="selectOption" required>
            <option value="json">application/json</option>
            <option value="xml">application/xml</option>
        <input type="submit" name="getSecret" value="Confirm" >
    <form>
<?php
} else if(!isset($_GET["secret"]) && !isset($uri[3])) {
?>
    <form method="POST" action="">
        hash <input type="text" name="hash" required>
        Response content type: <select name="options" class="selectOption" required>
            <option value="json">application/json</option>
            <option value="xml">application/xml</option>
        <input type="submit" name="getSecret" value="Confirm" >
        <a href="secret">Add new Secret</a>
    <form>
<?php
} else if(isset($uri[3])){    
    if(!isset($_SERVER['CONTENT_TYPE'])){
        $baseController->sendData(array('Content-Type: application/json', 'HTTP/1.1 200 OK'));
    } else if ($_SERVER['CONTENT_TYPE'] == 'application/json') {
        $baseController->sendData(array('Content-Type: application/json', 'HTTP/1.1 200 OK'));
    } else if($_SERVER['CONTENT_TYPE'] == 'application/xml') {
        $baseController->sendData(array('Content-Type: application/xml', 'HTTP/1.1 200 OK'));
    } else {
        $baseController->sendData(array('Content-Type: application/json', 'HTTP/1.1 200 OK'));
    } 
}

if(isset($_POST["submit"])) {
    if(empty($_POST["secretText"]) || empty($_POST["expireAfterViews"]) || empty($_POST["expireAfter"])) {
        echo "Input field must not be empty!";
    } else {
        try {
            $baseController->createSecret($_POST["secretText"], $_POST["expireAfterViews"], $_POST["expireAfter"]);
        } catch (Exception $e) {
            echo "<br>Caught Exception: ", $e -> getMessage(), "<br>";   
        }        
    }
}

if(isset($_POST[""])) {

}

?>