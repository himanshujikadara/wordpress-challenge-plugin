<?php
 use PHPUnit\Framework\TestCase;
 
require_once("../../../../wp-load.php");
 
/**
 * An  test case.
 */
class MyPlugin_Test extends TestCase {
 
    function test_request_runs_multiple_time_per_hour(){
        $expires = (int) get_option( '_transient_timeout_strategy11_data_storage', 0 );
        $time_left = $expires - time();
        $this->assertLessThan(60*60, $time_left);
    }
    
    function test_is_table_showing_expected_results(){
        $json_key = 'strategy11_data_storage';
        $data = get_transient($json_key);
        
        $args = array ('headers' => array('Content-Type' => 'application/json'));
        $rawData = wp_remote_get('http://api.strategy11.com/wp-json/challenge/v1/1', $args);
        $data2 = wp_remote_retrieve_body( $rawData );
        
        $this->assertEquals($data, $data2);
    }
}