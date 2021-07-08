<?php
/**
 * Created by PhpStorm.
 * User: GNassro
 * Date: 06/06/2020
 * Time: 02:15
 */
header('Content-Type: application/json');
include "include/MysqlConnect.php";
include "include/functions.php";
include "modules/Ftp/ftp_functions.php";
$output = array();

$user_id = "";
if (!check_connexion($user_id)){
    $output["result"] = 105; // not login
}else{
    if(isset($_POST["transfert_type"]) && isset($_POST["files_to_transfert"]) && isset($_POST["clsrc"])) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $cldcon = new MysqlConnect();

        $cldcon->prepareAndBind('SELECT * FROM cloud  WHERE user_id = ? and cloud_id = ?','ii',$user_id,$_POST["clsrc"]);
        if ($cldcon->secureExcecute()){
            if ($cldcon->get_num_rows_stm() == 1) {
                $row = $cldcon->fetch_assoc_stm();
                $cldcon->close_stm();
                if (in_array($row["cloud_type"],['ftp','sftp','ftps'])) {

                $cldcon->prepareAndBind('SELECT * FROM ftp  WHERE user_id = ? and cloud_id = ?','ii',$user_id,$_POST["clsrc"]);
                if ($cldcon->secureExcecute()) {
                    if ($cldcon->get_num_rows_stm() == 1) {
                        $row = $cldcon->fetch_assoc_stm();
                        $cldcon->close_stm();
                            /**
                             * @var $ftp Ftp
                             */

                            $file_to_transfert = json_decode($_POST["files_to_transfert"]);
                            $b = true;

                            $file_to_transfert_filtred = array();
                            $i = 0;
                            foreach ($file_to_transfert as $v) {
                                if ($v != '.' && $v != '..' && $v != './' && $v != '../' && $v != '/.' && $v != '/..') {
                                    $file_to_transfert_filtred[$i] = $v;
                                    $i++;
                                }

                            }

                            if ($b) {
                                $_SESSION["file_to_move"] = array(
                                    "transfert_type" => $_POST["transfert_type"],
                                    "clsrc" => $_POST["clsrc"],
                                    "files_to_transfert" => $file_to_transfert_filtred
                                );
                                $output["result"] = 107;
                            } else {
                                $output["result"] = 122; //something wrong
                            }


                    } else {
                        $output["result"] = 140 . $user_id . $_POST["clsrc"]; //something wrong
                    }

                } else {
                    $output["result"] = 141; //something wrong
                }
            }else if ($row["cloud_type"] == 'ggdrive'){

                    $cldcon->prepareAndBind('SELECT * FROM gg_drive  WHERE user_id = ? and cloud_id = ?','ii',$user_id,$_POST["clsrc"]);
                    if ($cldcon->secureExcecute()) {
                        if ($cldcon->get_num_rows_stm() == 1) {
                            $row = $cldcon->fetch_assoc_stm();
                                $file_to_transfert = json_decode($_POST["files_to_transfert"]);
                                $_SESSION["file_to_move"] = array(
                                        "transfert_type" => $_POST["transfert_type"],
                                        "clsrc" => $_POST["clsrc"],
                                        "files_to_transfert" => $file_to_transfert
                                    );
                                    $output["result"] = 107;
                        } else {
                            $output["result"] = 140 . $user_id . $_POST["clsrc"]; //something wrong
                        }

                    } else {
                        $output["result"] = 141; //something wrong
                    }
                    }else {
                    $output["result"] = 888;
                }
            }else{
                $output["result"] = 199 ; //something wrong
            }
        }else {
            $output["result"] = 109; // no permission or session expired
        }
    }else{
        $output["result"] = 101; // bad parameter
    }
}
echo json_encode($output);