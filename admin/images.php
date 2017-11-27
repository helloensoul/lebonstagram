<?php
    function cust_sort($a,$b) {    }


    function images() { 
        global $wpdb;?>
        <h1>LebonStagram - #<?php echo get_option('lebonstagram-hashtag'); ?></h1>
            <?php
            /*foreach($meta as $dato) {
                $id_image = $dato['id'];
                $created_time = date("Y-m-d H:i",$dato['created_time']);
                $standard_url = $dato['images']['standard_resolution']['url'];
                $user = $dato['caption']['from']['username'];
                $thumbnail_url = $dato['images']['thumbnail']['url'];
                $link = $dato['link'];
                $lowres_url = $dato['images']['low_resolution']['url'];
                $wpdb->insert("lebonstagram_images", array('image_id'=>$id_image,'created_time'=>$created_time,'standard_url'=>$standard_url,'user'=>$user,'thumbnail_url'=>$thumbnail_url,'instagram_link'=>$link,'lowres_url'=>$lowres_url));
                $banned = $dato['banned'];} */
                $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
                $limit = 50;
                $offset = ( $pagenum - 1 ) * $limit;
                $sql = "SELECT * FROM lebonstagram_images ORDER BY created_time DESC LIMIT $offset, $limit";
                $meta = $wpdb->get_results($sql,ARRAY_A);
                foreach($meta as $dato) {
                    $banned = $dato['banned'];
                ?>
                    <div class="lebonstagram_admin_pic">
                        <a href="<?php echo $dato['standard_url'];?>"><img class="<?php if($banned == 1) {?>banned<?php } else {?>display<?php }?>" src="<?php echo $dato['thumbnail_url'];?>"></a><br />
                        <p><a href="http://instagram.com/<?php echo $dato['user'];?>"><?php echo $dato['user'];?></a></p>
                        <?php if($banned == 1) {?>
                            <input type="button" data-id="<?php echo $dato['image_id'];?>" data-ban="banned" class="ban" value="Aggiungi">
                        <?php } else {?>
                            <input type="button" data-id="<?php echo $dato['image_id'];?>" data-ban="display" class="ban" value="Rimuovi">
                        <?php }?>      
                    </div>
            <?php
            } $total = $wpdb->get_var( "SELECT COUNT(`id`) FROM lebonstagram_images" );
$num_of_pages = ceil( $total / $limit );
$page_links = paginate_links( array(
    'base' => add_query_arg( 'pagenum', '%#%' ),
    'format' => '',
    'prev_text' => __( '&laquo;', 'aag' ),
    'next_text' => __( '&raquo;', 'aag' ),
    'total' => $num_of_pages,
    'current' => $pagenum
) );
 
if ( $page_links ) {
    echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
} ?>
        <style>
            .lebonstagram_admin_pic {
                background-color: white;
                float: left;
                margin: 10px;
                padding: 10px;
                text-align: center;
            }

            .ban {
                background-color: #e50e07;
                border: 0 none;
                color: white;
                font-size: 12px;
                text-transform: uppercase;
                width: 100%;
            }

            .lebonstagram_admin_pic img {
                border: 1px solid #bebebe;
            }
            
            .banned {
                opacity: 0.5;
            }
        </style>

        <script>
            jQuery("input.ban").click(function() {
                var target = jQuery(this);
                var databan = target.attr("data-ban");
                var action_id = target.attr("data-id");
                
                if(databan == "banned") {
                    jQuery.ajax({
                        type: "POST",
                        url: "<?php echo plugin_dir_url( __FILE__ );?>/ban.php",
                        data: {action_id:action_id,operazione:"display"},
                    }).done(function() {
                        target.prop("value","Rimuovi");
                        target.attr("data-ban","display");
                        target.parent().find('img').fadeTo("fast",1);
                    });
                } 

                if (databan == "display") {
                    jQuery.ajax({
                        type: "POST",
                        url: "<?php echo plugin_dir_url( __FILE__ );?>/ban.php",
                        data: {action_id:action_id,operazione:"ban"},
                    }).done(function() {
                        target.prop("value","Aggiungi");
                        target.attr("data-ban","banned");
                        target.parent().find('img').fadeTo("fast",0.5);
                    });
                }
            });

            /*jQuery('input[data-ban="banned"]').click(function(){
                var target = jQuery(this);
                var action_id = jQuery(this).attr("data-id");
                jQuery.ajax({
                    type: "POST",
                    url: "<?php echo plugin_dir_url( __FILE__ );?>/ban.php",
                    data: {action_id:action_id,operazione:"display"},
                }).done(function() {
                    jQuery(target).prop("value","Rimuovi");
                    jQuery(target).attr("data-ban","display");
                    jQuery(target).parent().find('img').fadeTo("fast",1);
                })
            });

            jQuery('input[data-ban="display"]').click(function(){
                var target = jQuery(this);
                var action_id = jQuery(this).attr("data-id");
                jQuery.ajax({
                    type: "POST",
                    url: "<?php echo plugin_dir_url( __FILE__ );?>/ban.php",
                    data: {action_id:action_id,operazione:"ban"},
                }).done(function() {
                    jQuery(target).prop("value","Aggiungi");
                    jQuery(target).attr("data-ban","banned");
                    jQuery(target).parent().find('img').fadeTo("fast",0.5);
                });
            });*/
        </script>
<?php
    }
    ?>