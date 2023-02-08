<?php

require_once  './inc/wow_api.php';
$wow_api = new wow_api();

//get_oauth_token
if($_SERVER['SERVER_NAME'] != "localhost"){


if (isset($_GET['api_token'])) {
    if (!str_contains($_GET['api_token'], API_TOKEN)) {
        die("TOKEN ERROR");
    }
} else {
    die("ERROR");
}
}
if(isset($_POST['reload_char'])) {
    $wow_api->char_updater(true,true,true,true,array($_POST['reload_char']));
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
    <table>
        <thead>
            <tr>
                <th colspan="11">Informationen</th>
                <th colspan="18">Itemlevel</th>
                <th colspan="9">Enchants</th>
                <th colspan="6">Items & Sets</th>
                <th colspan="4">Raid Progress</th>
                <th colspan="4">Raid Lockouts</th>
                <th colspan="3">M+ Daten & Chestdaten</th>
            </tr>
            <tr>
                <th>Aktion</th>
                <th>Realm</th>
                <th>Name</th>
                <th>Fraktion</th>
                <th>Guild</th>
				<th>Guild Rank</th>
                <th>Klasse</th>
                <th>Spec</th>
                <th>Level</th>
                <th>Erfolge</th>
                <th>Eingeloggt am</th>


                <th>ILVL Tasche</th>
                <th>ILVL EQ</th>
                <th>Kopf</th>
                <th>Kette</th>
                <th>Schultern</th>
                <th>Umhang</th>
                <th>Brust</th>
                <th>Armschienen</th>
                <th>Hände</th>
                <th>Gürtel</th>
                <th>Beine</th>
                <th>Füße</th>
                <th>Ring 1</th>
                <th>Ring 2</th>
                <th>Trinket 1</th>
                <th>Trinket 2</th>
                <th>Waffe</th>
                <th>Nebenhand</th>

                <th>Kette</th>
                <th>Umhang</th>
                <th>Brust</th>
                <th>Armschienen</th>
                <th>Beine</th>
                <th>Füße</th>
                <th>Ring 1</th>
                <th>Ring 2</th>
                <th>Waffe</th>

                <th>Setitems</th>
                <th>Ring 1</th>
                <th>Ring 2</th>
                <th>Trinket 1</th>
                <th>Trinket 2</th>
                <th>Waffe</th>

                <th>LFR</th>
                <th>Normal</th>
                <th>Heroisch</th>
                <th>Mythisch</th>

                <th>LFR</th>
                <th>Normal</th>
                <th>Heroisch</th>
                <th>Mythisch</th>
                
                <th>Raiting</th>
				
				<th>M+ Chest</th>
				<th>Raid Chest</th>
            </tr>
        </thead>
        <tbody>
            <?php
            echo $wow_api->print_data();

            ?>
        </tbody>
    </table>
</body>

</html>