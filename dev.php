<?php

require_once  './inc/wow_api.php';
$wow_api = new wow_api();

//get_oauth_token
if($_SERVER['SERVER_NAME'] != "localhost") {
if (isset($_GET['dev_token'])) {
    if (!str_contains($_GET['dev_token'], DEV_TOKEN)) {
        die("TOKEN ERROR");
    }
} else {
    die("ERROR");
}
}

?>
<html>
<meta charset="UTF-8">
<title>Übersicht</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
<link rel="stylesheet" href="./style.css">

<head>

</head>

<body style="background: #000; color: #fff;">
    <form method="POST">
        <input type="text" name="exec_url">
        <button type="submit" name="exec_btn" value="1">Ausführen</button>
    </form>
    <hr>
    <?php
    if(isset($_POST['exec_btn']))
    {
        echo $wow_api->exec_url($_POST['exec_url']);
    }
    
    ?>
</body>

</html>