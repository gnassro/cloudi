<?php
/**
 * Created by PhpStorm.
 * User: GNassro
 * Date: 08/06/2020
 * Time: 21:36
 */

class Transfert
{
    //private $condb;
    private $user;


    public function __construct($condb, $user_id)
    {
        /* @var $condb MysqlConnect */
        //$this->condb = $condb;
        $this->user = $user_id;
    }

    public function execute_transfert()
    {
        echo "1\n";
        $connect = new MysqlConnect();
        $connect->prepareAndBind('SELECT * FROM files_to_move WHERE user_id = ? ORDER BY timestamp_file ASC LIMIT 1','i',$this->user);

        if ($connect->secureExcecute()) {
            if ($connect->get_num_rows_stm() == 1) {
                echo "2\n";
                $other_confb = new MysqlConnect();

                $other_confb->prepareAndBind('SELECT * FROM transfert_log WHERE user_id = ?','i',$this->user);
                if ($other_confb->secureExcecute()) {
                    $row_file_to_move = $connect->fetch_assoc_stm();
                    if ($other_confb->get_num_rows_stm() == 0) {
                        echo "3\n";
                        $transfert_type = "";
                        if ($row_file_to_move["transfert_type"] == "cp") {
                            $transfert_type = "Copy";
                            echo "4\n";
                        } else if ($row_file_to_move["transfert_type"] == "mv") {
                            $transfert_type = "Move";
                            echo "5\n";
                        }

                        $other_confb->prepareAndBind('SELECT * FROM cloud WHERE user_id = ? AND cloud_id = ?','ii',$this->user,$row_file_to_move['src_cld_id']);

                        if ($other_confb->secureExcecute()) {
                            if ($other_confb->get_num_rows_stm() == 1) {
                                echo "6\n";
                                $row = $other_confb->fetch_assoc_stm();
                                $source_cld_name = $row["cloud_name"];
                                $source_cld_type = $row["cloud_type"];


                                $other_confb->prepareAndBind('SELECT * FROM cloud WHERE user_id = ? AND cloud_id = ?','ii',$this->user,$row_file_to_move['dest_cld_id']);

                                if ($other_confb->secureExcecute()) {
                                    if ($other_confb->get_num_rows_stm() == 1) {
                                        echo "7\n";
                                        $row = $other_confb->fetch_assoc_stm();
                                        $dest_cld_name = $row["cloud_name"];
                                        $dest_cld_type = $row["cloud_type"];

                                        if (in_array($source_cld_type,['ftp','sftp','ftps']) && in_array($dest_cld_type,['ftp','sftp','ftps'])) {
                                            $file_label = $transfert_type . " " . $row_file_to_move["file_to_transfert"] . " from " . $source_cld_name . " to " . $dest_cld_name;

                                            $cond = new MysqlConnect();

                                            $cond->prepareAndBind('INSERT INTO transfert_log (user_id, file_id, file_label) VALUES (?, ?, ?)','iis', $this->user, $row_file_to_move["id"], $file_label);
                                            if ($cond->secureExcecute()) {
                                                if ($this->ftp_ftp_transfert(clone $other_confb, $row_file_to_move["file_to_transfert"], $row_file_to_move['src_cld_id'], $row_file_to_move['dest_cld_id'], $row_file_to_move['dest_folder'], $row_file_to_move["transfert_type"])) {
                                                    shell_exec("rm -r /home/cloudi_log/$this->user");
                                                    $con = new MysqlConnect();
                                                    echo $row_file_to_move["id"];

                                                    $con->prepareAndBind('DELETE FROM files_to_move WHERE id = ?','i',$row_file_to_move["id"]);
                                                    if ($con->secureExcecute()) {
                                                        echo "??\n";
                                                        $this->execute_transfert();
                                                    } else {
                                                        echo "mmm\n";
                                                    }
                                                } else {
                                                    // do somethings
                                                }
                                            }
                                        } else if ($source_cld_type == "ggdrive" && $dest_cld_type == "ggdrive") {
                                            $dbcon = new MysqlConnect();

                                            $dbcon->prepareAndBind('SELECT * FROM gg_drive WHERE cloud_id = ?','i',$row_file_to_move['src_cld_id']);
                                            if ($dbcon->secureExcecute()) {
                                                $source_gg_row = $dbcon->fetch_assoc_stm();

                                                $dbcon->prepareAndBind('SELECT * FROM gg_drive WHERE cloud_id = ?','i',$row_file_to_move['dest_cld_id']);
                                                if ($dbcon->secureExcecute()) {
                                                    $dest_gg_row = $dbcon->fetch_assoc_stm();
                                                    require_once $_SERVER["DOCUMENT_ROOT"] . "modules/ApiGoogleDrive/gg_drive_functions.php";
                                                    $cheTokenAvailability_src_cl = get_access_token_availability($client_src_cl, $dbcon, $row_file_to_move['src_cld_id']);
                                                    /* @var $client_src_cl Google_Client */
                                                    if ($cheTokenAvailability_src_cl["result"]) {
                                                        $cheTokenAvailability_dest_cl = get_access_token_availability($client_dest_cl, $dbcon, $row_file_to_move['dest_cld_id']);
                                                        /* @var $client_dest_cl Google_Client */
                                                        if ($cheTokenAvailability_dest_cl["result"]) {

                                                            $service_src_cl = new Google_Service_Drive($client_src_cl);
                                                            $service_dest_cl = new Google_Service_Drive($client_dest_cl);
                                                            $Params = array(
                                                                'fields' => 'name'
                                                            );
                                                            $File_Name_to_transfert = $service_src_cl->files->get($row_file_to_move["file_to_transfert"], $Params)->getName();

                                                            $file_label = $transfert_type . " " . $File_Name_to_transfert . " from " . $source_cld_name . " to " . $dest_cld_name;

                                                            $sm = new MysqlConnect();

                                                            $sm->prepareAndBind('INSERT INTO transfert_log (user_id, file_id, file_label) VALUES (?, ?, ?)','iis', $this->user, $row_file_to_move["id"], $file_label);
                                                            if ($sm->secureExcecute()) {
                                                                if ($this->ggdrive_ggdrive_transfert($row_file_to_move["file_to_transfert"], $service_src_cl, $service_dest_cl, $row_file_to_move['dest_folder'], $row_file_to_move["transfert_type"])) {
                                                                    //@shell_exec("rm -r /home/cloudi_log/$this->user");
                                                                    $con = new MysqlConnect();
                                                                    $con->prepareAndBind('DELETE FROM files_to_move WHERE id= ?','i',$row_file_to_move["id"]);
                                                                    if ($con->secureExcecute()) {
                                                                        $this->execute_transfert();
                                                                    } else {
                                                                        // do something
                                                                    }
                                                                } else {
                                                                    // log
                                                                }
                                                            }

                                                        } else {
                                                            // handle log
                                                        }
                                                    } else {
                                                        // handle log
                                                    }
                                                }
                                            }
                                        } else if ($source_cld_type == "ggdrive" && in_array($dest_cld_type,['ftp','sftp','ftps'])) {
                                            $dbcon = new MysqlConnect();

                                            $dbcon->prepareAndBind('SELECT * FROM gg_drive WHERE cloud_id = ?','i',$row_file_to_move['src_cld_id']);
                                            if ($dbcon->secureExcecute()) {
                                                $source_gg_row = $dbcon->fetch_assoc_stm();
                                                require_once $_SERVER["DOCUMENT_ROOT"] . "modules/ApiGoogleDrive/gg_drive_functions.php";
                                                $cheTokenAvailability_src_cl = get_access_token_availability($client_src_cl, $dbcon, $row_file_to_move['src_cld_id']);
                                                /* @var $client_src_cl Google_Client */
                                                if ($cheTokenAvailability_src_cl["result"]) {
                                                    $service_src_cl = new Google_Service_Drive($client_src_cl);
                                                    $Params = array(
                                                        'fields' => 'name'
                                                    );
                                                    $File_Name_to_transfert = $service_src_cl->files->get($row_file_to_move["file_to_transfert"], $Params)->getName();
                                                    $file_label = $transfert_type . " " . $File_Name_to_transfert . " from " . $source_cld_name . " to " . $dest_cld_name;

                                                    $con_ddb = new MysqlConnect();

                                                    $con_ddb->prepareAndBind('INSERT INTO transfert_log (user_id, file_id, file_label) VALUES (?, ?, ?)','iis', $this->user, $row_file_to_move["id"], $file_label);
                                                    if ($con_ddb->secureExcecute()) {
                                                        if ($this->ggdrive_ftp_transfert($row_file_to_move["file_to_transfert"], $service_src_cl, $row_file_to_move['dest_cld_id'], $row_file_to_move['dest_folder'], $row_file_to_move["transfert_type"])) {
                                                            @shell_exec("rm -r /home/cloudi_log/$this->user");
                                                            $con = new MysqlConnect();

                                                            $con->prepareAndBind('DELETE FROM files_to_move WHERE id = ?','i',$row_file_to_move["id"]);
                                                            if ($con->secureExcecute()) {
                                                                $this->execute_transfert();
                                                            } else {
                                                                // do something
                                                            }
                                                        } else {
                                                            echo "finish but error";
                                                        }
                                                    }
                                                }
                                            }
                                        } else if (in_array($source_cld_type,['ftp','sftp','ftps']) && $dest_cld_type == "ggdrive") {
                                            $dbcon = new MysqlConnect();

                                            $dbcon->prepareAndBind('SELECT * FROM gg_drive WHERE cloud_id = ?','i',$row_file_to_move['dest_cld_id']);
                                            if ($dbcon->secureExcecute()) {
                                                require_once $_SERVER["DOCUMENT_ROOT"] . "modules/ApiGoogleDrive/gg_drive_functions.php";
                                                $cheTokenAvailability_dest_cl = get_access_token_availability($client_dest_cl, $dbcon, $row_file_to_move['dest_cld_id']);
                                                /* @var $client_dest_cl Google_Client */
                                                if ($cheTokenAvailability_dest_cl["result"]) {
                                                    $service_dest_cl = new Google_Service_Drive($client_dest_cl);
                                                    $file_label = $transfert_type . " " . $row_file_to_move["file_to_transfert"] . " from " . $source_cld_name . " to " . $dest_cld_name;
                                                    $st = new MysqlConnect();

                                                    $st->prepareAndBind('INSERT INTO transfert_log (user_id, file_id, file_label) VALUES (?, ?, ?)','iis', $this->user, $row_file_to_move["id"], $file_label);
                                                    if ($st->secureExcecute()) {
                                                        if ($this->ftp_ggdrive_transfert(new MysqlConnect(), $row_file_to_move["file_to_transfert"], $row_file_to_move['src_cld_id'], $service_dest_cl, $row_file_to_move['dest_folder'], $row_file_to_move["transfert_type"])) {
                                                            @shell_exec("rm -r /home/cloudi_log/$this->user");
                                                            $con = new MysqlConnect();

                                                            $con->prepareAndBind('DELETE FROM files_to_move WHERE id = ?','i',$row_file_to_move["id"]);
                                                            if ($con->secureExcecute()) {
                                                                $this->execute_transfert();
                                                            } else {
                                                                // do something
                                                            }
                                                        } else {
                                                            echo "from Transfert: false to upload to ggdrive";
                                                            ///$this->execute_transfert();
                                                        }
                                                    }
                                                } else {
                                                    // handle it
                                                }
                                            }

                                        }

                                    }
                                }
                            }
                        }

                    } else if ($other_confb->get_num_rows_stm() == 1) {
                        @shell_exec("rm -r /home/cloudi_log/$this->user");
                        $con = new MysqlConnect();

                        shell_exec("echo 'Error:" . $row_file_to_move["file_label"] . "' >> /opt/lampp/htdocs/file_not_transfered.txt");

                        $con->prepareAndBind('DELETE FROM files_to_move WHERE id = ?','i',$row_file_to_move["id"]);
                        if ($con->secureExcecute()) {
                            $this->execute_transfert();
                        } else {
                            // do something
                        }
                    }
                }
            }
        }
    }

