<?php
/**
* Plugin Name: LebonStagram
* Description: Visualizza un'immagine casuale da un feed di Instagram. Permette di bannare immagini e/o utenti dal feed.
* Version: 1.0.0
* Author: Giulio Pecorella
* Author URI: http://www.ensoul.it
*/
    include("admin/images.php");
    include("admin/options.php");
    
    function attivazione_lebonstagram() {
        global $wpdb;
        $sql = "CREATE TABLE IF NOT EXISTS `lebonstagram_images` (
        `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `image_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `created_time` datetime NOT NULL,
        `standard_url` varchar(2083) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `user` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `thumbnail_url` varchar(2083) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `instagram_link` varchar(2083) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `lowres_url` varchar(2083) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `banned` int(1) NOT NULL,
        PRIMARY KEY (`ID`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=161 ;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    add_filter('cron_schedules', 'add_scheduled_interval');
 
    // add once 5 minute interval to wp schedules
    function add_scheduled_interval($schedules) {
        $schedules['minutes_5'] = array('interval'=>60, 'display'=>'Once 10 minutes');
        return $schedules;
    }

    function disattivazione_lebonstagram() {
       wp_clear_scheduled_hook('my_hourly_event');
    }

    function menu_pages() {
        add_menu_page('LebonStagram', 'LebonStagram', 'manage_options','pics',"images",plugin_dir_url( __FILE__ )."/instagram.png","");
        add_submenu_page("pics", "Opzioni", "Opzioni", "manage_options", "lebonstagram-options","lebonstagram_options");
        wp_enqueue_script("jquery");
    }

    function fetch_images() {
        global $wpdb;
        $last_sql = "SELECT created_time FROM `lebonstagram_images` ORDER BY `lebonstagram_images`.`created_time` DESC LIMIT 0,1";
        $last_db = $wpdb->get_var($last_sql);
        $timestamp = (int) strtotime($last_db);
        $ban_sql = "SELECT user FROM `lebonstagram_images` WHERE banned = 1";
        $bannati = $wpdb->get_results($ban_sql,ARRAY_N);
        $banned = $bannati[0];
        $url = "https://api.instagram.com/v1/tags/".get_option('lebonstagram-hashtag')."/media/recent?access_token=275965118.99f7f81.95c6ae78c76f400186faac8e212c3fec";        
        do {
            $array_url = objectToArray(json_decode(fetchData($url)));
            $fetched_pics = $array_url['data'];           
            $immagini_feed = sizeof($fetched_pics);
            $new = 0;
            foreach($fetched_pics as $dato) {
                $data_immagine = (int) $dato['created_time'];
                if($data_immagine > $timestamp) {
                    $id_image = $dato['id'];
                    $created_time = date("Y-m-d H:i",$dato['created_time']);
                    $standard_url = $dato['images']['standard_resolution']['url'];
                    $user = $dato['caption']['from']['username'];
                    $thumbnail_url = $dato['images']['thumbnail']['url'];
                    $link = $dato['link'];
                    $not_dub = "SELECT instagram_link FROM  `lebonstagram_images` WHERE instagram_link = '".$link."'";
                    $not_dub_link = $wpdb->get_var($not_dub);
                    $lowres_url = $dato['images']['low_resolution']['url'];
                    if(in_array($user,$banned)) {
                   
                    } 
                    else {
                            if($link != $not_dub_link) {
                                $wpdb->insert("lebonstagram_images", array('image_id'=>$id_image,'created_time'=>$created_time,'standard_url'=>$standard_url,'user'=>$user,'thumbnail_url'=>$thumbnail_url,'instagram_link'=>$link,'lowres_url'=>$lowres_url));
                                $new++;
                            }
                    }
                }
            }

            if($new == $immagini_feed) {
                $url = $fetched_pics['pagination']['next_url'];
            } else {
                $url = false;
            }
        } while($url);  
    }

    function display_photo() {
        global $wpdb;
        $sql = "SELECT * FROM `lebonstagram_images` ORDER BY RAND() LIMIT 0,1";
        $image = $wpdb->get_row($sql, ARRAY_A);
        $content = '<div class="lebonstagram_wrapper">';
        $content .= '<div class="lebonstagram_image_wrapper">';
        $url_img = $image['standard_url'];
        $content .= '<img class="lebonstagram_image lebonstagram" style="width: 100%; height: auto; margin-top: 92px;" src="'.$url_img.'">';
        $content .= '</div>';
        $content .= '</div>';
        return $content;
    }

    add_action('admin_menu', 'menu_pages');
    add_action( 'my_hourly_event',  'fetch_images' );
    add_shortcode("lebonstagram_pic", "display_photo");
    register_activation_hook(__FILE__, 'attivazione_lebonstagram');
    register_deactivation_hook(__FILE__, 'disattivazione_lebonstagram');
?>