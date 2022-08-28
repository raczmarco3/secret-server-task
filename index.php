<?php
session_start();
require_once "Controller/BaseController.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri);
// Fix for "secret/" url
if(isset($uri[3]) && empty($uri[3])) {
    $uri[3] = 0;
}
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
        Response content type: <select name="options" required>
            <option value="json">application/json</option>
            <option value="xml">application/xml</option>
        <input type="submit" name="getSecret" value="Confirm" >
    <form>
<?php
} else if(!isset($_GET["secret"]) && !isset($uri[3])) {
?>
    <form method="POST" action="">
        hash <input type="text" name="hash" required>
        Response content type: <select name="options" required>
            <option value="application/json">application/json</option>
            <option value="application/xml">application/xml</option>
        <input type="submit" name="getSecret" value="Confirm" >
        <a href="secret">Add new Secret</a>
    <form>
<?php
} else if(isset($uri[3])){
    // Check if we came from the form
    if(isset($_SESSION["Content-type"])) {
        $_SERVER['CONTENT_TYPE'] = $_SESSION["Content-type"];
        unset($_SESSION["Content-type"]);
    }
       
    if(!isset($_SERVER['CONTENT_TYPE'])){
        $baseController->getData($uri[3], array('Content-Type: application/json', 'HTTP/1.1 200 OK'));
    } else if ($_SERVER['CONTENT_TYPE'] == 'application/json') {
        $baseController->getData($uri[3], array('Content-Type: application/json', 'HTTP/1.1 200 OK'));
    } else if($_SERVER['CONTENT_TYPE'] == 'application/xml') {
        $baseController->getData($uri[3], array('Content-Type: application/xml', 'HTTP/1.1 200 OK'));
    } else {
        $baseController->getData($uri[3], array('Content-Type: application/json', 'HTTP/1.1 200 OK'));
    }
}

// Check if form is submitted
if(isset($_POST["submit"])) {
    // Check input fields
    if(empty($_POST["secretText"]) || empty($_POST["expireAfterViews"]) || empty($_POST["expireAfter"])) {
        if(is_numeric($_POST["expireAfterViews"]) && $_POST["expireAfterViews"]<1) {
            echo "expireAfterViews should be bigger than 0!";
        } else {
            echo "Input field must not be empty!";
        }
    } else if(!is_numeric($_POST["expireAfterViews"]) || !is_numeric($_POST["expireAfter"])) {
        echo "expireAfterViews and expireAfter should be a number!";
    } else {
        try {
            $baseController->createSecret($_POST["secretText"], $_POST["expireAfterViews"], $_POST["expireAfter"]);
        } catch (Exception $e) {
            echo "<br>Caught Exception: ", $e -> getMessage(), "<br>";   
        }        
    }
}

if(isset($_POST["getSecret"])) {
    if(empty($_POST["hash"])) {
        echo "Input field must not be empty!";
    } else {
        $redirect = 'Location: secret/'.$_POST["hash"];
        $_SESSION["Content-type"] = $_POST["options"];
        header($redirect);
    }
}
?>