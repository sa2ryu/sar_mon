<?php
/*
   Plugin Name: sar_mon
   Plugin URI:
   Description: sarの画面出力
   Version: 0.0.1
   Author: satoru
   Author URI: https://github.com/sa2ryu
 */

add_action('admin_menu',function(){
add_submenu_page('tools.php' ,'サーバ監視','サーバ監視','level_10','sar_mon/display.php');
});


?>