    private function ftp_ggdrive_transfert($condb, $file_to_transfert, $src_cloud_id, $service_dest_cl, $dest_folder, $transfert_type){
        /* @var $service_dest_cl Google_Service_Drive */
        /* @var $condb MysqlConnect */
        $fun_ret = false;

        $con = new MysqlConnect();

        $con->prepareAndBind('SELECT ftp.*, cloud.cloud_type FROM ftp JOIN cloud ON cloud.cloud_id = ftp.cloud_id AND ftp.cloud_id=?','i',$src_cloud_id);
        if ($con->secureExcecute()) {
            if ($con->get_num_rows_stm() == 1) {
                $src_cloud_row = $con->fetch_assoc_stm();

                $server_src = $src_cloud_row["server"];
                $username_src = $src_cloud_row["ftp_user"];
                $password_src = $src_cloud_row["ftp_pwd"];
                $port_src = $src_cloud_row["port"];
                $cloud_type_src = $src_cloud_row["cloud_type"];

                $file_type = 'file';
                try {
                    if ($cloud_type_src === 'ftp') {
                        require_once __DIR__ . "/../Ftp/Ftp.php";
                        $ftp = new Ftp;
                        $ftp->connect(str_replace('ftp://', '', $server_src), $port_src);
                        $ftp->login($username_src, $password_src);

                        if ($ftp->isDir($file_to_transfert))
                            $file_type = 'dir';

                    } else if ($cloud_type_src === 'ftps') {
                        require_once __DIR__ . "/../Ftp/Ftp.php";
                        $ftp = new Ftp;
                        $ftp->sslConnect(str_replace('ftp://', '', $server_src), $port_src);
                        $ftp->login($username_src, $password_src);

                        if ($ftp->isDir($file_to_transfert))
                            $file_type = 'dir';

                    } else if ($cloud_type_src === 'sftp') {
                        require_once __DIR__ . "/../SFTP/phpseclib/vendor/autoload.php";
                        $sftp = new \phpseclib\Net\SFTP($server_src, $port_src);
                        $sftp->login($username_src, $password_src);

                        if ($sftp->is_dir($file_to_transfert))
                            $file_type = 'dir';
                    }
                } catch (FtpException $e) {
                    echo $e->getMessage();
                }

                echo shell_exec("mkdir -p /home/cloudi_log/$this->user/TEMP ; chmod -R 777 /home/cloudi_log/$this->user");
                //echo shell_exec("/opt/lampp/htdocs/ftp_to_local.sh $this->user $username_src $password_src $server_src $file_to_transfert $port_src $transfert_type");
                echo "/opt/lampp/htdocs/ftp_ftps_sftp_to_local.sh $this->user $username_src $password_src $server_src $file_to_transfert $port_src $transfert_type $cloud_type_src $file_type ";
                $log_res = shell_exec("sudo /opt/lampp/htdocs/ftp_ftps_sftp_to_local.sh $this->user $username_src $password_src $server_src $file_to_transfert $port_src $transfert_type $cloud_type_src $file_type");

                echo "LOG_FTP_DOWNLOAD:" . $log_res . PHP_EOL;

                $path = "/home/cloudi_log/$this->user/TEMP/" . basename($file_to_transfert);
                if (file_exists($path)) {
                    if (!is_dir($path)) {
                        return $this->upload_file_local_to_ggdrive($path, $service_dest_cl, $dest_folder, $transfert_type);
                    } else {
                        $folder_contents = array();
                        $folder_contents[0]["FullPATH"] = $path;
                        $folder_contents[0]["type"] = "folder";

                        $this->local_dir_r($folder_contents[0]["childs"], $path);
                        var_dump($folder_contents);
                        $get_res = $this->upload_local_to_gg_dir($folder_contents, $service_dest_cl, $dest_folder, $transfert_type);


                        if ($transfert_type == "mv") {
                            if ($get_res) {
                                shell_exec("rm -r $path");
                            } else {
                                return false;
                            }
                        }
                        return true;
                    }
                } else {
                    echo    "file doesn't exist";
                    return false;
                }

            }
        }
        return $fun_ret;
    }

