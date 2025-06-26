<html>
    <head>
        <script src="./classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>
        <script src="./classi/printarea/jquery-ui-1.10.4.custom.js"></script>
        <script src="./classi/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>

        <script src="./bootstrap/js/bootstrap.min.js" type="text/JavaScript" language="javascript"></script>

        <link href="./bootstrap/css/darkly.min.css" rel="stylesheet">
        <link href="./bootstrap/css/docs.min.css" rel="stylesheet">
        <link href="./bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">
       
        <script src="https://code.jquery.com/jquery-2.1.1.js" type="text/JavaScript" language="javascript"></script>


    </head>
    <body>
        <div class="container">
            <div class="row col-md-12">

                <?php
                include_once("./classi/config.php");
                include_once("./classi/auth.lib.php");
                include_once("./classi/license.lib.php");
                include_once("./classi/reg.lib.php");
                include_once("./classi/utils.lib.php");



                list($status, $user) = auth_get_status();

                if ($status == AUTH_NOT_LOGGED) {
                    $uname = trim($_POST['uname']);
                    $passw = trim($_POST['passw']);

                    if ($uname == "" or $passw == "") {
                        $status = AUTH_INVALID_PARAMS;
                    } else {
                        list($status, $user) = auth_login($uname, $passw);
                        if (!is_null($user)) {
                            list($status, $uid) = auth_register_session($user);
                        }
                    }
                }
                $query = "SELECT U.nome_negozio, U.username as username, U.password as password
	FROM " . $_CONFIG['table_sessioni'] . " S," . $_CONFIG['table_utenti'] . " U
	WHERE S.user_id = U.id and S.uid = '" . $uid . "'";

                /*
                error_reporting(E_ALL);

                var_dump(license_has($user, "sede_centrale"));
                var_dump(license_has($user, "affiliato"));
                 * 
                 */
                
                
                if (license_has($user, "sede_centrale")) {
                    switch ($status) {
                        case AUTH_LOGGED:
                            header("Refresh: 5;URL=./pagine/gestione_magazzino/elenco_movimenti.php?switch=Scarichi");
                            echo '<div align="center">Sei gia connesso ... attendi il reindirizzamento</div>';
                            break;
                        case AUTH_INVALID_PARAMS:
                            header("Refresh: 5;URL=./index.php");
                            echo '<div align="center"><h1>Hai inserito dati non corretti ... attendi il reindirizzamento</h1></div>';
                            break;
                        case AUTH_LOGEDD_IN:
                            switch (auth_get_option("TRANSICTION METHOD")) {
                                case AUTH_USE_LINK:
                                    header("Refresh: 0;URL=./pagine/gestione_magazzino/elenco_movimenti.php?switch=Scarichi&uid=" . $uid);
                                    break;
                                case AUTH_USE_COOKIE:
                                    header("Refresh: 0;URL=./pagine/gestione_magazzino/elenco_movimenti.php?switch=Scarichi");
                                    setcookie('uid', $uid, time() + 3600 * 12, '/'); //time()+3600
                                    break;
                                case AUTH_USE_SESSION:
                                    header("Refresh: 0;URL=./pagine/gestione_magazzino/elenco_movimenti.php?switch=Scarichi");
                                    $_SESSION['uid'] = $uid;
                                    break;
                            }

                            echo '<div align="center"><h1>Ciao ' . $user['nome_negozio'] . ' ... attendi il reindirizzamento</h1></div>';
                            break;
                        case AUTH_FAILED:
                            header("Refresh: 5;URL=./index.php");
                            echo '<div align="center"><h1>Fallimento durante il tentativo di connessione ... attendi il reindirizzamento</h1></div>';
                            break;
                    }
                }
                elseif(license_has($user, "affiliato")){
                    switch ($status) {
                        case AUTH_LOGGED:
                            header("Refresh: 5;URL=./pagine/contabilita/cassa.php");
                            echo '<div align="center"><h1>Sei gia connesso ... attendi il reindirizzamento</h1></div>';
                            break;
                        case AUTH_INVALID_PARAMS:
                            header("Refresh: 5;URL=./index.php");
                            echo '<div align="center"><h1>Hai inserito dati non corretti ... attendi il reindirizzamento</h1></div>';
                            break;
                        case AUTH_LOGEDD_IN:
                            switch (auth_get_option("TRANSICTION METHOD")) {
                                case AUTH_USE_LINK:
                                    header("Refresh: 0;URL=./pagine/contabilita/cassa.php&uid=" . $uid);
                                    break;
                                case AUTH_USE_COOKIE:
                                    header("Refresh: 0;URL=./pagine/contabilita/cassa.php");
                                    setcookie('uid', $uid, time() + 3600 * 12, '/'); //time()+3600
                                    break;
                                case AUTH_USE_SESSION:
                                    header("Refresh: 0;URL=./pagine/contabilita/cassa.php");
                                    $_SESSION['uid'] = $uid;
                                    break;
                            }

                            echo '<div align="center"><h1>Ciao ' . $user['nome_negozio'] . ' ... attendi il reindirizzamento</h1></div>';
                            break;
                        case AUTH_FAILED:
                            header("Refresh: 5;URL=./index.php");
                            echo '<div align="center"><h1>Fallimento durante il tentativo di connessione ... attendi il reindirizzamento</h1></div>';
                            break;
                    }
                    }
                    else 
                    {
                        switch ($status) {
                        case AUTH_LOGGED:
                            header("Refresh: 5;URL=./pagine/contabilita/cassa.php");
                            echo '<div align="center">Sei gia connesso ... attendi il reindirizzamento</div>';
                            break;
                        case AUTH_INVALID_PARAMS:
                            header("Refresh: 5;URL=./index.php");
                            echo '<div align="center"><h1>Hai inserito dati non corretti ... attendi il reindirizzamento</h1></div>';
                            break;
                        case AUTH_LOGEDD_IN:
                            switch (auth_get_option("TRANSICTION METHOD")) {
                                case AUTH_USE_LINK:
                                    header("Refresh: 0;URL=./pagine/contabilita/cassa.php&uid=" . $uid);
                                    break;
                                case AUTH_USE_COOKIE:
                                    header("Refresh: 0;URL=./pagine/contabilita/cassa.php");
                                    setcookie('uid', $uid, time() + 3600 * 12, '/'); //time()+3600
                                    break;
                                case AUTH_USE_SESSION:
                                    header("Refresh: 0;URL=./pagine/contabilita/cassa.php");
                                    $_SESSION['uid'] = $uid;
                                    break;
                            }

                            echo '<div align="center"><h1>Ciao ' . $user['nome_negozio'] . ' ... attendi il reindirizzamento</h1></div>';
                            break;
                        case AUTH_FAILED:
                            header("Refresh: 5;URL=./index.php");
                            echo '<div align="center"><h1>Fallimento durante il tentativo di connessione ... attendi il reindirizzamento</h1></div>';
                            break;
                    }
                    }
                
                    
                ?>
            </div>
        </div>
    </body>
</html>
