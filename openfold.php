<?php
/**
 * Created by PhpStorm.
 * User: GNassro
 * Date: 30/05/2020
 * Time: 09:47
 */

header('Content-Type: application/json');
include "include/MysqlConnect.php";
include "include/functions.php";
$output = array();

$user_id = "";
if (!check_connexion($user_id)){
    $output["result"] = 105; // not login
}else{
    if(isset($_POST["fold_to_open"]) && isset($_POST["clitem"]) && isset($_POST["currect-dir"])){
        $con = new MysqlConnect();

        $con->prepareAndBind('SELECT * from cloud WHERE user_id = ? AND cloud_id = ?','ii',$user_id,$_POST["clitem"]);
        if ($con->secureExcecute()) {
            if ($con->get_num_rows_stm() == 1 ) {
                $clirow = $con->fetch_assoc_stm();

                if ($clirow['cloud_type'] == 'ftp') {
                    include "modules/Ftp/ftp_functions.php";

                    $con->prepareAndBind('SELECT * from ftp WHERE user_id = ? AND cloud_id = ?', 'ii', $user_id, $_POST["clitem"]);
                    if ($con->secureExcecute()) {
                        if ($con->get_num_rows_stm() == 1) {
                            $row = $con->fetch_assoc_stm();
                            if (check_ftp_connexion($ftp, $row["server"], $row["ftp_user"], $row["ftp_pwd"], $_POST["currect-dir"])) {
                                $output["result"] = 207;
                                $output["output"] = show_ftp_content($ftp, $_POST["clitem"], $_POST["fold_to_open"]);
                            } else {
                                $output["result"] = 2077;
                                $output["output"] = "error";
                            }

                        } else {
                            $output["result"] = 604;  // something wrong
                        }
                    } else {
                        $output["result"] = 603;  // bad query
                    }
                } else if ($clirow['cloud_type'] == 'sftp'){

                    require_once __DIR__ . '/modules/SFTP/phpseclib/vendor/autoload.php';

                    $con->prepareAndBind('SELECT * from ftp WHERE user_id = ? AND cloud_id = ?', 'ii', $user_id, $_POST["clitem"]);
                    if ($con->secureExcecute()) {
                        if ($con->get_num_rows_stm() == 1) {
                            $row = $con->fetch_assoc_stm();

                            $ssh2 = new \phpseclib\Net\SFTP($row['server'],$row['port']);

                            if ($ssh2->login($row['ftp_user'],$row['ftp_pwd'])) {
                                include_once __DIR__ . '/modules/SFTP/sftp_functions.php';
                                $ssh2->chdir($_POST["currect-dir"]);
                                $output["result"] = 207;
                                $output["output"] = show_sftp_content($ssh2,$_POST["clitem"],$_POST["fold_to_open"]);
                            } else {
                                $output["result"] = 2077;
                                $output["output"] = "error";
                            }

                        } else {
                            $output["result"] = 604;  // something wrong
                        }
                    } else {
                        $output["result"] = 603;  // bad query
                    }
                } else if ($clirow['cloud_type'] == 'ftps') {
                    include "modules/Ftp/ftp_functions.php";

                    $con->prepareAndBind('SELECT * from ftp WHERE user_id = ? AND cloud_id = ?', 'ii', $user_id, $_POST["clitem"]);
                    if ($con->secureExcecute()) {
                        if ($con->get_num_rows_stm() == 1) {
                            $row = $con->fetch_assoc_stm();
                            if (check_ftps_connexion($ftp, $row["server"], $row["ftp_user"], $row["ftp_pwd"], $_POST["currect-dir"])) {
                                $output["result"] = 207;
                                $output["output"] = show_ftp_content($ftp, $_POST["clitem"], $_POST["fold_to_open"]);
                            } else {
                                $output["result"] = 2077;
                                $output["output"] = "error";
                            }

                        } else {
                            $output["result"] = 604;  // something wrong
                        }
                    } else {
                        $output["result"] = 603;  // bad query
                    }
                }
            } else {
                $output["result"] = 2077;
                $output["output"] = "error";
            }
        } else {
            $output["result"] = 2077;
            $output["output"] = "error";
        }
    }else if (isset($_POST["gg_clitem"]) && isset($_POST["gg_fold_to_open_id"])){
        require __DIR__ . '/modules/ApiGoogleDrive/gg_drive_functions.php';

        $con = new MysqlConnect();

        $con->prepareAndBind('SELECT * from gg_drive WHERE user_id = ? AND cloud_id = ?','ii',$user_id,$_POST["gg_clitem"]);
        if($con->secureExcecute()) {
            if ($con->get_num_rows_stm() == 1) {
                $row = $con->fetch_assoc_stm();

                $gg_array_result = show_gg_drive_content(clone $con,$_POST["gg_clitem"],$_POST["gg_fold_to_open_id"]);
                if ($gg_array_result["result"] == 12){
                    $output["result"] = 207;
                    $output["output"] = $gg_array_result["output"];
                }else if ($gg_array_result["result"] == 10){
                    $output["result"] = 333; // redirect to google account to get access token from user

                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }

                    $tocrypt = md5(microtime().$row ["ds_name"]);
                    $_SESSION[$tocrypt]["type"] = "ggdrive";
                    $_SESSION[$tocrypt]["dsname"] = $row ["ds_name"];

                    $output["output"] = get_uri_for_access_token($tocrypt);
                }else if ($gg_array_result["result"] == 11){
                    $output["result"] = 396; // access denied
                } else {
                    $output["result"] = 399; // something wrong
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