    private function upload_local_to_gg_dir ($folder_contents, $service_dest_cl,$destFoldId,$transfert_type, $isAllfile_trandfered = true){
        foreach ($folder_contents as $i => $v){
            if ($v["type"] != "folder") {
                if(!$this->upload_file_local_to_ggdrive($v["FullPATH"],$service_dest_cl,$destFoldId,$transfert_type))
                    $isAllfile_trandfered = false;
            } else {
                $optParams = array(
                    'fields' => 'files(id, name)',
                    'q' => "trashed = false and '" . $destFoldId . "' in parents and name = '" . basename($v["FullPATH"]) . "'"
                );
                $getFoldIfExist = $service_dest_cl->files->listFiles($optParams);
                $getFoldNameIfexist = false;
                $getFoldIdIfexist = false;
                foreach ($getFoldIfExist->getFiles() as $file) {
                    $getFoldIdIfexist = $file->getId();
                    $getFoldNameIfexist = $file->getName();
                }
                if ($getFoldNameIfexist == basename($v["FullPATH"])) {
                    $this->upload_local_to_gg_dir($v["childs"],$service_dest_cl,$getFoldIdIfexist,$transfert_type,$isAllfile_trandfered);
                } else {
                    $DriveFile = new Google_Service_Drive_DriveFile();
                    $DriveFile->setName(basename($v["FullPATH"]));
                    $DriveFile->setParents(array($destFoldId));
                    $DriveFile->setMimeType('application/vnd.google-apps.folder');

                    $result = $service_dest_cl->files->create($DriveFile, array(
                        'fields' => 'id'
                    ));

                    if ($result->getId()) {
                        $this->upload_local_to_gg_dir($v["childs"],$service_dest_cl,$result->getId(),$transfert_type,$isAllfile_trandfered);
                    } else {
                        $isAllfile_trandfered = false;
                    }

                }
            }
        }
        return $isAllfile_trandfered;
    }

