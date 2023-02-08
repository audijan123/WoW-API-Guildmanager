<?php

define("client_id", "wow_api_id");
define("client_secret", "wow_api_secret");

if($_SERVER['SERVER_NAME'] == "localhost")
{
    define('DB_HOST', '127.0.0.1');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'game_api');
}else{
    define('DB_HOST', 'server');
    define('DB_USERNAME', 'dbuser');
    define('DB_PASSWORD', 'dbpass');
    define('DB_NAME', 'dbname');
}





define('API_TOKEN', 'api_token"');
define('DEV_TOKEN', 'dev_token');