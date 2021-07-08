<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 7/3/20
 * Time: 4:14 PM
 */
require __DIR__ . '/vendor/autoload.php';

function global_gg_params (){
    $cl = new Google_Client();
    $cl->setApplicationName('Google Drive API');
    $cl->setScopes(Google_Service_Drive::DRIVE);
    $cl->setAuthConfig(__DIR__ . '/credentials.json');
    $cl->setAccessType('offline');
    //$cl->setPrompt('force');
    $cl->setApprovalPrompt('force');

    return $cl;
}

function get_uri_for_access_token ($id_redirect){

    $client = global_gg_params();
    $client->setState($id_redirect);
    $authUrl = $client->createAuthUrl();

    return $authUrl;
}

function show_gg_drive_content ($con, $clitem, $folderID = 'root'){
    /* @var $con MysqlConnect */

    $resArray = array();

    $cheTokenAvailability = get_access_token_availability($client,$con,$clitem);
    if (!$cheTokenAvailability["result"]){
        if ($cheTokenAvailability["message"] == "Access denied"){
            $resArray["result"] = 11;  // access denied
        }else if ($cheTokenAvailability["message"] == "User must refresh accsess token"){
            $resArray["result"] = 10;  // you have to authentificate from google
        }
        return $resArray;
    }

    $service = new Google_Service_Drive($client);


    $isNextPageTokenAvalable = false;

    $results = null;
    $allPageArray = array();
    $count = 0;
    do {
        $optParams = array(
            'pageSize' => 50,
            'fields' => 'nextPageToken, files(id, name, mimeType, modifiedTime, size, parents)',
            'q' => "trashed = false and '".$folderID."' in parents"
        );
        if ($isNextPageTokenAvalable){
            $optParams["pageToken"] = $isNextPageTokenAvalable;
        }
        $results = $service->files->listFiles($optParams);

        $allPageArray[$count] = $results->getFiles();
        $count++;
        $isNextPageTokenAvalable = $results->getNextPageToken();
    } while ($isNextPageTokenAvalable);

    $action_buttons = "<div id='action-btn'>
                            <button type=\"button\" id='cp' class=\"btn btn-primary disabled\" onclick='files_to_transfert(this".",\"".$clitem."\")'>
                                <span class=\"fa fa-copy\" ></span> copy
                            </button>
                            <button type=\"button\" id='mv' class=\"btn btn-primary disabled\"  onclick='files_to_transfert(this".",\"".$clitem."\")'>
                                <span class=\"fa fa-cut\" ></span> move
                            </button>
                            <button type=\"button\" id='pst' class=\"btn btn-primary disabled\" onclick='proceed_transfert(this".",\"".$folderID."\",".$clitem.")'>
                                <span class=\"fa fa-paste\" ></span> paste
                            </button>
                       </div><br>";

    $a = "";
    if ($folderID != "root"){
        $sservice = clone $service;
        $Params = array(
            'fields' => 'parents'
        );
        $a = $sservice->files->get($folderID,$Params)->getParents();

        if (!isset($a[0])){
            $a [0] = 'root';
        }

        $action_buttons .= "<form action=\"#\" method=\"POST\" class=\"cl-item\">
                            <input type=\"hidden\" name=\"gg_clitem\" value=\"" . $clitem . "\">
                            <input type=\"hidden\" name=\"gg_fold_to_open_id\" value=\"" . $a[0] . "\">
                            <button type=\"submit\" name=\"sub\" value='' class=\"btn btn-light\"><span class=\"fa fa-arrow-left\" ></span></button>
                        </form><br>";
    }else {
        $action_buttons .= "<form ><button type=\"submit\" name=\"sub\" value='' class=\"btn btn-light\" disabled><span class=\"fa fa-arrow-left\" ></span></button>
                        </form><br>";
    }

    $head = "<div class=\"table-responsive\">
    <div id=\"main-table_wrapper\" class=\"dataTables_wrapper\">
        <table class=\"table table-bordered table-hover table-sm bg-white dataTable\" id=\"main-table\" role=\"grid\">
            <thead class=\"thead-white\">
                <tr role=\"row\">
                    <th style=\"width: 24.2px;\">
                        <div class=\"custom-control custom-checkbox\">
                            <input type=\"checkbox\" class=\"custom-control-input\" id=\"js-select-all-items\" onclick=\"checkbox_toggle()\">
                            <label class=\"custom-control-label\" for=\"js-select-all-items\"></label>
                        </div>
                    </th>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Modified</th>
                </tr>
            </thead>
            <tbody>";

    $rows = "";
    foreach ($allPageArray as $pageFiles){
        foreach ($pageFiles as $file) {
            $d = new DateTime($file->getModifiedTime());
            $modidiedTime = $d->format('Y-m-d H:i');
            if ($file->getSize()){
                $img = '<img src="http://'. $_SERVER['HTTP_HOST'] .'/style/img/file.png">';
                $rows .= "<tr role=\"row\">
                    <td class=\"custom-checkbox-td\">
                        <div class=\"custom-control custom-checkbox\">
                            <input type=\"checkbox\" class=\"custom-control-input\" id=\"" . $file->getId() . "\" name=\"file[]\" value=\"" . $file->getId() . "\" onclick=\"checkbox_items(this)\">
                            <label class=\"custom-control-label\"  for=\"" . $file->getId() . "\"></label>
                        </div>
                    </td>
                    <td>
                            <button class=\"not-button-like-a\">" . $img . "  " . $file->getName() . "</button>
                        
                    </td>
                    <td>" . $file->getSize() . "</td>
                    <td>" . $modidiedTime . "</td>
                </tr>";
            } else {
                if ($file->getMimeType() == "application/vnd.google-apps.folder"){
                    $img = '<img src="http://'. $_SERVER['HTTP_HOST'] .'/style/img/folder.png">';
                    $rows .= "<tr role=\"row\">
                    <td class=\"custom-checkbox-td\">
                        <div class=\"custom-control custom-checkbox\">
                            <input type=\"checkbox\" class=\"custom-control-input\" id=\"" . $file->getId() . "\" name=\"file[]\" value=\"" . $file->getId() . "\" onclick=\"checkbox_items(this)\">
                            <label class=\"custom-control-label\"  for=\"" . $file->getId() . "\"></label>
                        </div>
                    </td>
                    <td><form action=\"#\" method=\"POST\" class=\"cl-item\">
                            <input type=\"hidden\" name=\"gg_clitem\" value=\"" . $clitem . "\">
                            <input type=\"hidden\" name=\"gg_fold_to_open_id\" value=\"" . $file->getId() . "\">
                            <button type=\"submit\" name=\"sub\" value='' class=\"button-like-a\">" . $img . "  " . $file->getName() ."</button>
                        </form>
                    </td>
                    <td>Folder</td>
                    <td>" . $modidiedTime . "</td>
                </tr>";
                }
            }
        }
    }

    $rows .= "    </tbody>
                </table>
               </div>
             </div>";

    $resArray["result"] = 12; //success
    $resArray["output"] = $action_buttons.$head.$rows;
    return $resArray;
}