    private function local_dir_r (&$folder_contents, $folderPath){
        $row = scandir($folderPath);
        $i = 0;
        foreach ($row as $file){
            if ($file!="." && $file!=".."){
                if (!is_dir($folderPath."/".$file)) {
                    $folder_contents[$i]["FullPATH"] = $folderPath."/".$file;
                    $folder_contents[$i]["type"] = "file";
                } else {
                    $folder_contents[$i]["FullPATH"] = $folderPath."/".$file;
                    $folder_contents[$i]["type"] = "folder";
                    $this->local_dir_r($folder_contents[$i]["childs"],$folderPath."/".$file);
                }
            }
            $i++;
        }
    }

    private function upload_file_local_to_ggdrive ($fileNamePath, $service_dest_cl, $dest_folder, $transfert_type ){
        $ret = true;
        try {
            $optParams = array(
                'fields' => 'files(id, name)',
                'q' => "trashed = false and '" . $dest_folder . "' in parents and name = '" . basename($fileNamePath) . "'"
            );
            $getFileIfExist = $service_dest_cl->files->listFiles($optParams);
            $getFileNameIfexist = false;
            $getFileIdIfexist = false;
            foreach ($getFileIfExist->getFiles() as $file) {
                echo $file->getName();
                $getFileIdIfexist = $file->getId();
                $getFileNameIfexist = $file->getName();
            }
            $mtime = filemtime($fileNamePath);
            $currentTime = DateTime::createFromFormat('U', $mtime);
            $getModifTIME = $currentTime->format('c');
            $DriveFile = new Google_Service_Drive_DriveFile();
            $DriveFile->setModifiedTime($getModifTIME);
            if ($getFileNameIfexist == basename($fileNamePath)) {
                $result = $service_dest_cl->files->update($getFileIdIfexist, $DriveFile, array(
                    'data' => file_get_contents($fileNamePath),
                    'mimeType' => 'application/octet-stream',
                    'fields' => 'id',
                    'uploadType' => 'resumable'
                ));
            } else {
                $DriveFile->setName(basename($fileNamePath));
                $DriveFile->setParents(array($dest_folder));

                $result = $service_dest_cl->files->create($DriveFile, array(
                    'data' => file_get_contents($fileNamePath),
                    'mimeType' => 'application/octet-stream',
                    'fields' => 'id',
                    'uploadType' => 'resumable'
                ));
            }
            if ($result->getId()) {
                shell_exec("rm -r $fileNamePath");
            } else {
                $ret = false;
            }
        } catch (Error $error){
            echo "bvtyf";
            $ret = false;
        } catch (Throwable $th) {
            echo "bvtyf222";
            $ret = false;
        }
        return $ret;
    }
    private function ggdrive_ftp_transfert($file_to_transfert, $service_src_cl, $dest_cloud_id, $dest_folder, $transfert_type)
    {
        /* @var $service_src_cl Google_Service_Drive */
        /* @var $condb MysqlConnect */
        $con = new MysqlConnect();

        $Params = array(
            'fields' => 'mimeType'
        );
        $fun_ret = false;
        $getFileName = "";
        $file_to_transfert_type = $service_src_cl->files->get($file_to_transfert, $Params)->getMimeType();

        $file_type = "file";
        if ($file_to_transfert_type != "application/vnd.google-apps.folder") {
            $way_res = shell_exec("mkdir -p /home/cloudi_log/$this->user/TEMP ; chmod -R 777 /home/cloudi_log/$this->user");

            try {
                $getFileName = $service_src_cl->files->get($file_to_transfert, array("fields" => "name"))->getName();

                $content = $service_src_cl->files->get($file_to_transfert, array("alt" => "media"));

                $outHandle = fopen("/home/cloudi_log/$this->user/TEMP/" . $getFileName, "w+");
                while (!$content->getBody()->eof()) {
                    fwrite($outHandle, $content->getBody()->read(1024));
                }
                fclose($outHandle);

                $getModifTIME = $service_src_cl->files->get($file_to_transfert, array("fields" => "modifiedTime"))->getModifiedTime();
                $service_src_cl->files->delete($file_to_transfert);
                $d = new DateTime($getModifTIME);
                touch("/home/cloudi_log/$this->user/TEMP/" . $getFileName, $d->format('U'));
            } catch (Exception $a) {
                shell_exec("rm -r /home/cloudi_log/$this->user");
                return false;
            }
        } else {
            $file_type = 'dir';
            try {
                $folder_contents = array();
                $getFolder = $service_src_cl->files->get($file_to_transfert, array("fields" => "name,id"));
                $getFileName = $getFolder->getName();
                $folder_contents[0]["id"] = $getFolder->getId();
                $folder_contents[0]["name"] = $getFolder->getName();
                $folder_contents[0]["type"] = "folder";

                $this->gg_dir_r($folder_contents[0]["childs"], $getFolder->getId(), $service_src_cl);

                $get_res = $this->download_gg_dir_to_local($folder_contents, $service_src_cl);

                if ($transfert_type == "mv") {
                    if ($get_res)
                        $service_src_cl->files->delete($file_to_transfert);
                }
            } catch (Exception $a) {
                return false;
            }
        }


        $con->prepareAndBind('SELECT ftp.*, cloud.cloud_type FROM ftp JOIN cloud ON cloud.cloud_id = ftp.cloud_id AND ftp.cloud_id=?','i',$dest_cloud_id);
        if ($con->secureExcecute()) {
            if ($con->get_num_rows_stm() == 1) {
                $dest_cloud_row = $con->fetch_assoc_stm();

                $server_dest = str_replace('ftp://', '', $dest_cloud_row["server"]);
                $username_dest = $dest_cloud_row["ftp_user"];
                $password_dest = $dest_cloud_row["ftp_pwd"];
                $port_dest = $dest_cloud_row["port"];
                $cloud_type_dest = $dest_cloud_row["cloud_type"];

                //echo shell_exec("/opt/lampp/htdocs/local_to_ftp.sh $this->user $getFileName $username_dest $password_dest $server_dest $port_dest $dest_folder");

                echo "sudo /opt/lampp/htdocs/local_to_ftp_ftps_sftp.sh $this->user $getFileName $username_dest $password_dest $server_dest $port_dest $dest_folder $cloud_type_dest $file_type";
                echo shell_exec("sudo /opt/lampp/htdocs/local_to_ftp_ftps_sftp.sh $this->user $getFileName $username_dest $password_dest $server_dest $port_dest $dest_folder $cloud_type_dest $file_type");

                $fun_ret = true;
            }
        }
        @$con->close_stm();
        @$con->close_Mysql_stm();
        return $fun_ret;
    }

