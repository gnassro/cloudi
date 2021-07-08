<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/18/20
 * Time: 8:45 AM
 */

header('Content-Type: application/json');
include "include/MysqlConnect.php";
include "include/functions.php";
$output = array();

$user_id = "";
if (!check_connexion($user_id)){
    $output["result"] = 105; // not login
}else{
    if(isset($_POST["just_param"])){

        $con = new MysqlConnect();

        $con->prepareAndBind('SELECT * from files_to_move WHERE user_id = ? ','i',$user_id);
        if($con->secureExcecute()) {
            if ($con->get_num_rows_stm() > 0) {
                $con->close_stm();

                $con->prepareAndBind('SELECT * from transfert_log WHERE user_id = ? ', 'i',$user_id);
                if($con->secureExcecute()) {
                    if ($con->get_num_rows_stm() == 1) {
                        $row = $con->fetch_assoc_stm();
                        $output["result"] = 666;
                        $output["text"] = $row["file_label"];
                    }else{
                        $output["result"] = 667;  // something wrong
                    }
                }else {
                    $output["result"] = 603;  // bad query
                }
            }else{
                $output["result"] = 604;  // something wrong
            }
        }else {
            $output["result"] = 603;  // bad query
        }
    }else{
        $output["result"] = 106; // parameter(s) missing
    }
}

echo json_encode($output);