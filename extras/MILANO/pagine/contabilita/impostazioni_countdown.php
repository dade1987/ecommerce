<?php session_start(); ?>
<html>
    <head>
        <script src="../../classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>
        <script src="../../classi/printarea/jquery-ui-1.10.4.custom.js"></script>
        <script src="../../classi/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>

        <script src="../../bootstrap/js/bootstrap.min.js" type="text/JavaScript" language="javascript"></script>

        <link href="../../bootstrap/css/flatly.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/docs.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">

        <script src="https://code.jquery.com/jquery-2.1.1.js" type="text/JavaScript" language="javascript"></script>

    </head>
    <body>

        <?php
        include_once("../../classi/funzioni.php");
        include_once("../../classi/config.php");
        include_once("../../classi/auth.lib.php");
        list($status, $user) = auth_get_status();

        if ($status == AUTH_LOGGED && license_has($user, "sede_centrale")) {
            menu();

            echo "<h1>THE FINAL COUNTDOWN</h1>";


            if ($_POST['submit'] === "submit") {

                $c = $_POST['quantita_post'];

                if ($c > 0) {

                    for ($i = 0; $i < $c; $i++) {

                        //if (!empty($_POST["2_euro_" . $i]) || !empty($_POST["5_euro_" . $i]) || !empty($_POST["50_euro_" . $i]) || !empty($_POST["qt_" . $i])) {
                        $query = "UPDATE utenti3 SET 20_perc='" . $_POST["20_perc_" . $i] . "',2_euro='" . $_POST["2_euro_" . $i] . "',5_euro='" . $_POST["5_euro_" . $i] . "',50_perc='" . $_POST["50_perc_" . $i] . "',qt='" . $_POST["qt_" . $i] . "' WHERE username='" . $_POST["negozio_" . $i] . "';";

                        //echo $query;

                        $risultato = $db_magazzino->query($query);
                        //}
                    }
                }
            }
            ?>

            <form name="form1" method="post" >

                <table class="table">
                    <?php
                    $risultato = $db_magazzino->query("SELECT * FROM utenti3;");

                    $i = 0;
                    while ($row = $risultato->fetch_assoc()) {
                        ?>
                        <tr><td><input type="hidden" name="negozio_<?php echo $i; ?>" value="<?php echo $row["username"]; ?>"><?php echo $row["username"]; ?></td>                            

                            <td><input type="checkbox" <?php if ($row["2_euro"] === "on") echo " checked "; ?> name="2_euro_<?php echo $i; ?>"><label>2 Euro</label></td>

                            <td><input type="checkbox" <?php if ($row["5_euro"] === "on") echo " checked "; ?> name="5_euro_<?php echo $i; ?>"><label>5 Euro</label></td>

                            <td><input type="checkbox" <?php if ($row["20_perc"] === "on") echo " checked "; ?> name="20_perc_<?php echo $i; ?>"><label>20%</label></td>

                            <td><input type="checkbox" <?php if ($row["50_perc"] === "on") echo " checked "; ?> name="50_perc_<?php echo $i; ?>"><label>50%</label></td>
                            <td><input class="form-control" name="qt_<?php echo $i; ?>"  type="text" placeholder="Quantita massima" value="<?php echo $row["qt"]; ?>"></td></tr>

                        <?php
                        $i++;
                    }
                    ?>
                    <input type="hidden" name="quantita_post" value="<?php echo $i; ?>">

                </table>

                <button name="submit" value="submit" class="form-control" style="width: 200px;">Conferma modifiche</button>

            </form>

    </html>
    <?php
} else {
    non_autorizzato();
}
?>
