<?php
include_once 'const.php';
include_once 'basic_function.php';
class wow_api
{
    //current guild
    private $current_realm = "blackmoore";
    private $current_guild_name = "orgrimmars-wächter";


    //API SETTINGS
    private $namespace_profile = "profile-eu";
    private $current_lang = "de_DE";
   

    //ITEM SETTINGS
    private $avaible_eq = array("head", "neck", "shoulder", "back", "chest",  "wrist", "hands", "waist", "legs", "feet", "finger_1", "finger_2", "trinket_1", "trinket_2", "main_hand", "off_hand");
    private $filter_enchant = array("neck", "back", "chest", "wrist",  "legs", "feet", "finger_1", "finger_2", "main_hand");
    private $ignore_items = array("SHIRT", "TABARD");


    //CHEST DATA
    private $chest_level_per_key_level = array("FEHLER", "FEHLER", "382", "385", "385", "389", "389", "392", "395", "395", "398", "402", "405", "408", "408", "411", "415", "415", "418", "418", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421", "421");
    private $chest_count_per_key_count = array(0, 3, 7); //+1

    //WOW ID LISTS
    private $current_t_set_ids = array("1526", "1527", "1528", "1529", "1530", "1531", "1532", "1533", "1534", "1535", "1536", "1537", "1538");
    private $wow_DK_VZ_ID_LIST = array("3368", "3370", "3847", "3369", "3883", "3366", "3595", "3367", "3594", "3365");


    //wow API URLS
    private $oauth_url = "https://eu.battle.net/oauth/token";
    private $wow_api_guild_roster = "https://eu.api.blizzard.com/data/wow/guild/";
    private $wow_api_profile_main = "https://eu.api.blizzard.com/profile/wow/character/";

    //CONSTRUCTOR
    private $con;
    function __construct()
    {
        require_once dirname(__FILE__) . '/db_connect.php';

        $db = new DbConnect();
        $this->con = $db->connect();
    }

    //MAIN FUNCTIONS DONT TOUCH
    public function api_mysql_query($query)
    {
        if ($result = $this->con->query($query)) {
            return $result;
        } else {
            return false;
        }
    }

    public function get_value($value_id, $id_name, $colum, $table)
    {
        if ($result = $this->con->query("SELECT " . $colum . " FROM " . $table . " WHERE " . $id_name . "='" . $value_id . "' LIMIT 1")) {
            $row = mysqli_fetch_array($result);

            return $row[$colum];
        } else {
            return "Fehler";
        }
    }

    public function update_value($value_id, $id_name, $update_value, $update_name, $table)
    {
        if ($result = $this->con->query("UPDATE " . $table . " SET " . $update_name . " ='" . $update_value . "' WHERE " . $id_name . " = '" . $value_id . "'")) {
            return true;
        } else {
            return false;
        }
    }

    public function delete_value($value_id, $id_name, $table)
    {

        if ($result = $this->con->query("Delete FROM " . $table . " WHERE " . $id_name . "='" . $value_id . "'")) {

            return true;
        } else {
            return false;
        }
    }

    public function _EQ($var)
    {
        return mysqli_real_escape_string($this->con, $var);
    }


    public function exec_url($url)
    {
        $current_token = $this->get_oauth_token();
        $request_url = $url . "&locale=" . $this->current_lang . "&access_token=" . $current_token;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $request_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        return curl_exec($curl);
    }


    //AUTH
    public function get_oauth_token()
    {
        static $auth_token = "";
        if ($auth_token == "") {
            $url = $this->oauth_url;
            $params = ['grant_type' => 'client_credentials'];
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            curl_setopt($curl, CURLOPT_USERPWD, client_id . ':' . client_secret);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = json_decode(curl_exec($curl));
            curl_close($curl);
            $auth_token = $result->access_token;
        }
        return $auth_token;
    }
    //API GET
    public function get_data($url)
    {
        $current_token = $this->get_oauth_token();
        $request_url = $url . "&access_token=" . $current_token;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $request_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = json_decode(curl_exec($curl), true);

        return $result;
    }


    ###END


    //SAVE FUNCTIONS
    public function save_char_information($uid, $more_data)
    {
        //prepare data
        $guild_name = "Keine Gilde";
        $old_char_data =  $this->get_value($uid,"uid","char_data","chars");
        $current_ilvl = 0;
       
        if($old_char_data != "" && $old_char_data != " " && $old_char_data != "[]"){
            $old_char_data_decode = json_decode($old_char_data,true);
            $current_ilvl = intval($old_char_data_decode['lvl_bag']);#
           
        }
        if(isset($more_data['average_item_level'])){
        if(intval($more_data['average_item_level']) > $current_ilvl){
            $current_ilvl = intval($more_data['average_item_level']);
            //gs change
            $old_history_data = $this->get_value($uid,"uid","history","chars");
            if($old_history_data != "" && $old_history_data != " " && $old_history_data != "[]"){
                $old_history_data_decode = json_decode($old_history_data,true);
                array_push($old_history_data_decode["gs_history"],array("date" => time(),"gs" => $current_ilvl));
            }else{
                $old_history_data_decode = array("gs_history" => array(),"m_history"=> array(),"m_chest_history"=> array(),"raid_chest_history"=> array(),"m_raiting_history"=> array());
                array_push($old_history_data_decode["gs_history"],array("date" => time(),"gs" => $current_ilvl));
            }

            if (!$this->update_value($uid, "uid", json_encode($old_history_data_decode, JSON_UNESCAPED_UNICODE), "history", "chars")) {
                return false;
            } 
        }
        }
        if(isset($more_data['guild']['name'])) {
            $guild_name = $more_data['guild']['name'];
        }
        if (isset($more_data['faction']['name'])) {
            $array_more_data = array(
                "fraktion" => $more_data['faction']['name'],
                "guild" => $guild_name,
                "klasse" => $more_data['character_class']['name'],
                "level" => $more_data['level'],
                "spec" =>  $more_data['active_spec']['name'],
                "erfolge" => $more_data['achievement_points'],
                "last_login" => $more_data['last_login_timestamp'],
                "lvl_bag" => $current_ilvl,
                "lvl_current" => $more_data['equipped_item_level'],
            );

            //check if exist
            if ($this->update_value($uid, "uid", json_encode($array_more_data, JSON_UNESCAPED_UNICODE), "char_data", "chars")) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function save_char_items($uid, $eq_data)
    {
        $array_enchant_data = array();
        if (isset($eq_data['equipped_items'])) {
            foreach ($eq_data['equipped_items'] as $eq_item) {
                //ilvl update
                $array_item_data = array(
                    "name" => str_replace(array("'"), " ", $eq_item['name']),
                    "lvl" => $eq_item['level']['value'],
                );

                if (isset($eq_item['sockets'])) {
                    $array_item_data['sockets'] = count($eq_item['sockets']);
                } else {
                    $array_item_data['sockets'] = 0;
                }

                if (isset($eq_item['enchantments'])) {
                    if (in_array($eq_item['enchantments'][0]['enchantment_id'], $this->wow_DK_VZ_ID_LIST)) {
                        $array_item_data['enchantments'] = array("name" => "DK VZ", "lvl" => 3);
                    } else {
                        $enchant_data =  explode("|", $eq_item['enchantments'][0]['display_string']);
                        $enchant_level = 0;
                        if (isset($enchant_data[1])) {
                            if (str_contains($enchant_data[1], "Tier3")) {
                                $enchant_level = 3;
                            } else if (str_contains($enchant_data[1], "Tier2")) {
                                $enchant_level = 2;
                            } else if (str_contains($enchant_data[1], "Tier1")) {
                                $enchant_level = 1;
                            }
                        }

                        $array_item_data['enchantments'] = array("name" => $enchant_data[0], "lvl" => $enchant_level);
                    }
                } else {
                    $array_item_data['enchantments'] = array();
                }

                if (isset($eq_item['set']) && !empty($eq_item['set'])) {
                    if (in_array($eq_item['set']['item_set']["id"], $this->current_t_set_ids)) {
                        $array_item_data['is_set'] = true;
                    } else {
                        $array_item_data['is_set'] = false;
                    }
                } else {
                    $array_item_data['is_set'] = false;
                }

                if (!in_array($eq_item['slot']['type'], $this->ignore_items)) {


                    if (!$this->update_value($uid, "uid", json_encode($array_item_data, JSON_UNESCAPED_UNICODE), $eq_item['slot']['type'], "chars")) {
                        return false;
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }


    public function save_guild_member($char_data)
    {
        $charname = $char_data['character']['name'];
        if ($result = $this->api_mysql_query("SELECT * FROM chars WHERE name='" . $charname . "'")) {
            if ($result->num_rows > 0) {
                //update_member
                $row = mysqli_fetch_array($result);
                if ($row['guild_rank'] != $char_data['rank']) {
                    if ($this->update_value($row['uid'], "uid", $char_data['rank'], "guild_rank", "chars")) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return true;
                }
            } else {
                //create_guild_member
                if ($this->api_mysql_query("INSERT INTO `chars`(`name`,`realm`,`guild_rank`) VALUES ('" . $char_data['character']['name'] . "','" . $char_data['character']['realm']['slug'] . "','" . $char_data['rank'] . "')")) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    //END


    //UPDATE DATA FUNCTION
    public function update_all_data(){
        //update_roaster
        echo "<p class='output'>#### Update Gildendaten ###</p>";
        $this->update_guild("","",false);
        echo "<p class='output success'>#### Gildendaten wurden erneuert###</p>";
        //update_char
        $this->char_updater(true,true,true,true);
    }


    public function char_updater($update_personal_data = false,$update_item_data = false,$update_raid_data = false, $update_m_data = false, $update_only_uids = array()){
            if ($result = $this->api_mysql_query("SELECT uid,name,realm FROM chars")) {
                if ($result->num_rows > 0) {
                    $current_char_index = 0;
                    $count_member_to_update = $result->num_rows;
                    if(!empty($update_only_uids)){
                        $count_member_to_update = count($update_only_uids);
                    }
                  
                    echo "<p class='output'>#### (0/".$count_member_to_update.") Charaktere ###</p>";
                    while ($row = mysqli_fetch_array($result)) {
                        if(empty($update_only_uids) || in_array($row['uid'],$update_only_uids)){
                            $current_char_index++;
                        echo "<hr><p class='output'>####(".$current_char_index."/".$count_member_to_update.") Lade Charakter Update für: " . $row['name'] . "###</p>";
                        if ($row['name'] != "" && $row['realm'] != "") {
                        if($update_personal_data){
                            $char_more_data = $this->get_data($this->wow_api_profile_main . basic_func_convert_name_to_url($row['realm']) . "/" . basic_func_convert_name_to_url($row['name']) . "?namespace=".$this->namespace_profile."&locale=" . $this->current_lang);
                            if($this->save_char_information($row['uid'], $char_more_data)){
                                echo "<p class='output success'>".$row['name'] . ": Carakterinformationen wurden erfolgreich aktualisiert</p>";
                            }else{
                                echo "<p class='output danger'>".$row['name'] . ": Carakterinformationen konnten nicht erfolgreich aktualisiert werden</p>";
                            }
                        }
                        if($update_item_data){
                            $char_eq_data = $this->get_data($this->wow_api_profile_main . basic_func_convert_name_to_url($row['realm']) . "/" . basic_func_convert_name_to_url($row['name']) . "/equipment?namespace=".$this->namespace_profile."&locale=" . $this->current_lang);
                            if($this->save_char_items($row['uid'], $char_eq_data)){
                                echo "<p class='output success'>".$row['name'] . ": Carakter Iteminformationen wurden erfolgreich aktualisiert</p>";
                            }else{
                                echo "<p class='output danger'>".$row['name'] . ": Carakter Iteminformationen konnten nicht erfolgreich aktualisiert werden</p>";
                            }
                        }

                        if($update_raid_data){
                            if($this->update_raid_data($row['name'], $row['uid'], $row['realm'])){
                                echo "<p class='output success'>".$row['name'] . ": Carakter Raidinformationen wurden erfolgreich aktualisiert</p>";
                            }else{
                                echo "<p class='output danger'>".$row['name'] . ": Carakter Raidinformationen konnten nicht erfolgreich aktualisiert werden</p>";
                            }
                        }

                        if($update_m_data){
                            if(  $this->update_raiting_data($row['name'], $row['uid'], $row['realm'])){
                                echo "<p class='output success'>".$row['name'] . ": Carakter Mythic Plus Daten wurden erfolgreich aktualisiert</p>";
                            }else{
                                echo "<p class='output danger'>".$row['name'] . ": Carakter Mythic Plus Daten konnten nicht erfolgreich aktualisiert werden</p>";
                            }
                        }

                        echo "<p class='output'>#### Charakter Update für: " . $row['name'] . " wurde erfolgreich durchgeführt ###</p>";
                        }else{
                            echo "<p class='output danger'>#### Charakter Update für: " . $row['name'] . " konnte nicht durchgeführt werden - Realm oder Name ungültig ###</p>";
                        }
                    }
                }
                echo "<p class='output'>#### (".$count_member_to_update."/".$count_member_to_update.") Update Fertig ###</p>";
                return true;
                }
            }else{
                return false;
            }
        
    }

    public function update_guild($guildname = "", $realm = "", $output)
    {
        if ($guildname == "") {
            $guildname = $this->current_guild_name;
        }
        if ($realm == "") {
            $realm = $this->current_realm;
        }
        if ($output) {
            echo "<p class='output'>Update Guildroster</p>";
        }
        $response = $this->get_data($this->wow_api_guild_roster . "/" . $realm . "/" . urlencode($guildname) . "/roster?namespace=" . $this->namespace_profile . "&locale=" . $this->current_lang);
        if ($output) {
            echo "<p class='output'>Add member to guild</p>";
        }
        $current_update_char = 0;
        $max_update_char = count($response['members']);
        foreach ($response['members'] as $guildmate) {
            if(!$this->save_guild_member($guildmate)){
                echo "<p  class='output danger'>Fehler:" . $char_data['character']['name'] . " konnte nicht geupdatet werden!</p>";
             
            }
        }

        if ($output) {

            echo "<p  class='output'>Update Guild finish</p>";
        }
    }

    public function update_raiting_data($name, $uid, $realm)
    {
        $char_m_plus_data = $this->get_data($this->wow_api_profile_main . basic_func_convert_name_to_url($realm) . "/" . basic_func_convert_name_to_url($name) . "/mythic-keystone-profile?namespace=".$this->namespace_profile."&locale=" . $this->current_lang);
        if (!empty($char_m_plus_data) && isset($char_m_plus_data['current_mythic_rating']) && !empty($char_m_plus_data['current_mythic_rating'])) {
            $m_chest_str = $this->extract_m_chest($char_m_plus_data);
            if ($this->update_value($uid, "uid", $char_m_plus_data['current_mythic_rating']['rating'], "raiting", "chars") && $this->update_value($uid, "uid", $m_chest_str, "m_chest", "chars")) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function update_raid_data($name, $uid, $realm)
    {
        $member_raid_data = $this->get_data($this->wow_api_profile_main . "/" . basic_func_convert_name_to_url($realm) . "/" . basic_func_convert_name_to_url($name) . "/encounters/raids?namespace=".$this->namespace_profile."&locale=" . $this->current_lang);
        $array_lfr_data = array();
        $array_normal_data = array();
        $array_hc_data = array();
        $array_m_data = array();
        if (isset($member_raid_data['expansions'])) {
            foreach ($member_raid_data['expansions'] as $expansion_data) {
                if ($expansion_data['expansion']['name'] == "Dragonflight") {
                    //var_dump(json_encode($expansion_data['instances']));
                    foreach ($expansion_data['instances'][0]['modes'] as $raid_level) {
                        //echo $raid_level['difficulty']['name'];
                        switch ($raid_level['difficulty']['type']) {
                            case 'LFR':
                                foreach ($raid_level['progress']['encounters'] as $encounter) {
                                    array_push($array_lfr_data, array("name" => $encounter["encounter"]["name"], "kill_count" => $encounter["completed_count"], "last_kill" => $encounter["last_kill_timestamp"]));
                                }
                                break;
                            case 'NORMAL':
                                foreach ($raid_level['progress']['encounters'] as $encounter) {
                                    array_push($array_normal_data, array("name" => $encounter["encounter"]["name"], "kill_count" => $encounter["completed_count"], "last_kill" => $encounter["last_kill_timestamp"]));
                                }
                                break;
                            case 'HEROIC':
                                foreach ($raid_level['progress']['encounters'] as $encounter) {
                                    array_push($array_hc_data, array("name" => $encounter["encounter"]["name"], "kill_count" => $encounter["completed_count"], "last_kill" => $encounter["last_kill_timestamp"]));
                                }
                                break;
                            case 'MYTHIC':
                                foreach ($raid_level['progress']['encounters'] as $encounter) {
                                    array_push($array_m_data, array("name" => $encounter["encounter"]["name"], "kill_count" => $encounter["completed_count"], "last_kill" => $encounter["last_kill_timestamp"]));
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
            $array_raid_data = array("lfr" => $array_lfr_data, "normal" => $array_normal_data, "hc" => $array_hc_data, "m" => $array_m_data);
            if ($this->update_value($uid, "uid", json_encode($array_raid_data, JSON_UNESCAPED_UNICODE), "raid_data", "chars")) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //END

    //PRINT DATA 
    public function print_data()
    {
        $table_str = "";
        $last_id_reset_timestamp = basic_func_get_last_reset_time();
        if ($result = $this->api_mysql_query("SELECT * FROM chars")) {
            if ($result->num_rows > 0) {
                while ($row = mysqli_fetch_array($result)) {
                    //print member
                    $char_info = array();
                    if ($row['char_data'] != "" && $row['char_data'] != " " && $row['char_data'] != "[]") {
                        $char_info = json_decode($row['char_data'], true);
                    }
                    $char_eq = basic_func_convert_eq_data($row,$this->avaible_eq);

                    $char_raid = array();
                    if ($row['raid_data'] != "" && $row['raid_data'] != " " && $row['raid_data'] != "[]") {
                        $char_raid = json_decode($row['raid_data'], true);
                    }


                    $table_str .= "<tr>";
                    $table_str .= "<td><form method='POST'><button type='submit' name='reload_char' value='" . $row['uid'] . "'>Neuladen</button></form></td>";
                    $table_str .= "<td>" . $row['realm'] . "</td>";
                    $table_str .= "<td>" . $row['name'] . "</td>";
                    if (!empty($char_info) && $char_info['fraktion'] != "") {
                        $table_str .= "<td>" . $char_info['fraktion'] . "</td>";
                        $table_str .= "<td>" . $char_info['guild'] . "</td>";
                    } else {
                        $table_str .= "<td colspan='2'>Keine Chardaten</td>";
                    }
                    $table_str .= "<td>" . $row['guild_rank'] . "</td>";
                    if (!empty($char_info) && $char_info['fraktion'] != "") {
                        $table_str .= "<td>" . $char_info['klasse'] . "</td>";
                        $table_str .= "<td>" . $char_info['spec'] . "</td>";
                        $table_str .= "<td>" . $char_info['level'] . "</td>";
                        $table_str .= "<td>" . $char_info['erfolge'] . "</td>";
                        $table_str .= "<td>" . basic_func_format_last_login($char_info['last_login']) . "</td>";
                        $table_str .= "<td>" . $char_info['lvl_bag'] . "</td>";
                        $table_str .= "<td>" . $char_info['lvl_current'] . "</td>";
                    } else {
                        $table_str .= "<td colspan='7'>Keine Chardaten</td>";
                    }
                    //print ilvl
                    if (!empty($char_eq)) {
                        foreach ($this->avaible_eq as $eq_pices) {
                            if (isset($char_eq[$eq_pices])) {
                                if (isset($char_eq[$eq_pices]['lvl'])) {
                                    $table_str .= "<td>" . $char_eq[$eq_pices]['lvl'] . "</td>";
                                } else {
                                    $table_str .= "<td>Kein Item</td>";
                                }
                            } else {
                                $table_str .= "<td>Kein Item</td>";
                            }
                        }
                    } else {
                        $table_str .= "<td colspan='16'>Keine EQDaten</td>";
                    }

                    //print enchants
                    //filter_enchant
                    if (!empty($char_eq)) {
                        foreach ($this->filter_enchant as $current_enchant) {
                            if (isset($char_eq[$current_enchant])) {
                                if ($current_enchant == "neck") {
                                    if (isset($char_eq[$current_enchant]['sockets'])) {
                                        $table_str .= "<td>" . $char_eq[$current_enchant]['sockets'] . "</td>";
                                    } else {
                                        $table_str .= "<td>0</td>";
                                    }
                                } else {
                                    if (isset($char_eq[$current_enchant]['enchantments']) && !empty($char_eq[$current_enchant]['enchantments'])) {
                                        $table_str .= "<td>" . $char_eq[$current_enchant]['enchantments']['lvl'] . "</td>";
                                    } else {
                                        $table_str .= "<td>0</td>";
                                    }
                                }
                            } else {
                                $table_str .= "<td>0</td>";
                            }
                        }
                    } else {
                        $table_str .= "<td>0</td>";
                        $table_str .= "<td>0</td>";
                        $table_str .= "<td>0</td>";
                        $table_str .= "<td>0</td>";
                        $table_str .= "<td>0</td>";
                        $table_str .= "<td>0</td>";
                        $table_str .= "<td>0</td>";
                        $table_str .= "<td>0</td>";
                        $table_str .= "<td>0</td>";
                    }
                    //print set & items
                    if (!empty($char_eq)) {
                        $table_str .= "<td>" . basic_func_get_set_count($char_eq) . "</td>";
                        if (isset($char_eq['finger_1']) && !empty($char_eq['finger_1'])) {
                            $table_str .= "<td>" . $char_eq['finger_1']['name'] . "</td>";
                        } else {
                            $table_str .= "<td>Kein Item</td>";
                        }

                        if (isset($char_eq['finger_2']) && !empty($char_eq['finger_2'])) {
                            $table_str .= "<td>" . $char_eq['finger_2']['name'] . "</td>";
                        } else {
                            $table_str .= "<td>Kein Item</td>";
                        }

                        if (isset($char_eq['trinket_1']) && !empty($char_eq['trinket_1'])) {
                            $table_str .= "<td>" . $char_eq['trinket_1']['name'] . "</td>";
                        } else {
                            $table_str .= "<td>Kein Item</td>";
                        }

                        if (isset($char_eq['trinket_2']) && !empty($char_eq['trinket_2'])) {
                            $table_str .= "<td>" . $char_eq['trinket_2']['name'] . "</td>";
                        } else {
                            $table_str .= "<td>Kein Item</td>";
                        }

                        if (isset($char_eq['main_hand']) && !empty($char_eq['main_hand'])) {
                            $table_str .= "<td>" . $char_eq['main_hand']['name'] . "</td>";
                        } else {
                            $table_str .= "<td>Kein Item</td>";
                        }
                    } else {
                        $table_str .= "<td>0</td>";
                        $table_str .= "<td colspan='5'>Keine ItemDaten</td>";
                    }

                    //print raidexp
                    if (!empty($char_raid)) {
                        if (isset($char_raid['lfr']) && !empty($char_raid['lfr'])) {
                            $table_str .= "<td>" . count($char_raid['lfr']) . "|8</td>";
                        } else {
                            $table_str .= "<td>0|8</td>";
                        }

                        if (isset($char_raid['normal']) && !empty($char_raid['normal'])) {
                            $table_str .= "<td>" . count($char_raid['normal']) . "|8</td>";
                        } else {
                            $table_str .= "<td>0|8</td>";
                        }

                        if (isset($char_raid['hc']) && !empty($char_raid['hc'])) {
                            $table_str .= "<td>" . count($char_raid['hc']) . "|8</td>";
                        } else {
                            $table_str .= "<td>0|8</td>";
                        }

                        if (isset($char_raid['m']) && !empty($char_raid['m'])) {
                            $table_str .= "<td>" . count($char_raid['m']) . "|8</td>";
                        } else {
                            $table_str .= "<td>0|8</td>";
                        }

                        //lockouts
                        $current_lockouts = array(
                            basic_func_get_raid_lockouts("lfr", $last_id_reset_timestamp, $char_raid),
                            basic_func_get_raid_lockouts("normal", $last_id_reset_timestamp, $char_raid),
                            basic_func_get_raid_lockouts("hc", $last_id_reset_timestamp, $char_raid),
                            basic_func_get_raid_lockouts("m", $last_id_reset_timestamp, $char_raid)
                        );

                        $table_str .= "<td>" . $current_lockouts[0]  . "|8</td>";
                        $table_str .= "<td>" . $current_lockouts[1]  . "|8</td>";
                        $table_str .= "<td>" . $current_lockouts[2] . "|8</td>";
                        $table_str .= "<td>" . $current_lockouts[3] . "|8</td>";
                    } else {
                        $table_str .= "<td colspan='8'>Keine Raiddaten</td>";
                    }


                    $table_str .= "<td>" . str_replace(",", "", number_format($row['raiting'], 0)) . "</td>";

                    $table_str .= "<td>" . $row['m_chest'] . "</td>";
                    $table_str .= "<td>" . basic_func_get_raid_chest($current_lockouts) . "</td>";
                    $table_str .= "</tr>";
                }
                return $table_str;
            } else {
                //keine daten
            }
        } else {
            //db fehler
        }
    }

    //END

   

   

    //get chest data

    public function extract_m_chest($data)
    {
        $return_chest_str = "Keine Chest";
        if (isset($data['current_period']['best_runs'])) {
            $array_key_level_data = array();
            foreach ($data['current_period']['best_runs'] as $m_run) {
                array_push($array_key_level_data, intval($m_run['keystone_level']));
            }
            if (count($array_key_level_data) > $this->chest_count_per_key_count[0]) {
                rsort($array_key_level_data);
                $return_chest_str = $this->chest_level_per_key_level[$array_key_level_data[$this->chest_count_per_key_count[0]]];
                if (count($array_key_level_data) > $this->chest_count_per_key_count[1]) {
                    $return_chest_str .= " | " . $this->chest_level_per_key_level[$array_key_level_data[$this->chest_count_per_key_count[1]]];
                    if (count($array_key_level_data) > $this->chest_count_per_key_count[2]) {
                        $return_chest_str .= " | " . $this->chest_level_per_key_level[$array_key_level_data[$this->chest_count_per_key_count[2]]];
                    }
                }
            }
        }
        return $return_chest_str;
    }


    

}
