<?php
/**
 * Created by PhpStorm.
 * User: GNassro
 * Date: 08/06/2020
 * Time: 05:20
 */
header('Content-Type: application/json');
include "include/MysqlConnect.php";
include "include/functions.php";
$output = array();

$user_id = "";
$output["result"] = 225;

if (!check_connexion($user_id)){
    $output["result"] = 105; // not login
}else{
    if(isset($_POST["current_dir"]) && isset($_POST["clsdest"])) {
        if (isset($_SESSION["file_to_move"])){
            $transfert_type = $_SESSION["file_to_move"]["transfert_type"];
            $clsrc = $_SESSION["file_to_move"]["clsrc"];
            $cldst = $_POST["clsdest"];
            $current_dir = $_POST["current_dir"];
            $files = $_SESSION["file_to_move"]["files_to_transfert"];

            $con = new MysqlConnect();
            //$con->getLink_Mysql()->autocommit(false);
            $v = null;
            if ($con->secure_mysql_is_connected()) {


                foreach ($files as $v) {
                    $con->prepareAndBind('INSERT INTO files_to_move (user_id, src_cld_id, dest_cld_id, file_to_transfert, dest_folder, transfert_type) VALUES (?, ?, ?, ?, ?, ?)','iiisss',$user_id,$clsrc, $cldst,$v,$current_dir,$transfert_type);
                    $con->secureExcecute();
                }
                //$con->getLink_Mysql()->commit();

                //shell_exec("php7.3 /opt/lampp/htdocs/modules/transfert/transfert_handler.php $user_id > /dev/null 2>/dev/null &");
                shell_exec("php7.3 /opt/lampp/htdocs/modules/transfert/transfert_handler.php $user_id > /opt/lampp/htdocs/log_down 2>/opt/lampp/htdocs/log_down_err &");


                $output["result"] = 900;
                $_SESSION["file_to_move"] = null;
            }else{
                $output["result"] = 396;
            }

        }else {
            $output["result"] = 225;
        }
    } else {
        $output["result"] = 226;
    }
}

echo json_encode($output);