    private function download_gg_file_to_local($file_to_transfert, $service_src_cl, $relative_path)
    {
        try {
            $CACHE_PATH = "/home/cloudi_log/$this->user/TEMP/";
            $getFileName = $service_src_cl->files->get($file_to_transfert, array("fields" => "name"))->getName();

            $content = $service_src_cl->files->get($file_to_transfert, array("alt" => "media"));


            if (!is_dir($CACHE_PATH)) {
                $way_res = shell_exec("mkdir -p $CACHE_PATH ; chmod -R 777 /home/cloudi_log/$this->user");
            }


            $outHandle = fopen($CACHE_PATH . $relative_path . '/' . $getFileName, "w+");
            while (!$content->getBody()->eof()) {
                fwrite($outHandle, $content->getBody()->read(1024));
            }
            fclose($outHandle);

            $getModifTIME = $service_src_cl->files->get($file_to_transfert, array("fields" => "modifiedTime"))->getModifiedTime();

            $d = new DateTime($getModifTIME);
            touch($CACHE_PATH . $relative_path . '/' . $getFileName, $d->format('U'));
        } catch (Exception $a) {
            return false;
        }
    }

    private function download_gg_dir_to_local($fold_contents, $service_src_cl, $relative_path = "", $isAllfile_trandfered = true)
    {
        /* @var $service_src_cl Google_Service_Drive */

        $CACHE_PATH = "/home/cloudi_log/$this->user/TEMP/";

        if (!is_dir($CACHE_PATH . $relative_path)) {
            mkdir($CACHE_PATH . $relative_path, 0777, true);
        }

        foreach ($fold_contents as $i => $v) {
            if ($v["type"] != "folder") {
                $ret = $this->download_gg_file_to_local($v["id"], $service_src_cl, $relative_path);

                if (!$ret) {
                    $isAllfile_trandfered = false;
                    // log
                }
            } else {
                $this->download_gg_dir_to_local($v["childs"], $service_src_cl, $relative_path . '/' . $v["name"], $isAllfile_trandfered);
            }
        }

        return $isAllfile_trandfered;
    }

