<?php
require_once __DIR__."/config.php";

class MysqlConnect2{
    private $con;
    private $result;
    private $numRows;
    private $status;
    // Establish connection to database
    public function __construct() {
        @$this->con = new mysqli(HOST_NAME, USER_NAME, PASSWORD, DATA_BASE_NAME, 3306, "/opt/lampp/var/mysql/mysql.sock");
        $this->status = true;
        if ($this->con->connect_error) {
            $this->status = false;
        }
    }

    public function mysql_is_connected (){
        return $this->status;
    }

    // Sends the query to the connection
    public function send_query($sql) {
        if ($this->mysql_is_connected()) {
            $this->con->query("SET NAMES 'utf8'");
            $this->con->query("SET CHARACTER SET 'utf8'");
            $this->con->set_charset('utf8mb4');
            $this->result = $this->con->query($sql) or die(mysqli_error($this->con));
            return $this->result;
        }else{
            return false;
        }
    }

    // Return the number of rows
    public function get_num_rows() {
        $this->numRows = mysqli_num_rows($this->result);
        return $this->numRows;
    }

    // Fetchs the rows and return them
    public function fetch_assoc() {
        return  mysqli_fetch_assoc($this->result);
    }

    // Used by other classes to get the connection
    public function getLink() {
        return $this->con;
    }

    // Close database connection
    public function set_close() {
        @$this->con->close();
    }
}
?>