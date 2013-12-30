<?php
include('./includes/loader.php');

if ((!empty($_POST['email'])) && (!empty($_POST['password']))) {
    $sUser = User::login($_POST['email'], $_POST['password'], 1);
    if (is_array($sUser)) {
        echo json_encode($sUser);
        die();
    }
} else {
    echo json_encode(array("json" => 1, "type" => "result", "result" => "The username and password can not be blank!"));
    die();
}

$sAction = $_POST['action'];

if (empty($sAction)) {
    echo json_encode(array("json" => 1, "type" => "result", "result" => "You must submit an action!"));
    die();
}

if ($sUser->sPermissions != 7) {
    die();
}

$sAction = strtolower($sAction);
if(is_file("./api_functions/{$sAction}.php")){
    include "./api_functions/{$sAction}.php";
    $api = new $sAction($database);
    $api->run();
    echo json_encode($api->output);
    die();
}