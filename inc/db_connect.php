<?php
 
class DbConnect
{
    //Variable to store database link1
    private $con;
 
    //Class constructor
    function __construct()
    {
 
    }
 
    //This method will connect to the database
    function connect()
    {
        //Including the constants.php file to get the database constants
        include_once dirname(__FILE__) . '/const.php';
 
        //connecting to mysql database
        $this->con = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
        //Checking if any error occured while connecting
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error(); 
            return null;
        }
		
		$this->con->set_charset("utf8");
 
        //finally returning the connection link
        return $this->con;
    }
 
}
?>