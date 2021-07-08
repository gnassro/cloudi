<?php
/**
 * Created by PhpStorm.
 * User: GNassro
 * Date: 06/03/2020
 * Time: 20:07
 */

/**
 * Output of jsoncode
 *
 *  1024 :   account signin successfully
 *  404  :   email or password wrong
 *  260  :   must verify password
 *  190  :   must verify email
 */
header('Content-Type: application/json');
include "include/MysqlConnect.php";
include "include/functions.php";
$output = array();
if (check_connexion()){
    $output["result"] = 105;
}else{
    if(!isset($_POST["signincondition"]) || !isset($_POST["signinpassword"]) || !isset($_POST["signinemail"])){
        $output["result"] = 40400;
    }else{
        if(check_mail($_POST["signinemail"])){
            if(strlen($_POST["signinpassword"]) >= 6){

                        $password = $_POST["signinpassword"];
                        $email = $_POST["signinemail"];
                        $condition = $_POST["signincondition"];

                        $sigcon = new MysqlConnect();

                        $sigcon->prepareAndBind('SELECT * from user WHERE email = ?','s',$email);
                        if($sigcon->secureExcecute()){
                            if ($sigcon->get_num_rows_stm() == 1){
                                $row = $sigcon->fetch_assoc_stm();
                                $sigcon->close_stm();
                                if (password_verify($password,$row["password"])){

                                    $date = new DateTime();
                                    $session_id = md5($row["id"].$date->getTimestamp());

                                    $sigcon->prepareAndBind('INSERT INTO session_account (session_id, user_id) VALUES (?,?)','si',$session_id,$row["id"]);

                                    if($sigcon->secureExcecute()){
                                        $output["result"] = 1024;
                                        if (session_status() == PHP_SESSION_NONE) {
                                            session_start();
                                        }
                                        if($condition == "true"){
                                            setcookie("session_key", $session_id, time()+(3600*24*30), "/", $_SERVER['SERVER_NAME'], false,true);
                                            $_SESSION['session_key'] = $session_id;
                                        }else{
                                            $_SESSION['session_key'] = $session_id;
                                        }
                                    }else{
                                        $output["result"] = 990;
                                    }

                                }else{
                                    $output["result"] = 404;
                                }
                            }else{
                                $output["result"] = 404;
                            }
                        }
            }else{
                $output["result"] = 260;
            }
        }else{
            $output["result"] = 190;
        }
    }
}
echo json_encode($output);