    private function ggdrive_ggdrive_transfert($file_to_transfert, $service_src_cl, $service_dest_cl, $dest_folder, $transfert_type)
    {
        /* @var $service_src_cl Google_Service_Drive */
        /* @var $service_dest_cl Google_Service_Drive */


        $Params = array(
            'fields' => 'mimeType'
        );
        $file_to_transfert_type = $service_src_cl->files->get($file_to_transfert, $Params)->getMimeType();
        if ($file_to_transfert_type != "application/vnd.google-apps.folder") {
            return $this->upload_gg_file($file_to_transfert, $service_src_cl, $service_dest_cl, $dest_folder, $transfert_type);
        } else {
            try {
                $folder_contents = array();
                $getFolder = $service_src_cl->files->get($file_to_transfert, array("fields" => "name,id"));
                $folder_contents[0]["id"] = $getFolder->getId();
                $folder_contents[0]["name"] = $getFolder->getName();
                $folder_contents[0]["type"] = "folder";

                $this->gg_dir_r($folder_contents[0]["childs"], $getFolder->getId(), $service_src_cl);

                $get_res = $this->upload_gg_dir($folder_contents, $dest_folder, $service_src_cl, $service_dest_cl, $transfert_type);

                if ($transfert_type == "mv") {
                    if ($get_res)
                        $service_src_cl->files->delete($file_to_transfert);
                }
                return true;
            } catch (Exception $a) {
                return false;
            }
        }

    }


