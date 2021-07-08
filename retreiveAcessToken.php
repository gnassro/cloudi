<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 7/5/20
 * Time: 5:59 PM
 */
require __DIR__ . "/include/MysqlConnect.php";
require __DIR__ . "/include/functions.php";

require __DIR__ . '/modules/ApiGoogleDrive/gg_drive_functions.php';

if (!check_connexion($user_id)){
    header('location: http://' . $_SERVER['HTTP_HOST'] . '/index.php?error=no_user_connected');
}
else {
    if (!(isset($_GET["code"]) && isset($_GET["scope"]) && isset($_GET["state"]))) {

        if (isset($_GET["error"]) && isset($_GET["state"])) {
            header('location: http://' . $_SERVER['HTTP_HOST'] . '/index.php?error=' . $_GET["error"] . '&state=' . $_GET["state"]);
        } else {
            header('location: http://' . $_SERVER['HTTP_HOST'] . '/index.php?error=somethings_wrong');
        }
    } else {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION[$_GET["state"]])) {
            header('location: http://' . $_SERVER['HTTP_HOST'] . '/index.php?error=bad_parameters');
        } else {
            $client = global_gg_params();
            $accessToken = $client->fetchAccessTokenWithAuthCode(trim($_GET["code"]));
            if (isset($accessToken["error"])) {
                header('location: http://' . $_SERVER['HTTP_HOST'] . '/index.php?error=' . $accessToken["error"] . '&error_description=' . $accessToken["error_description"]);
            }else {
                $client->setAccessToken($accessToken);
                if (!$client->isAccessTokenExpired()) {
                    $con = new MysqlConnect();

                    $service = new Google_Service_Drive($client);
                    $optParams = array(
                        'fields' => 'user(emailAddress)'
                    );
                    $email = $service->about->get($optParams)->getUser()->emailAddress;
                    $accessToken = json_encode($client->getAccessToken());
                    $con->prepareAndBind('SELECT * FROM gg_drive WHERE email = ? AND user_id = ?','si',$email,$user_id);
                    if ($con->secureExcecute()) {
                        if ($con->get_num_rows_stm() == 0) {
                            $con->close_stm();
                            $con2 = new MysqlConnect();

                            $con2->prepareAndBind("INSERT INTO cloud (user_id, cloud_name, cloud_type) VALUES (?, ?, ?)",'iss', $user_id, $_SESSION[$_GET["state"]]["dsname"], $_SESSION[$_GET["state"]]["type"]);
                            if ($con2->secureExcecute()) {
                                $id_cloud = $con2->getLink_stm()->insert_id;
                                $con2->close_stm();

                                $con2->prepareAndBind("INSERT INTO gg_drive (cloud_id, user_id, ds_name, access_token, email) VALUES (?, ?, ?, ?, ?)",'iisss', $id_cloud, $user_id, $_SESSION[$_GET["state"]]["dsname"], $accessToken, $email);
                                if ($con2->secureExcecute()) {
                                    unset($_SESSION[$_GET["state"]]);
                                    header('location: http://' . $_SERVER['HTTP_HOST']);
                                } else {

                                    $con->prepareAndBind('DELETE FROM cloud WHERE cloud_id = ?', 'i',$id_cloud);

                                    if ($con->secureExcecute()){
                                        header('location: http://' . $_SERVER['HTTP_HOST'] . '/index.php?error=bad_connexion');
                                    } else {
                                        header('location: http://' . $_SERVER['HTTP_HOST'] . '/index.php?error=Database_error');
                                    }
                                    $con->close_stm();
                                }


                            } else {
                                header('location: http://' . $_SERVER['HTTP_HOST'] . '/index.php?error=bad_connexion');
                            }
                            $con2->close_stm();
                            $con2->close_Mysql_stm();
                        } else {
                            $con->close_stm();
                            unset($_SESSION[$_GET["state"]]);

                            $con->prepareAndBind('UPDATE gg_drive SET access_token = ? WHERE email = ? AND user_id = ?','ssi',$accessToken,$email,$user_id);
                            if ($con->secureExcecute()){
                                header('location: http://' . $_SERVER['HTTP_HOST'] . '/index.php?error=drive_already_exist&update=1');
                            }else {
                                header('location: http://' . $_SERVER['HTTP_HOST'] . '/index.php?error=drive_already_exist&update=0');
                            }
                            $con->close_stm();
                            $con->close_Mysql_stm();
                        }
                    } else {
                        header('location: http://' . $_SERVER['HTTP_HOST'] . '/index.php?error=bad_connexion');
                    }
                }
            }
        }
    }
}
