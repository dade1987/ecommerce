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

    $array["pannello"] = $_POST["array"][0];
    $array["negozio"] = $_POST["array"][1];
    $array["opzione"] = $_POST["array"][2];

    //var_dump($array);

    $iteratore = count($array["pannello"]);
//echo $iteratore;

    $query = "DELETE FROM magazzino_base ; ";
    $query .= "\n";

    echo $query;

    $db_magazzino->query($query);

    for ($i = 0; $i < $iteratore; $i++) {
        $query = " INSERT INTO magazzino_base ";
        $query .= " ( pannello , negozio , bool ) ";
        $query .= " VALUES ( ";
        $query .= "\"" . $array["pannello"][$i] . "\"";
        $query .= " , ";
        $query .= "\"" . $array["negozio"][$i] . "\"";
        $query .= " , ";
        $query .= "\"" . $array["opzione"][$i] . "\"";
        $query .= " ) ; ";
        $query .= "\n";

        echo $query;

        $db_magazzino->query($query);
    }
}
?>