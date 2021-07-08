<?php
/**
 * Created by PhpStorm.
 * User: GNassro
 * Date: 04/03/2020
 * Time: 19:03
 */
/**
 * Output of jsoncode
 *
 *  101 :   account created successfully
 *  99  :   Mysql connexion down
 *  75  :   email already used
 *  35  :   must verify lastname
 *  31  :   must verify firstname
 *  26  :   must verify password
 *  19  :   must verify email
 */
header('Content-Type: application/json');
include "include/MysqlConnect.php";
include "include/functions.php";
$output = array();
if (check_connexion()){
    $output["result"] = 105;
}else{
    if(!isset($_POST["signuppassword"]) || !isset($_POST["signuplastname"]) || !isset($_POST["signupfirstname"]) || !isset($_POST["signupemail"])){
        $output["result"] = 4040;
    }else{
        if(check_mail($_POST["signupemail"])){
            if(strlen($_POST["signuppassword"]) >= 6){
                if(ctype_alpha($_POST["signupfirstname"])){
                    if(ctype_alpha($_POST["signuplastname"])){


                        $password = $_POST["signuppassword"];
                        $email = $_POST["signupemail"];
                        $firstname = $_POST["signupfirstname"];
                        $lastname = $_POST["signuplastname"];
                        $sigcon = new MysqlConnect();

                        $sigcon->prepareAndBind('SELECT * from user WHERE email = ?','s',$email);
                        if($sigcon->secureExcecute()){
                            if ($sigcon->get_num_rows_stm() == 0){
                                $sigcon->close_stm();
                                $sigcon->prepareAndBind('INSERT INTO user (firstname, lastname, email, password) VALUES (?,?,?,?)','ssss',$firstname,$lastname,$email,password_hash($password, PASSWORD_DEFAULT));
                                if($sigcon->secureExcecute()){
                                    $id = $sigcon->getLink_Mysql()->insert_id;

                                    $secureftpType = "sftp";
                                    $sftp_username = md5($_POST["signupemail"]);

                                    $a = shell_exec("sudo /opt/lampp/htdocs/create_user_ftp.sh $sftp_username " . $_POST["signuppassword"]);

                                    $addcldcon = new MysqlConnect();

                                    $addcldcon->prepareAndBind('INSERT INTO cloud (user_id, cloud_name, cloud_type) VALUES (?, ?, ?)','iss',$id,"Local", "sftp");
                                    if ($addcldcon->secureExcecute()){
                                        $id_cloud = $addcldcon->getLink_stm()->insert_id;

                                        $addcldcon->prepareAndBind("INSERT INTO ftp (cloud_id, user_id, dsname, server, port, ftp_user, ftp_pwd) VALUES (?, ?, ?, ?, ?, ?, ?)",'iississ',$id_cloud, $id, "Local","127.0.0.1","22",$sftp_username,$_POST["signuppassword"]);
                                        if ($addcldcon->secureExcecute()) {
                                            $addcldcon->close_stm();
                                            $addcldcon->close_Mysql_stm();
                                            $sigcon->close_stm();
                                            $sigcon->close_Mysql_stm();
                                            $output["result"] = 101; // FTP added successfully
                                        }else{
                                            $output["result"] = 24; // server is down
                                        }
                                    }else{
                                        $output["result"] = 24; // server is down
                                    }

                                }else{
                                    $output["result"] = 99;
                                }
                            }else{
                                $output["result"] = 75;
                            }
                        }
                    }else{
                        $output["result"] = 35;
                    }
                }else{
                    $output["result"] = 31;
                }
            }else{
                $output["result"] = 26;
            }
        }else{
            $output["result"] = 19;
        }
    }
}
echo json_encode($output);