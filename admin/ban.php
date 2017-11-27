<?php
    require('../../../../wp-blog-header.php');

    function aggiungi_blacklist($id_immagine) {
        global $wpdb;
        $wpdb->update("lebonstagram_images",array('banned'=>1),array("image_id"=>stripslashes($id_immagine)));
    }

    function rimuovi_blacklist($id_immagine) {
        global $wpdb;
        $wpdb->update("lebonstagram_images",array('banned'=>0),array("image_id"=>stripslashes($id_immagine)));
    }

    $action = $_POST['operazione'];
    $id_immagine = $_POST['action_id'];

    switch($action) {
        case "ban":
            aggiungi_blacklist($id_immagine);
            echo $id_immagine.": rimossa.";
            break;

        case "display":
            rimuovi_blacklist($id_immagine);
            echo $id_immagine.": riaggiunta.";
            break;
    }
?>