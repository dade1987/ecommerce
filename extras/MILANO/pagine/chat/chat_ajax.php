<?php



include_once("../../classi/utils.lib.php");

include_once("../../classi/license.lib.php");

include_once("../../classi/funzioni.php");

include_once("../../classi/config.php");

include_once("../../classi/auth.lib.php");

list($status, $user) = auth_get_status();



if (isset($_POST['io']) && isset($_POST['lui'])) {

    $chat = new chat($_POST['io'], $_POST['lui'],  license_has($user, "sede_centrale"));

}





if (!empty($_POST['messaggio'])&&isset($_POST['invia'])&&$_POST['invia']=="si") {

    $chat->invio($_POST['messaggio']);

} else {

    $chat->ricezione();

}



class chat {



    function __construct($io, $lui,$sede_centrale) {



        $this->io = $io;

        $this->lui = $lui;

        $this->sede_centrale=$sede_centrale;

        

        #var_dump($this->sede_centrale);

        if ($this->sede_centrale==true) {

            $this->filename = $this->lui.".txt";

        } else {

            $this->filename = $this->io.".txt";

        }    

        #echo $this->filename;

    $this->file=fopen($this->filename, "a+");

    }



    public function ricezione() {

        #echo $this->filename; 

        #var_dump($this->file);

        $read=fread($this->file,filesize($this->filename));

        $read=substr($read,-500);

        echo $read;
        fclose($this->file);

    }



    public function invio($messaggio) {

        #echo $this->filename; 

        $messaggio=$this->io.": $messaggio \r";

        fwrite($this->file, $messaggio);

        $this->ricezione();
        fclose($this->file);
    }



}



?>