    private function upload_gg_file($file_to_transfert, $service_src_cl, $service_dest_cl, $dest_folder, $transfert_type)
    {
        try {
            $getFileName = $service_src_cl->files->get($file_to_transfert, array("fields" => "name"))->getName();

            $content = $service_src_cl->files->get($file_to_transfert, array("alt" => "media"));
            $way_res = shell_exec("mkdir -p /home/cloudi_log/$this->user/TEMP ; chmod -R 777 /home/cloudi_log/$this->user");

            $outHandle = fopen("/home/cloudi_log/$this->user/TEMP/" . $getFileName, "w+");
            while (!$content->getBody()->eof()) {
                fwrite($outHandle, $content->getBody()->read(1024));
            }
            fclose($outHandle);

            $getModifTIME = $service_src_cl->files->get($file_to_transfert, array("fields" => "modifiedTime"))->getModifiedTime();

            $d = new DateTime($getModifTIME);
            touch("/home/cloudi_log/$this->user/TEMP/" . $getFileName, $d->format('U'));


            $optParams = array(
                'fields' => 'files(id, name)',
                'q' => "trashed = false and '" . $dest_folder . "' in parents and name = '" . $getFileName . "'"
            );
            $getFileIfExist = $service_dest_cl->files->listFiles($optParams);

            $getFileNameIfexist = false;
            $getFileIdIfexist = false;
            foreach ($getFileIfExist->getFiles() as $file) {
                $getFileIdIfexist = $file->getId();
                $getFileNameIfexist = $file->getName();
            }
            $DriveFile = new Google_Service_Drive_DriveFile();
            $DriveFile->setModifiedTime($getModifTIME);
            if ($getFileNameIfexist == $getFileName) {
                $result = $service_dest_cl->files->update($getFileIdIfexist, $DriveFile, array(
                    'data' => file_get_contents("/home/cloudi_log/$this->user/TEMP/" . $getFileName),
                    'mimeType' => 'application/octet-stream',
                    'fields' => 'id',
                    'uploadType' => 'resumable'
                ));
            } else {
                $DriveFile->setName($getFileName);
                $DriveFile->setParents(array($dest_folder));

                $result = $service_dest_cl->files->create($DriveFile, array(
                    'data' => file_get_contents("/home/cloudi_log/$this->user/TEMP/" . $getFileName),
                    'mimeType' => 'application/octet-stream',
                    'fields' => 'id',
                    'uploadType' => 'resumable'
                ));
            }

            if ($result->getId()) {
                if ($transfert_type == "mv") {
                    echo "moved";
                    $service_src_cl->files->delete($file_to_transfert);
                }
            } else {
                shell_exec("rm -r /home/cloudi_log/$this->user");
                return false;
            }
            shell_exec("rm -r /home/cloudi_log/$this->user");
            return true;
        } catch (Exception $a) {
            shell_exec("rm -r /home/cloudi_log/$this->user");
            return false;
        }
    }

    private function upload_gg_dir($fold_contents, $destFoldId, $service_src_cl, $service_dest_cl, $transfert_type, $isAllfile_trandfered = true)
    {
        /* @var $service_dest_cl Google_Service_Drive */
        /* @var $service_src_cl_cl Google_Service_Drive */

        var_dump($fold_contents);
        foreach ($fold_contents as $i => $v) {
            if ($v["type"] != "folder") {
                $ret = $this->upload_gg_file($v["id"], $service_src_cl, $service_dest_cl, $destFoldId, $transfert_type);

                if (!$ret) {
                    $isAllfile_trandfered = false;
                    // log
                }
            } else {
                $optParams = array(
                    'fields' => 'files(id, name)',
                    'q' => "trashed = false and '" . $destFoldId . "' in parents and name = '" . $v["name"] . "'"
                );
                $getFoldIfExist = $service_dest_cl->files->listFiles($optParams);
                $getFoldNameIfexist = false;
                $getFoldIdIfexist = false;
                foreach ($getFoldIfExist->getFiles() as $file) {
                    $getFoldIdIfexist = $file->getId();
                    $getFoldNameIfexist = $file->getName();
                }

                if ($getFoldNameIfexist == $v["name"]) {
                    $this->upload_gg_dir($v["childs"], $getFoldIdIfexist, $service_src_cl, $service_dest_cl, $transfert_type, $isAllfile_trandfered);
                } else {
                    $DriveFile = new Google_Service_Drive_DriveFile();
                    $DriveFile->setName($v["name"]);
                    $DriveFile->setParents(array($destFoldId));
                    $DriveFile->setMimeType('application/vnd.google-apps.folder');

                    $result = $service_dest_cl->files->create($DriveFile, array(
                        'fields' => 'id'
                    ));

                    if ($result->getId()) {
                        $this->upload_gg_dir($v["childs"], $result->getId(), $service_src_cl, $service_dest_cl, $transfert_type, $isAllfile_trandfered);
                    } else {
                        $isAllfile_trandfered = false;
                    }

                }

            }
        }
        return $isAllfile_trandfered;
    }

