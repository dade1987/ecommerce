<?php

include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");


//var_dump($_POST["array"]);
//echo count($_POST["array"][0]);
//echo ("BOOL: " . ($status == AUTH_LOGGED && license_has($user, "sede_centrale")));
list($status, $user) = auth_get_status();

if ($status == AUTH_LOGGED && license_has($user, "sede_centrale")) {

    $array["gruppi"] = $_POST["array"][0];
    $array["colori"] = $_POST["array"][1];
    $array["quantita"] = $_POST["array"][2];
    $array["pannello"] = $_POST["array"][3];

    //var_dump($array);

    $iteratore = count($array["gruppi"]);
//echo $iteratore;

    $query = "DELETE FROM pannelli ; ";
    $query .= "\n";

    echo $query;

    $db_magazzino->query($query);

    for ($i = 0; $i < $iteratore; $i++) {
        $query = " INSERT INTO pannelli ";
        $query .= " ( gruppo , colore , quantita, nome ) ";
        $query .= " VALUES ( ";
        $query .= "\"" . $array["gruppi"][$i] . "\"";
        $query .= " , ";
        $query .= "\"" . $array["colori"][$i] . "\"";
        $query .= " , ";
        $query .= "\"" . $array["quantita"][$i] . "\"";
        $query .= " , ";
        $query .= "\"" . $array["pannello"][$i] . "\"";
        $query .= " ) ; ";
        $query .= "\n";

        echo $query;

        $db_magazzino->query($query);
    }
}
?>