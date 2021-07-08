<?php
require_once __DIR__."/config.php";

class MysqlConnect{
    private $con;
    private $result;
    private $numRows;
    private $status;
    private $stm;
    public function __construct() {
        @$this->con = new mysqli(HOST_NAME, USER_NAME, PASSWORD, DATA_BASE_NAME, 3306, "/opt/lampp/var/mysql/mysql.sock");
        $this->status = true;
        if ($this->con->connect_error) {
            $this->status = false;
        }
    }

    public function secure_mysql_is_connected (){
        return $this->status;
    }

    public function prepareAndBind($query, $types, ...$bindParams) {
        $this->stm = $this->con->prepare($query);
        return $this->stm->bind_param($types, ...$bindParams);
    }

    public function secureExcecute(){
        /* @var $res bool */
        $res = $this->stm->execute();
        $this->result = $this->stm->get_result();
        return $res;
    }

    public function fetch_assoc_stm() {
        return  $this->result->fetch_assoc();
    }

    public function get_num_rows_stm() {
        $this->numRows = $this->result->num_rows;
        return $this->numRows;
    }

    public function getLink_Mysql(){
        return $this->con;
    }

    public function getLink_stm() {
        return $this->stm;
    }

    public function close_stm() {
        $this->stm->close();
    }
    public function close_Mysql_stm() {
        @$this->con->close();
    }
}
?>