    private function gg_dir_r(&$fold_contents, $folderId, $service)
    {
        /* @var $service Google_Service_Drive */

        $isNextPageTokenAvalable = false;

        $results = null;
        $allPageArray = array();
        $count = 0;
        do {
            $optParams = array(
                'pageSize' => 50,
                'fields' => 'nextPageToken, files(id, name, mimeType)',
                'q' => "trashed = false and '" . $folderId . "' in parents"
            );
            if ($isNextPageTokenAvalable) {
                $optParams["pageToken"] = $isNextPageTokenAvalable;
            }
            $results = $service->files->listFiles($optParams);

            $allPageArray[$count] = $results->getFiles();
            $count++;
            $isNextPageTokenAvalable = $results->getNextPageToken();
        } while ($isNextPageTokenAvalable);

        $i = 0;
        foreach ($allPageArray as $pageFiles) {
            foreach ($pageFiles as $file) {
                /* @var $file Google_Service_Drive_DriveFile */
                if ($file->getMimeType() != "application/vnd.google-apps.folder") {
                    $fold_contents[$i]["id"] = $file->getId();
                    $fold_contents[$i]["name"] = $file->getName();
                    $fold_contents[$i]["type"] = "file";
                } else {
                    $fold_contents[$i]["id"] = $file->getId();
                    $fold_contents[$i]["name"] = $file->getName();
                    $fold_contents[$i]["type"] = "folder";
                    $this->gg_dir_r($fold_contents[$i]["childs"], $file->getId(), $service);
                }
                $i++;
            }
        }
    }

    private function ftp_ftp_transfert($condb, $file_to_transfert, $src_cloud_id, $dest_cloud_id, $folder_dest, $transfert_type)
    {
        /* @var $condb MysqlConnect */
        $fun_ret = false;

        $con = new MysqlConnect();

        $con->prepareAndBind('SELECT ftp.*, cloud.cloud_type FROM ftp JOIN cloud ON cloud.cloud_id = ftp.cloud_id AND ftp.cloud_id=?','i',$src_cloud_id);
        if ($con->secureExcecute()) {
            if ($con->get_num_rows_stm() == 1) {
                $src_cloud_row = $con->fetch_assoc_stm();

                $con->prepareAndBind('SELECT ftp.*, cloud.cloud_type FROM ftp JOIN cloud ON cloud.cloud_id = ftp.cloud_id AND ftp.cloud_id=?','i',$dest_cloud_id);

                if ($con->secureExcecute()) {
                    if ($con->get_num_rows_stm() == 1) {
                        $dest_cloud_row = $con->fetch_assoc_stm();

                        $server_src = str_replace('ftp://', '', $src_cloud_row["server"]);
                        $username_src = $src_cloud_row["ftp_user"];
                        $password_src = $src_cloud_row["ftp_pwd"];
                        $port_src = $src_cloud_row["port"];
                        $cloud_type_src = $src_cloud_row["cloud_type"];

                        $server_dest = str_replace('ftp://', '', $dest_cloud_row["server"]);
                        $username_dest = $dest_cloud_row["ftp_user"];
                        $password_dest = $dest_cloud_row["ftp_pwd"];
                        $port_dest = $dest_cloud_row["port"];
                        $cloud_type_dest = $dest_cloud_row["cloud_type"];

                        $file_type = 'file';
                        try {
                            if ($cloud_type_src === 'ftp') {
                                require_once __DIR__ . "/../Ftp/Ftp.php";
                                $ftp = new Ftp;
                                $ftp->connect(str_replace('ftp://', '', $server_src), $port_src);
                                $ftp->login($username_src, $password_src);

                                if ($ftp->isDir($file_to_transfert))
                                    $file_type = 'dir';

                            } else if ($cloud_type_src === 'ftps') {
                                require_once __DIR__ . "/../Ftp/Ftp.php";
                                $ftp = new Ftp;
                                $ftp->sslConnect(str_replace('ftp://', '', $server_src), $port_src);
                                $ftp->login($username_src, $password_src);

                                if ($ftp->isDir($file_to_transfert))
                                    $file_type = 'dir';

                            } else if ($cloud_type_src === 'sftp') {
                                require_once __DIR__ . "/../SFTP/phpseclib/vendor/autoload.php";
                                $sftp = new \phpseclib\Net\SFTP($server_src, $port_src);
                                $sftp->login($username_src, $password_src);

                                if ($sftp->is_dir($file_to_transfert))
                                    $file_type = 'dir';
                            }
                        } catch (FtpException $e) {
                            echo $e->getMessage();
                        }
                        //$way_res = shell_exec("mkdir -p /home/cloudi_log/$this->user/TEMP ; chmod -R 777 /home/cloudi_log/$this->user ; /opt/lampp/htdocs/ftp_ftp.sh $this->user $username_src $password_src $server_src $file_to_transfert $username_dest $password_dest $server_dest $folder_dest $port_src $port_dest $transfert_type 2> /opt/lampp/htdocs/log_down_err ");
                        $way_res = shell_exec("mkdir -p /home/cloudi_log/$this->user/TEMP ; chmod -R 777 /home/cloudi_log/$this->user ; sudo /opt/lampp/htdocs/ftp_ftps_sftp.sh $this->user $username_src $password_src $server_src $file_to_transfert $username_dest $password_dest $server_dest $folder_dest $port_src $port_dest $transfert_type $cloud_type_src $cloud_type_dest $file_type");

                        $fun_ret = true;
                    }
                }

            }
        }
        @$con->close_stm();
        @$con->close_Mysql_stm();
        return $fun_ret;
    }
}

