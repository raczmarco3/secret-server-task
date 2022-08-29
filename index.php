<?php
ob_start();
session_start();
require_once "Controller/BaseController.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri);
// Fix for "secret/" url
if(isset($uri[3]) && empty($uri[3])) {
    $uri[3] = 0;
}
$baseController = new BaseController();

// Check url for location
if(isset($_GET["secret"]) && !isset($uri[3])) {  
    // Print necessary html
    $baseController->printHtml();
    unset($_SESSION["Content-type"]);

    // Print forms  
    $baseController->printNewSecretForm();
    $baseController->echoGetSecretForm();
} else if(!isset($_GET["secret"]) && !isset($uri[3])) {
    // Print necessary html
    $baseController->printHtml();
    unset($_SESSION["Content-type"]);
    $baseController->echoGetSecretForm();
    // Homepage link to add new Secret
    echo '<a href="secret">Add new Secret</a>';
} else if(isset($uri[3]) && $uri[3]!=0) {
    // Check if we came from the form
    if(isset($_SESSION["Content-type"])) {
        $_SERVER['CONTENT_TYPE'] = $_SESSION["Content-type"];        
    }
    
    // Check Content-type for proper response
    if(!isset($_SERVER['CONTENT_TYPE'])) {
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
    if(empty($_POST["secretText"]) || empty($_POST["expireAfterViews"]) 
    || (empty($_POST["expireAfter"]) && !is_numeric($_POST["expireAfter"]))) {
        if(is_numeric($_POST["expireAfterViews"]) && $_POST["expireAfterViews"]<1) {
            echo '<div class="error">expireAfterViews should be bigger than 0!</div>';
        } else {
            echo '<div class="error">Input field must not be empty!</div>';
        }
    } else if(!is_numeric($_POST["expireAfterViews"]) || !is_numeric($_POST["expireAfter"])) {
        echo '<div class="error">expireAfterViews and expireAfter should be a number!</div>';
    } else {
        try {
            echo '<div class="success">Secret created successfully! The hash for your Secret is: ', 
            $baseController->createSecret($_POST["secretText"], $_POST["expireAfterViews"], $_POST["expireAfter"])
            ,'</div>';
        } catch (Exception $e) {
            echo '<div class="error">Caught Exception: ', $e -> getMessage(), "</div>";   
        }        
    }
}

if(isset($_POST["getSecret"])) {
    if(empty($_POST["hash"])) {
        echo '<div class="error">Input field must not be empty!</div>';
    } else {
        $redirect = 'Location: secret/'.$_POST["hash"];
        $_SESSION["Content-type"] = $_POST["options"];
        header($redirect);
    }
}

?>
    </body>
</html>