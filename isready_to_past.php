<?php
/**
 * Created by PhpStorm.
 * User: GNassro
 * Date: 07/06/2020
 * Time: 23:20
 */
header('Content-Type: application/json');
include_once "include/MysqlConnect.php";
include_once "include/functions.php";
$output = array();

$user_id = "";
$output["result"] = 225;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!check_connexion($user_id)){
    $output["result"] = 105; // not login
}else{
    if(isset($_POST["just_param"])) {
        if (isset($_SESSION["file_to_move"])){
            $output["result"] = 900;
        }else {
            $output["result"] = 225;
        }
    } else {
        $output["result"] = 226;
    }
}

echo json_encode($output);