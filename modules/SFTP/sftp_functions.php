<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11/25/20
 * Time: 11:37 PM
 */

function show_sftp_content($ftp, $clitem, $folder)
{
    /**
     * @var $ftp \phpseclib\Net\SFTP
     */

    $ftp->chdir($folder);
    $file_list = $ftp->_list('.');

    $path = "<div>" . $ftp->pwd() . "</div>";

    $action_buttons = "<div id='action-btn'>
                            <button type=\"button\" id='cp' class=\"btn btn-primary disabled\" onclick='files_to_transfert(this".",\"".$clitem."\")'>
                                <span class=\"fa fa-copy\" ></span> copy
                            </button>
                            <button type=\"button\" id='mv' class=\"btn btn-primary disabled\"  onclick='files_to_transfert(this".",\"".$clitem."\")'>
                                <span class=\"fa fa-cut\" ></span> move
                            </button>
                            <button type=\"button\" id='pst' class=\"btn btn-primary disabled\" onclick='proceed_transfert(this".",\"".$ftp->pwd()."\",".$clitem.")'>
                                <span class=\"fa fa-paste\" ></span> paste
                            </button>
                       </div>";


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
                    <th>Perms</th>
                    <th>Owner</th>
                </tr>
            </thead>
            <tbody>";


    $rows = "";
    $copy_file_list = array();
    foreach ($file_list as $key => $value) {
        if ($value['filename'] == '.') {
            $copy_file_list[$key] = $value;
            break;
        }
    }
    foreach ($file_list as $key => $value) {
        if ($value['filename'] == '..') {
            $copy_file_list[$key] = $value;
            break;
        }
    }
    foreach ($file_list as $key => $value) {
        if ($value['filename'] != '.' && $value['filename'] != '..') {
            $copy_file_list[$key] = $value;
        }
    }
    $iscontainPointsName = false;
    foreach ($copy_file_list as $value) {
        $name = $value['filename'];
        $size = ($value['type'] == '2') ? 'Folder' : $value['size'];
        $img = ($size == 'Folder') ? '<img src="style/img/folder.png">' : '<img src="style/img/file.png">';
        $last_time = gmdate("Y-m-d H:i:s", $value['mtime']);
        $perm = substr(decoct($value['permissions']),2);
        $grp_own = @$value['uid'] . ":" . @$value['gid'];

        if ($size == 'Folder') {
            if ($name == '..' || $name == '.') {
                $iscontainPointsName = true;
                $rows .= "<tr role=\"row\">
                    <td class=\"custom-checkbox-td\">
                    </td>
                    <td><form action=\"#\" method=\"POST\" class=\"cl-item\">
                            <input type=\"hidden\" name=\"currect-dir\" value=\"" . $ftp->pwd() . "\">
                            <input type=\"hidden\" name=\"clitem\" value=\"" . $clitem . "\">
                            <input type=\"hidden\" name=\"fold_to_open\" value=\"" . $name . "\">
                            <button type=\"submit\" name=\"sub\" value='' class=\"button-like-a\">" . $img . "  " . $name . "</button>
                        </form>
                    </td>
                    <td>" . $size . "</td>
                    <td>" . $last_time . "</td>
                    <td>" . $perm . "</td>
                    <td>" . $grp_own . "</td>
                </tr>";
            } else {
                if ($ftp->pwd() == "/"){
                    $absolute_path = $ftp->pwd().$name;
                }else{
                    $absolute_path = $ftp->pwd() . "/" . $name;
                }
                $rows .= "<tr role=\"row\">
                    <td class=\"custom-checkbox-td\">
                        <div class=\"custom-control custom-checkbox\">
                            <input type=\"checkbox\" class=\"custom-control-input\" id=\"" . $name . "\" name=\"file[]\" value=\"" . $absolute_path . "\" onclick=\"checkbox_items(this)\">
                            <label class=\"custom-control-label\" for=\"" . $name . "\"></label>
                        </div>
                    </td>
                    <td><form action=\"#\" method=\"POST\" class=\"cl-item\">
                            <input type=\"hidden\" name=\"currect-dir\" value=\"" . $ftp->pwd() . "\">
                            <input type=\"hidden\" name=\"clitem\" value=\"" . $clitem . "\">
                            <input type=\"hidden\" name=\"fold_to_open\" value=\"" . $name . "\">
                            <button type=\"submit\" name=\"sub\" value='' class=\"button-like-a\">" . $img . "  " . $name . "</button>
                        </form>
                    </td>
                    <td>" . $size . "</td>
                    <td>" . $last_time . "</td>
                    <td>" . $perm . "</td>
                    <td>" . $grp_own . "</td>
                </tr>";
            }
        } else {

            if ($ftp->pwd() == "/"){
                $absolute_path = $ftp->pwd().$name;
            }else{
                $absolute_path = $ftp->pwd() . "/" . $name;
            }

            $rows .= "<tr role=\"row\">
                    <td class=\"custom-checkbox-td\">
                        <div class=\"custom-control custom-checkbox\">
                            <input type=\"checkbox\" class=\"custom-control-input\" id=\"" . $name . "\" name=\"file[]\" value=\"" . $absolute_path . "\" onclick=\"checkbox_items(this)\">
                            <label class=\"custom-control-label\"  for=\"" . $name . "\"></label>
                        </div>
                    </td>
                    <td>
                            <button class=\"not-button-like-a\">" . $img . "  " . $name . "</button>
                        
                    </td>
                    <td>" . $size . "</td>
                    <td>" . $last_time . "</td>
                    <td>" . $perm . "</td>
                    <td>" . $grp_own . "</td>
                </tr>";
        }

    }
    $rows .= "</tbody>
        </table>
    </div>
</div>";
    if (!$iscontainPointsName){
        $img = '<img src="style/img/folder.png">';
        $names = array('..','.');
        foreach ($names as $name) {
            $rows = "<tr role=\"row\">
                    <td class=\"custom-checkbox-td\">
                    </td>
                    <td><form action=\"#\" method=\"POST\" class=\"cl-item\">
                            <input type=\"hidden\" name=\"currect-dir\" value=\"" . $ftp->pwd() . "\">
                            <input type=\"hidden\" name=\"clitem\" value=\"" . $clitem . "\">
                            <input type=\"hidden\" name=\"fold_to_open\" value=\"" . $name . "\">
                            <button type=\"submit\" name=\"sub\" value='' class=\"button-like-a\">" . $img . "  " . $name . "</button>
                        </form>
                    </td>
                    <td>--</td>
                    <td>--</td>
                    <td>--</td>
                    <td>--</td>
                </tr>" . $rows;
        }
    }

    return $action_buttons . $path . $head . $rows;
}