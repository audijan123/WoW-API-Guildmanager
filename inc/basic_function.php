<?php
/* This function prepare Daten to Array for Output */
function basic_func_convert_eq_data($eq_data,$avaible_eq)
    {
        $array_eq_data = array();
        foreach ($avaible_eq as $eq_piece) {
            if ($eq_data[$eq_piece] != "" && $eq_data[$eq_piece] != " " && $eq_data[$eq_piece] != "[]") {
                $array_eq_data[$eq_piece] = json_decode($eq_data[$eq_piece], true);
            } else {
                $array_eq_data[$eq_piece] = array();
            }
        }
        return $array_eq_data;
    }

function basic_func_get_set_count($eq_data)
    {
        $set_counter = 0;
        foreach ($eq_data as $eq_piece) {
            if (isset($eq_piece['is_set']) && $eq_piece['is_set']) {
                $set_counter++;
            }
        }
        return $set_counter;
    }

    function basic_func_format_last_login($timestamp)
    {
        //$table_str .= "<td>" . $char_info['last_login'] . "</td>";
        if ($timestamp != 0) {
            $time_last_login = $timestamp / 1000;
            $time_current = new DateTime();
            $time_last = new DateTime();
            $time_last->setTimestamp($time_last_login);
            $difference = $time_last->diff($time_current);
            return $difference->format('%a Tag/e %H Stunde/n');
        } else {
            return "Keine Daten";
        }
    }
       

    function basic_func_get_last_reset_time()
    {
        $last_reset_id = new DateTime();
        while ($last_reset_id->format('N') != 3) {
            $last_reset_id->sub(new DateInterval('P1D'));
        }
        $last_reset_id->setTime(5, 0);
        return $last_reset_id->getTimestamp();
    }


    function basic_func_get_raid_chest($current_lockouts)
    {
        $array_raid_data_chest = array();
        if ($current_lockouts[3] >= 2) {
            array_push($array_raid_data_chest, "M");
            if ($current_lockouts[3] >= 4) {
                array_push($array_raid_data_chest, "M");
                if ($current_lockouts[3] >= 6) {
                    array_push($array_raid_data_chest, "M");
                }
            }
        }
        if (count($array_raid_data_chest) < 3 && $current_lockouts[2] >= 2) {
            array_push($array_raid_data_chest, "H");
            if (count($array_raid_data_chest) < 3 && $current_lockouts[2] >= 4) {
                array_push($array_raid_data_chest, "H");
                if (count($array_raid_data_chest) < 3 && $current_lockouts[2] >= 6) {
                    array_push($array_raid_data_chest, "H");
                }
            }
        }

        if (count($array_raid_data_chest) < 3 && $current_lockouts[1] >= 2) {
            array_push($array_raid_data_chest, "N");
            if (count($array_raid_data_chest) < 3 && $current_lockouts[1] >= 4) {
                array_push($array_raid_data_chest, "N");
                if (count($array_raid_data_chest) < 3 && $current_lockouts[1] >= 6) {
                    array_push($array_raid_data_chest, "N");
                }
            }
        }

        if (count($array_raid_data_chest) < 3 && $current_lockouts[0] >= 2) {
            array_push($array_raid_data_chest, "L");
            if (count($array_raid_data_chest) < 3 && $current_lockouts[0] >= 4) {
                array_push($array_raid_data_chest, "L");
                if (count($array_raid_data_chest) < 3 && $current_lockouts[0] >= 6) {
                    array_push($array_raid_data_chest, "L");
                }
            }
        }
        if (count($array_raid_data_chest) == 0) //
        {
            return "Keine Chest";
        } else {
            return implode(" | ", $array_raid_data_chest);
        }
    }

    function basic_func_get_raid_lockouts($diff, $last_id_reset, $raid_data)
    {
        if (isset($raid_data[$diff]) && !empty($raid_data[$diff])) {
            $lockouts = 0;
            foreach ($raid_data[$diff] as $bosses) {

                $last_kill = $bosses['last_kill'] / 1000;
                if ($last_kill > $last_id_reset) {
                    $lockouts++;
                }
            }
            return $lockouts;
        } else {
            return 0;
        }
    }

    function basic_func_convert_name_to_url($name)
    {
        return urlencode(strtolower($name));
    }




    ?>