<?php
    add_action( 'admin_init', 'lebonstagram_settings');

    function lebonstagram_settings() { // whitelist options
        register_setting( 'lebonstagram-options', 'lebonstagram-hashtag' );
    }     

    function fetchData($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $result = curl_exec($ch);
        curl_close($ch); 
        return $result;
    }

    function objectToArray($d) {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }

        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map(__FUNCTION__, $d);
        }
        else {
            // Return array
            return $d;
        }
    }

    function lebonstagram_options() { 
        global $wpdb;?>
        <div class="wrap">
            <h1>LebonStagram - Opzioni</h1>
            <h2>Il prossimo aggiornamento avverrà alle 
                <?php $orario_array = localtime(wp_next_scheduled('my_hourly_event'),true); 
                echo $orario_array['tm_hour']+2;
                echo ":";
                echo $orario_array['tm_min'];
                echo " del ";
                echo $orario_array['tm_mday'];
                echo "/";
                echo $orario_array['tm_mon'];
                echo "/";
                echo $orario_array['tm_year']+1900;?>
            <?php if( isset($_GET['settings-updated']) ) { 
                $wpdb->query("TRUNCATE TABLE `lebonstagram_images`");
                $url = "https://api.instagram.com/v1/tags/".get_option('lebonstagram-hashtag')."/media/recent?access_token=275965118.99f7f81.95c6ae78c76f400186faac8e212c3fec";
                wp_clear_scheduled_hook('my_hourly_event');
                wp_schedule_event( time(), 'hourly', 'my_hourly_event' );
                $images = array();
                $giro = 0;
                do {      
                    $giro++;         
                    $fetched_pics = objectToArray(json_decode(fetchData($url)));
                    $images = array_merge($images,$fetched_pics['data']);
                    $url = $fetched_pics['pagination']['next_url'];
                } while($url);

                foreach($images as $dato) {
                    $id_image = $dato['id'];
                    $created_time = date("Y-m-d H:i",$dato['created_time']);
                    $standard_url = $dato['images']['standard_resolution']['url'];
                    $user = $dato['caption']['from']['username'];
                    $thumbnail_url = $dato['images']['thumbnail']['url'];
                    $link = $dato['link'];
                    $lowres_url = $dato['images']['low_resolution']['url'];
                    $wpdb->insert("lebonstagram_images", array('image_id'=>$id_image,'created_time'=>$created_time,'standard_url'=>$standard_url,'user'=>$user,'thumbnail_url'=>$thumbnail_url,'instagram_link'=>$link,'lowres_url'=>$lowres_url));
                }
                ?>
                <div id="message" class="updated">
                    <p><?php echo "Hashtag in uso: "; ?><strong><?php echo get_option('lebonstagram-hashtag');?></strong></p>
                </div>
            <?php } ?>
            <form method="post" action="options.php"> 
                <?php
                    settings_fields( 'lebonstagram-options' );
                    do_settings_sections( 'lebonstagram-options' );?>
                    <label for="lebonstagram-hashtag">Tag: </label><input type="text" name="lebonstagram-hashtag" value="<?php echo get_option('lebonstagram-hashtag'); ?>" />
                <?php
                    submit_button();
                ?>
            </form>
        </div>
<?php
    }
    ?>