function get_access_token_availability(&$client, $con, $clitem){
    /* @var $client Google_Client */
    /* @var $con MysqlConnect */
    $resArray["result"] = true;

    $client = global_gg_params ();

    $con->prepareAndBind('SELECT * FROM gg_drive WHERE cloud_id = ?','i',$clitem);
    if ($con->secureExcecute()) {
        $row = $con->fetch_assoc_stm();
        $con->close_stm();

        $accessToken = json_decode($row["access_token"], true);
        $client->setAccessToken($accessToken);
        if ($client->isAccessTokenExpired()) {
            $tokenRefreshed = $client->getRefreshToken();
            if ($tokenRefreshed) {
                $client->fetchAccessTokenWithRefreshToken($tokenRefreshed);
            } else {
                if (array_key_exists('error', $client->getAccessToken())) {
                    $resArray["result"] = false;  // access denied
                    $resArray["message"] = "Access denied";
                } else {
                    $resArray["result"] = false;  // you have to authentificate from google
                    $resArray["message"] = "User must refresh accsess token";
                }
            }
            $accessToken = $client->getAccessToken();
            $accessToken["refresh_token"] = $tokenRefreshed;

            $con->prepareAndBind('UPDATE gg_drive SET access_token = ? WHERE cloud_id = ? ','si',json_encode($accessToken),$clitem);
            if (!$con->secureExcecute()){
                $resArray["result"] = false;  // access denied
                $resArray["message"] = "Access Token failed";
            }
        }
    } else {
        $resArray["result"] = false;  // access denied
        $resArray["message"] = "Connexion Failed";
    }
    return $resArray;
}