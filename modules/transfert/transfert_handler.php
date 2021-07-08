<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/11/20
 * Time: 7:20 PM
 */

if (defined('STDIN')) {
    if (isset($argv[1])){
        include_once $_SERVER["DOCUMENT_ROOT"]."include/MysqlConnect.php";
        include_once $_SERVER["DOCUMENT_ROOT"]."modules/transfert/Transfert.php";

        $con = new MysqlConnect();

        if ($con->secure_mysql_is_connected()) {

            $con->prepareAndBind('SELECT * FROM files_to_move WHERE user_id = ?', 'i', $argv[1]);

            if ($con->secureExcecute()) {
                if ($con->get_num_rows_stm() > 0) {
                    $con->close_stm();

                    $con->prepareAndBind('SELECT * FROM transfert_log WHERE user_id = ?','i',$argv[1]);
                    if ($con->secureExcecute()) {
                        if ($con->get_num_rows_stm() == 0) {
                            $con = new MysqlConnect();

                            $trs_handel = new Transfert($con, $argv[1]);
                            $trs_handel->execute_transfert();
                        }
                    }
                }
            }
        }
    }
}