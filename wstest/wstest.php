<?php
/*
 * Plugin Name: WSTEST
 * Description: Formidable Forms Test Plugin 
 * Version: 1.0.0
 * Author: Himanshu Jikadara
 * License: GPLv2 or later
 * Text Domain: wstest
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2020 Web Sanskruti.
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function wstest_admin_menu() {
add_menu_page(
__( 'WS Test page', 'wstest' ),
__( 'WS Test', 'wstest' ),
'manage_options',
'wstest-admin-page',
'wstest_admin_page_contents',
'dashicons-welcome-view-site',
3);
}

add_action( 'admin_menu', 'wstest_admin_menu' );


function wstest_admin_page_contents() {
?>
<div class="container" style="background-color: #fff;">
<div class="row">
<div id="frm_top_bar">
    <a href="#" class="frm-header-logo">
        <img src="<?php echo plugin_dir_url(__FILE__) . "images/logo.svg"; ?>" height="50px" alt="<?php esc_html_e( 'Formidable Forms', 'wstest' ); ?>">
    </a>
    <a href="#" id="refresh_data" class="button button-secondary frm-button-secondary frm_animate_bg"><?php esc_html_e( 'Refresh Data', 'wstest' ); ?></a>
</div>
</div>
<div class="row">
<div id="message" class="alert alert-success" style="display: none;"></div>
</div>
<div class="row">
<div id="wstest_list"></div>
</div>
</div>
<?php
}

// define the actions for the two hooks created, first for logged in users and the next for logged out users
add_action("wp_ajax_wstest_get_strategy11_data", "wstest_get_strategy11_data");
add_action("wp_ajax_nopriv_wstest_get_strategy11_data", "wstest_get_strategy11_data");

function wstest_get_strategy11_data(){
    $json_key = 'strategy11_data_storage';
    $json_expiration = 60 * 60; // 60 minutes
    if ( $data = get_transient($json_key) ){
        //In Cache so do nothing  
    } else {
        $args = array ('headers' => array('Content-Type' => 'application/json'));
        $rawData = wp_remote_get('http://api.strategy11.com/wp-json/challenge/v1/1', $args);
        //$data = json_decode( wp_remote_retrieve_body( $rawData ), true );
        $data = wp_remote_retrieve_body( $rawData );
        //It's new so set in transient for next time
	set_transient($json_key, $data, $json_expiration);
    }
    echo $data;
    // don't forget to end your scripts with a die() function - very important
    die();
}


add_action("wp_ajax_wstest_admin_page_delete_transient", "wstest_admin_page_delete_transient");

// Create function to delete transient
function wstest_admin_page_delete_transient() {
    delete_transient( 'strategy11_data_storage' );
    echo true;
    die();
}

//for shortcode
function wstest_shortcut($atts) {
$message = '<div id="wstest_list"></div>'; 
return $message;
}

add_shortcode('wstest', 'wstest_shortcut');


function wstest_im_scripts() {
    wp_register_style('bootstrapmin', plugin_dir_url(__FILE__) . 'css/bootstrapmin.css');
    wp_enqueue_style('bootstrapmin');
    wp_enqueue_script('wstestjs', plugins_url('js/bootstrap.min.js', __FILE__), array('jquery'), true);
    wp_localize_script( 'wstestjs', 'wsajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )); 
}

add_action('wp_enqueue_scripts', 'wstest_im_scripts');

// js for front-end 
function wstest_script() {
    ?>
    <script>
        jQuery(document).ready( function() {
            jQuery.ajax({
            type : "post",
            dataType : "json",
            url : wsajax_script.ajaxurl,
            data : {action: "wstest_get_strategy11_data"},
            success: function(response) {
                var output = "";
                output += "<h1>"+response.title+"</h1>";
                if(response.data){
                    output += "<table class='table'>";
                    if(response.data.headers.length>0){
                        output += "<thead>";
                        output += "<tr>";
                        for (var i=0;i<response.data.headers.length;i++) {
                            output += "<th>"+response.data.headers[i]+"</th>";
                        }
                        output += "</tr>";
                        output += "</thead>";
                    }
                    if(response.data.rows){
                        output += "<tbody>";
                        for (var record in response.data.rows) {
                            output += "<tr>";
                            output += "<td>"+response.data.rows[record].id+"</td>";
                            output += "<td>"+response.data.rows[record].fname+"</td>";
                            output += "<td>"+response.data.rows[record].lname+"</td>";
                            output += "<td>"+response.data.rows[record].email+"</td>";
                            output += "<td>"+new Date(response.data.rows[record].date)+"</td>";
                            output += "</tr>";
                        }
                        output += "</tbody>";
                    }
                    output += "</table>";    
                }
                jQuery("#wstest_list").html(output);
            }
         });
        });
    </script>
    <?php
}

add_action('wp_footer', 'wstest_script');



function load_admin_style() {
  wp_register_style('bootstrapmin', plugin_dir_url(__FILE__) . 'css/bootstrapmin.css');
  wp_enqueue_style('bootstrapmin');
  wp_enqueue_script('wstestjs', plugins_url('js/bootstrap.min.js', __FILE__), array('jquery'), true);  
  wp_register_style( 'admin_css', plugin_dir_url(__FILE__) . 'css/frm_admin.css');
  wp_register_style( 'admin_css', plugin_dir_url(__FILE__) . 'css/frm_grids.css');
  wp_register_style( 'admin_css', plugin_dir_url(__FILE__) . 'css/formidableforms.css');
  wp_enqueue_style( 'admin_css');
 }

add_action( 'admin_enqueue_scripts', 'load_admin_style' );

// js for back-end 
function wstest_admin_script() {
    $ajax_nonce = wp_create_nonce( 'wstest' );
    ?>
    <script>
        jQuery(document).ready( function() {
            jQuery.ajax({
            type : "post",
            dataType : "json",
            url : ajaxurl,
            data : {action: "wstest_get_strategy11_data"},
            success: function(response) {
                var output = "";
                output += "<h1>"+response.title+"</h1>";
                if(response.data){
                    output += "<table class='table'>";
                    if(response.data.headers.length>0){
                        output += "<thead>";
                        output += "<tr>";
                        for (var i=0;i<response.data.headers.length;i++) {
                            output += "<th>"+response.data.headers[i]+"</th>";
                        }
                        output += "</tr>";
                        output += "</thead>";
                    }
                    if(response.data.rows){
                        output += "<tbody>";
                        for (var record in response.data.rows) {
                            output += "<tr>";
                            output += "<td>"+response.data.rows[record].id+"</td>";
                            output += "<td>"+response.data.rows[record].fname+"</td>";
                            output += "<td>"+response.data.rows[record].lname+"</td>";
                            output += "<td>"+response.data.rows[record].email+"</td>";
                            output += "<td>"+new Date(response.data.rows[record].date)+"</td>";
                            output += "</tr>";
                        }
                        output += "</tbody>";
                    }
                    output += "</table>";    
                }
                jQuery("#wstest_list").html(output);
            }
         });
         jQuery("#refresh_data").click( function(e) {
            e.preventDefault();
            jQuery.ajax({
            type : "post",
            dataType : "json",
            url : ajaxurl,
            data : {action: "wstest_admin_page_delete_transient"},
            success: function(response) {                
                jQuery("#message").html("Data removed from cache successfully.");
                jQuery("#message").show();
            }
         });
         });
        });
    </script>
    <?php
}

add_action('admin_footer', 'wstest_admin_script');

