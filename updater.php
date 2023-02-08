<?php

require_once  './inc/wow_api.php';
$wow_api = new wow_api();

//get_oauth_token
if($_SERVER['SERVER_NAME'] != "localhost")
{
if (isset($_GET['api_token'])) {
    if (!str_contains($_GET['api_token'], API_TOKEN)) {
        die("TOKEN ERROR");
    }
} else {
    die("ERROR");
}
}


?>
<html>
<meta charset="UTF-8">
<title>Datenupdate</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
<link rel="stylesheet" href="./style.css">

<head>

</head>

<body style="background: #000; color: #fff;">
    <?php 
	if (isset($_GET['update_guild_roster'])) {
        $wow_api->update_guild("","",true);
		} else if (isset($_GET['update_more_data'])) {
            $wow_api->char_updater(true,false,false,false);
} else if (isset($_GET['update_eq_data'])) {
    $wow_api->char_updater(false,true,false,false);
}else if (isset($_GET['update_raid_data'])) {
    $wow_api->char_updater(false,false,true,false);
}else if(isset($_GET['update_m_data'])){
    $wow_api->char_updater(false,false,false,true);
}else if(isset($_GET['update_all'])){
    $wow_api->update_all_data();
}

//update_char_raid_data
	
	?>
</body>

</html>