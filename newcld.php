<?php
/**
 * Created by PhpStorm.
 * User: GNassro
 * Date: 13/03/2020
 * Time: 04:08
 */
/**
 * Output of jsoncode
 *
 *  29 :   ftp created successfully
 *  24  :   server is down
 *  22  :   You should type FTP password user
 *  21  :   You should type FTP user
 *  19  :   Verify the port
 *  17  :   Verify your display name (all caracters must be letters without space)
 *  16  :   Verify ftp url server
 */
header('Content-Type: application/json');
include "include/Mysql.php";
include "include/functions.php";

require __DIR__ . '/modules/ApiGoogleDrive/gg_drive_functions.php';

$output = array();
$user_id = "";
if (false){
    $output["result"] = 105; // not login
}else{
    if(isset($_POST["type"]) && isset($_POST["dsname"]) && isset($_POST["server"]) && isset($_POST["cport"]) && isset($_POST["usrname"]) && isset($_POST["pswd"])){
        if (is_url($_POST["server"])){
            if (true){
                if(ctype_digit($_POST["cport"])){
                    if(!empty($_POST["usrname"])){
                        if (!empty($_POST["pswd"])){
                            /*$addcldcon = new MysqlConnect();

                            $addcldcon->prepareAndBind('INSERT INTO cloud (user_id, cloud_name, cloud_type) VALUES (?, ?, ?)','iss',$user_id,$_POST["dsname"], $_POST["type"]);


                            if ($addcldcon->secureExcecute()){
                                $id_cloud = $addcldcon->getLink_stm()->insert_id;
                                $addcldcon->close_stm();

                                $addcldcon->prepareAndBind("INSERT INTO ftp (cloud_id, user_id, dsname, server, port, ftp_user, ftp_pwd) VALUES (?, ?, ?, ?, ?, ?, ?)",'iississ',$id_cloud, $user_id,$_POST["dsname"],$_POST["server"],$_POST["cport"],$_POST["usrname"],$_POST["pswd"]);
                                if ($addcldcon->secureExcecute()) {
                                    $addcldcon->close_stm();
                                    $addcldcon->close_Mysql_stm();
                                    $output["result"] = 29; // FTP added successfully
                                }else{
                                $output["result"] = 24; // server is down
                            }
                            }else{
                                $output["result"] = 24; // server is down
                            }*/
                            $addcldcon = new MysqlConnect2();
                            $res = $addcldcon->send_query("INSERT INTO cloud (user_id, cloud_type, cloud_name) VALUES ('" . 18 . "','" . $_POST['type'] ."','" .  $_POST['dsname'] . "')");
                            $row = $res->fetch_assoc();

                        }else{
                            $output["result"] = 22; // You should type FTP password usersssssss
                        }
                    }else{
                        $output["result"] = 21; // You should type FTP user
                    }
                }else{
                    $output["result"] = 19; // Verify the port
                }
            }else{
                $output["result"] = 17; // Verify your display name (all caracters must be letters without space)
            }
        }else{
            $output["result"] = 16; // Verify ftp url server
        }
    } else if (isset($_POST["gg_type"]) && isset($_POST["gg_dsname"])){
        if ($_POST["gg_type"] === "ggdrive"){
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $tocrypt = md5(microtime().$_POST["gg_dsname"]);
            $_SESSION[$tocrypt]["type"] = $_POST["gg_type"];
            $_SESSION[$tocrypt]["dsname"] = $_POST["gg_dsname"];

            $output["uri_auth"] = get_uri_for_access_token($tocrypt);
            $output["result"] = 290;
        }else {
            $output["result"] = 16; // Verify parameters
        }
    }
}

echo json_encode($output);