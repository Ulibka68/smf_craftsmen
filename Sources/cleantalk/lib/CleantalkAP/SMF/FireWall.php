<?php

namespace CleantalkAP\SMF;

use CleantalkAP\Variables\Server;
use CleantalkAP\SMF\Helper as SpbcHelper;
use CleantalkAP\Variables\Post;

/**
 * CleanTalk Security Firewall class
 *
 * @package Security Plugin by CleanTalk
 * @subpackage Firewall
 * @Version 2.0
 * @author Cleantalk team (welcome@cleantalk.org)
 * @copyright (C) 2014 CleanTalk team (http://cleantalk.org)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 * @see           https://github.com/CleanTalk/security-malware-firewall
 */

class FireWall
{
	private $db; // Database handler
	private $store_interval = 300;
	private $chance_to_clean = 100;
	
	public $ip_array = array(); // Array with detected IPs
	public $blocked_ip = '';    // Blocked IP
	public $passed_ip = '';     // Passed IP
	public $result = null;      // Result
	
	public $waf_result = null;
	
	// TC
	public $tc_enabled = false; // Traffic control
	public $tc_limit = 1000;    // Traffic control limit requests
	public $tc_period = 3600;   // Traffic control block period
	public $tc_skip = false;    // Skip traffic control check
	
	// WAF
	public $waf_enabled       = false;
	public $waf_xss_check     = false;
	public $waf_sql_check     = false;
	public $waf_file_check    = false;
	public $waf_exploit_check = false;
	public $waf_pattern      = array(); // Why WAF is triggered (reason)
	
	// FLAGS
	public $whitelisted = false;
	public $trusted     = false;
	
	// MISC
	public $was_logged_in = false;
	
	public $waf_xss_patterns = array(
		'<script>',
		'</script>',
		'javascript:',
//		'data:',
	);
	
	public $waf_sql_patterns = array(
		'-\d\s?union',
		';\s?union',
		';\s?or',
		'pid=\d+\+union\+select\+0x5e2526,0x5e2526,0x5e2526,'
	);
	
	public $waf_exploit_patterns = array(
		'(random.*)?action=update-plugin(.*random)?',
	);
	
	public $waf_file_mime_check = array(
		'text/x-php',
		'text/plain',
		'image/x-icon',
	);
	
	public $statuses_priority = array(
		'PASS',
		'DENY',
		'DENY_BY_NETWORK',
		'DENY_BY_DOS',
		'PASS_BY_WHITELIST',
		'PASS_BY_TRUSTED_NETWORK', // Highest
	);
	/**
	 * @var int
	 */
	
	function __construct($params = array()){
		
		// TC
		$this->tc_enabled = isset($params['tc_enabled']) ? (bool)$params['tc_enabled'] : false;
		$this->tc_limit   = isset($params['tc_limit'])   ? (int)$params['tc_limit']    : 1000;
		$this->tc_period   = isset($params['tc_period'])   ? (int)$params['tc_period']  : 3600;
		$this->tc_period   = isset($params['tc_period'])   ? (int)$params['tc_period']  : 3600;
		
		$this->chance_to_clean = 100; // from 0 to 1000
		$this->store_interval = 300; // in seconds
		
		// WAF
		$this->waf_enabled       = isset($params['waf_enabled'])       ? (bool)$params['waf_enabled']       : false;
		$this->waf_xss_check     = isset($params['waf_xss_check'])     ? (bool)$params['waf_xss_check']     : false;
		$this->waf_sql_check     = isset($params['waf_sql_check'])     ? (bool)$params['waf_sql_check']     : false;
		$this->waf_file_check    = isset($params['waf_file_check'])    ? (bool)$params['waf_file_check']    : false;
		$this->waf_exploit_check = isset($params['waf_exploit_check']) ? (bool)$params['waf_exploit_check'] : false;
		
		// MISC
		$this->was_logged_in = isset($params['was_logged_in'])         ? (bool)$params['was_logged_in']     : false;
		
		$this->ip_array    = (array)static::ip__get(array('real'));
		
		$this->db = $params['db'];
	}
	
	static public function ip__get($ip_types = array('real', 'remote_addr', 'x_forwarded_for', 'x_real_ip', 'cloud_flare')){
		
		$result = (array)SpbcHelper::ip__get($ip_types);
		
		global $spbc;
		
		if(isset($_GET['spbct_test_ip'], $_GET['spbct_test'], $spbc->settings['spbc_key']) && $_GET['spbct_test'] == md5($spbc->settings['spbc_key'])){
			$ip_type = SpbcHelper::ip__validate($_GET['spbct_test_ip']);
			$test_ip = $ip_type == 'v6' ? SpbcHelper::ip__v6_normalize($_GET['spbct_test_ip']) : $_GET['spbct_test_ip'];
			if($ip_type)
				$result['test'] = $test_ip;
		}
		
		return $result;
	}
	
	public function ip__test(){
	
		global $wpdb;
		
		$fw_results = array();
		
		foreach($this->ip_array as $ip_origin => $current_ip){
			
			$ip_type = SpbcHelper::ip__validate($current_ip);
			
			if($ip_type && $ip_type == 'v4'){
				
				$current_ip_v4 = sprintf("%u", ip2long($current_ip));
				
				$sql = 'SELECT status, is_personal
				FROM `'. SPBC_TBL_FIREWALL_DATA ."` 
				WHERE spbc_network_4 = $current_ip_v4 & spbc_mask_4
				AND ipv6 = 0;";
				
			}elseif($ip_type){
				
				$current_ip_txt = explode(':', $current_ip);
				$current_ip_1 = hexdec($current_ip_txt[0].$current_ip_txt[1]);
				$current_ip_2 = hexdec($current_ip_txt[2].$current_ip_txt[3]);
				$current_ip_3 = hexdec($current_ip_txt[4].$current_ip_txt[5]);
				$current_ip_4 = hexdec($current_ip_txt[6].$current_ip_txt[7]);
				
				$sql = 'SELECT status, is_personal
				FROM `'. SPBC_TBL_FIREWALL_DATA ."` 
				WHERE spbc_network_1 = $current_ip_1 & spbc_mask_1
				AND   spbc_network_2 = $current_ip_2 & spbc_mask_2
				AND   spbc_network_3 = $current_ip_3 & spbc_mask_3
				AND   spbc_network_4 = $current_ip_4 & spbc_mask_4
				AND   ipv6 = 1;";
			}
			
			$result = $wpdb->get_results($sql, ARRAY_A);
			
			// In base
			if(!empty($result)){
				
				$in_base = true;
				foreach($result as $entry){
					switch ($entry['status']) {
						case 2:	 $fw_results[] = array('ip' => $current_ip, 'is_personal' => (bool)$entry['is_personal'], 'status' => 'PASS_BY_TRUSTED_NETWORK',); $this->tc_skip = true; break;
						case 1:	 $fw_results[] = array('ip' => $current_ip, 'is_personal' => (bool)$entry['is_personal'], 'status' => 'PASS_BY_WHITELIST',);       $this->tc_skip = true; break;
						case 0:	 $fw_results[] = array('ip' => $current_ip, 'is_personal' => (bool)$entry['is_personal'], 'status' => 'DENY',);                    break;
						case -1: $fw_results[] = array('ip' => $current_ip, 'is_personal' => (bool)$entry['is_personal'], 'status' => 'DENY_BY_NETWORK',);         break;
						case -2: $fw_results[] = array('ip' => $current_ip, 'is_personal' => (bool)$entry['is_personal'], 'status' => 'DENY_BY_DOS',);             break;						
					}
				}
				
			// Not in base
			}else
				$fw_results[] = array('ip' => $current_ip, 'is_personal' => false, 'status' => 'PASS',);
		}
		
		$current_fw_result_priority = 0;
		foreach ($fw_results as $fw_result) {
			$priority = array_search($fw_result['status'], $this->statuses_priority) + ($fw_result['is_personal'] ? count($this->statuses_priority) : 0);
			if($priority >= $current_fw_result_priority){
				$current_fw_result_priority = $priority;
				$this->result = $fw_result['status'];
				$this->passed_ip = $fw_result['ip'];
				$this->blocked_ip = $fw_result['ip'];
			}
		}
		
		if(!$this->tc_enabled && $priority == 0){
			$this->result = null;
			$this->passed_ip = '';
			$this->blocked_ip = '';
		}
	}
	
	public function tc__test(){
		if($this->tc_enabled && !$this->tc_skip && !$this->was_logged_in){
			$this->tc__clear_table();
			$time = time();
			foreach($this->ip_array as $ip_origin => $current_ip){
				$result = $this->db->get_results(
					"SELECT SUM(entries) as total_count"
					. ' FROM `' . SPBC_TBL_TC_LOG . '`'
					. " WHERE ip = '$current_ip' AND interval_start < '$time';",
					OBJECT
				);
				if(!empty($result) && $result[0]->total_count >= $this->tc_limit){
					$this->result = 'DENY_BY_DOS';
					$this->blocked_ip = $current_ip;
					return;
				}
			}
		}
	}
	
	public function tc__update_logs( $ip = array() ){
		$ip = !empty( $ip ) ? $ip : $this->ip_array;
		$interval_time = SpbcHelper::time__get_interval_start( $this->store_interval );
		foreach($this->ip_array as $ip_origin => $current_ip){
			$id = md5( $current_ip . $interval_time );
			$this->db->query(
				"INSERT INTO " . SPBC_TBL_TC_LOG . " SET
					id = '$id',
					ip = '$current_ip',
					entries = 1,
					interval_start = $interval_time
				ON DUPLICATE KEY UPDATE
					ip = ip,
					entries = entries + 1,
					interval_start = $interval_time;"
			);
		}
	}
	
	
	public function tc__clear_table(){
		if( rand( 0, 1000 ) < $this->chance_to_clean ){
			$interval_start = SpbcHelper::time__get_interval_start( $this->tc_period );
			$this->db->query(
				'DELETE
				FROM ' . SPBC_TBL_TC_LOG . '
				WHERE interval_start < '. $interval_start .'
				LIMIT 100000;'
			);
		}
	}
	
	public function waf__test(){
		
		if($this->waf_enabled && !in_array($this->result, array('PASS_BY_TRUSTED_NETWORK', 'PASS_BY_WHITELIST'))){
			
			// XSS
			if($this->waf_xss_check){
				if($this->waf_xss_check($_POST) || $this->waf_xss_check($_GET) || $this->waf_xss_check($_COOKIE)){
					$this->result = 'DENY_BY_WAF_XSS';
					$this->blocked_ip = end($this->ip_array);
				}
			}
			
			// SQL-injection
			if($this->waf_sql_check){
				if($this->waf_sql_check($_POST) || $this->waf_sql_check($_GET)){
					$this->result = 'DENY_BY_WAF_SQL';
					$this->blocked_ip = end($this->ip_array);
				}
			}
			
			// File
			if($this->waf_file_check){
				if($this->waf_file_check()){
					$this->result = 'DENY_BY_WAF_FILE';
					$this->blocked_ip = end($this->ip_array);
				}
			}
			
			// Exploits
			if($this->waf_exploit_check){
				if($this->waf_exploit_check()){
					$this->result = 'DENY_BY_WAF_EXPLOIT';
					$this->blocked_ip = end($this->ip_array);
				}
			}
		}
	}
	
	public function waf_xss_check($arr){
		foreach($arr as $name => $param){
			if(is_array($param)){
				$result = $this->waf_xss_check($param);
				if($result === true)
					return true;
				continue;
			}
			foreach($this->waf_xss_patterns as $pattern){
				if(stripos($param, $pattern) !== false){
					$this->waf_pattern = array('critical' => $pattern);
					return true;
				}
			}
			// Test
			if($name == 'spbct_test_waf' && $param == 'xss'){
				$this->waf_pattern = array('critical' => 'test');
				return true;
			}
		}
	}
	
	public function waf_sql_check($arr){
		foreach($arr as $name => $param){
			if(is_array($param)){
				$result = $this->waf_sql_check($param);
				if($result === true)
					return true;
				continue;
			}
			foreach($this->waf_sql_patterns as $pattern){
				if(preg_match('/'.$pattern.'/i', $param) === 1){
					$this->waf_pattern = array('critical' =>  $pattern);
					return true;
				}
			}
			// Test
			if($name == 'spbct_test_waf' && $param == 'sql'){
				$this->waf_pattern = array('critical' => 'test');
				return true;
			}
		}
	}
	
	public function waf_exploit_check(){
		$query = filter_input(INPUT_SERVER, 'QUERY_STRING');
		if(!empty($query)){
			foreach($this->waf_exploit_patterns as $pattern){
				if(preg_match('/'.$pattern.'/i', $query) === 1){
					$this->waf_pattern = array('critical' =>  $pattern);
					return true;
				}
			}
			// Test
			if(strpos($query, 'spbct_test_waf=exploit') !== false){
				$this->waf_pattern = array('critical' => 'test');
				return true;
			}
		}
	}
	
	/**
	 * Check uploaded files for malicious code
	 * 
	 * @todo Mime tipe detection from file content
	 * @return boolean Does the file contain malicious code
	 */
	public function waf_file_check(){
		if(!empty($_FILES)){
			foreach($_FILES as $filez){
				if ((empty($filez['errror']) || $filez['errror'] == UPLOAD_ERR_OK)) {
					$filez['tmp_name'] = is_array($filez['tmp_name']) ? $filez['tmp_name'] : array($filez['tmp_name']);
					foreach($filez['tmp_name'] as $file){
						if(is_string($file) && is_uploaded_file($file) && is_readable($file) && (function_exists('mime_content_type') && in_array(mime_content_type($file), $this->waf_file_mime_check))){
							$fileh = new SpbcScannerH(null, array('content' => file_get_contents($file)));
							if(empty($fileh->error)){
								$fileh->process_file();
								if(!empty($fileh->verdict)){
									foreach($fileh->verdict as $severity => $result){
										$this->waf_pattern[$severity] = reset($result);
									}
									return true;
								}
							}
						}
					}
				}
			}
		}
	}
	
	// AJAX callback for detailes about latest blocked file
	public static function waf_file__get_last_blocked_info()
	{
		check_ajax_referer('spbc_secret_nonce', 'security');
		
		global $wpdb;
		
		$timestamp = intval(Post::get( 'timestamp' ));
		
		// Select only latest ones.
		$result = $wpdb->get_results(
			'SELECT *'
			.' FROM '. SPBC_TBL_FIREWALL_LOG
			.' WHERE status = "DENY_BY_WAF_FILE" AND entry_timestamp > '.($timestamp - 2)
			.' ORDER BY entry_timestamp DESC LIMIT 1;'
			, OBJECT
		);
		
		if($result){
			$result = $result[0];
			$out = array(
				'blocked' => true,
				'warning' => __('Security by CleanTalk: File was blocked by Web Application FireWall.', 'security-malware-firewall'),
				'pattern_title' => __('Detected pattern: ', 'security-malware-firewall'),
				'pattern' => json_decode($result->pattern, true),
			);
		}else
			$out = array('blocked' => false);
		
		die(json_encode($out));
	}
	
	public function _die($service_id, $reason = '', $additional_reason = ''){
		
		// Adding block reason
		switch($reason){
			case 'DENY':                $reason = __('Blacklisted', 'security-malware-firewall');                      break;
			case 'DENY_BY_NETWORK':	    $reason = __('Hazardous network', 'security-malware-firewall');	               break; 
			case 'DENY_BY_DOS':         $reason = __('Blocked by DoS prevention system', 'security-malware-firewall'); break;
			case 'DENY_BY_WAF_XSS':	    $reason = __('Blocked by Web Application Firewall: XSS atatck detected.',    'security-malware-firewall'); break;
			case 'DENY_BY_WAF_SQL':	    $reason = __('Blocked by Web Application Firewall: SQL-injection detected.', 'security-malware-firewall'); break;
			case 'DENY_BY_WAF_EXPLOIT':	$reason = __('Blocked by Web Application Firewall: Exploit detected.',       'security-malware-firewall'); break;
			case 'DENY_BY_WAF_FILE':    $reason = __('Blocked by Web Application Firewall: Malicious files upload.', 'security-malware-firewall'); break;
		}
		
		$spbc_die_page = file_get_contents(SPBC_PLUGIN_DIR . 'inc/spbc_die_page.html');
		
		$spbc_die_page = str_replace( "{TITLE}", __('Blocked: Security by CleanTalk', 'security-malware-firewall'),     $spbc_die_page );
		$spbc_die_page = str_replace( "{REMOTE_ADDRESS}", $this->blocked_ip,     $spbc_die_page );
		$spbc_die_page = str_replace( "{SERVICE_ID}",     $service_id,           $spbc_die_page );
		$spbc_die_page = str_replace( "{HOST}",           $_SERVER['HTTP_HOST'], $spbc_die_page );
		$spbc_die_page = str_replace( "{TEST_TITLE}",     (!empty($_GET['spbct_test']) ? __('This is the testing page for Security FireWall', 'security-malware-firewall') : ''), $spbc_die_page );
		$spbc_die_page = str_replace( "{REASON}",         $reason, $spbc_die_page );
		$spbc_die_page = str_replace( "{GENERATED_TIMESTAMP}",    time(), $spbc_die_page );
		$spbc_die_page = str_replace( "{FALSE_POSITIVE_WARNING}", __('Maybe you\'ve been blocked by a mistake. Please refresh the page (press CTRL + F5) or try again later.', 'security-malware-firewall'), $spbc_die_page );
		
		if(headers_sent() === false){
			header('Expires: '.date(DATE_RFC822, mktime(0, 0, 0, 1, 1, 1971)));
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: post-check=0, pre-check=0', FALSE);
			header('Pragma: no-cache');
			header("HTTP/1.0 403 Forbidden");
			$spbc_die_page = str_replace("{GENERATED}", "", $spbc_die_page);
		}else{
			$spbc_die_page = str_replace("{GENERATED}", "<h2 class='second'>The page was generated at&nbsp;".date("D, d M Y H:i:s")."</h2>",$spbc_die_page);
		}
		wp_die( $spbc_die_page, "Blacklisted", Array('response'=>403) );
	}
	
	static public function firewall_update($spbc_key, $file_url = null, $immediate = false){
		
		global $wpdb;
		
		// Getting remote file name
		if(!$file_url){
		
			$result = API::method__security_firewall_data_file($spbc_key);
		
			if(empty($result['error'])){
			
				if( !empty($result['file_url']) ){
					
					$file_url = $result['file_url'];
					
					if(!$immediate){
						
						sleep(5); // Wait till file will be created
						
						// Asynchronously call
						return SpbcHelper::http__request(
							get_option('siteurl'), 
							array(
								'spbc_remote_call_token'  => md5($spbc_key),
								'spbc_remote_call_action' => 'update_security_firewall',
								'plugin_name'             => 'spbc',
								'file_url'                => $result['file_url'],
							),
							array('get', 'async')
						);
					}
					
				}else
					return array('error' => 'BAD_RESPONSE');
			}else
				return $result;
		}
			
		// Check for remote file
		if($file_url){
			
			if( SpbcHelper::http__request__get_response_code($file_url) === 200){ // Check if it's there
				
				$gz_data = SpbcHelper::http__request__get_content($file_url);
				
				if(SpbcHelper::get_mime_type($gz_data)){
					
					if(function_exists('gzdecode')) {
	
						$data = gzdecode( $gz_data );
	
						if($data !== false){
	
							$lines = SpbcHelper::buffer__parse__csv($data);
	
						}else
							return array('error' => 'COULDNT_UNPACK');
					}else
						return array('error' => 'Wrong mime type');
				}else
					return array('error' => 'Function gzdecode not exists. Please update your PHP to version 5.4');

				$wpdb->query('DELETE FROM `'. SPBC_TBL_FIREWALL_DATA .'`;');

				for( $count_result = 0; current($lines) !== false; ) {

					$query = "INSERT INTO `" . SPBC_TBL_FIREWALL_DATA . "` VALUES ";

					for ( $i=0; SPBC_WRITE_LIMIT !== $i && current($lines) !== false; $i++, $count_result++, next($lines) ) {

						$entry = current($lines);

						if ( empty( $entry ) ) {
							continue;
						}
						if ( SPBC_WRITE_LIMIT !== $i ) {

							$ip   = $entry[0];
							$mask = $entry[1];
							// $comment = $entry[2]; // Comment from user
							$status      = isset( $entry[3] ) ? $entry[3] : 0;
							$is_personal = isset( $entry[4] ) ? intval( $entry[4] ) : 0;

							// IPv4
							if ( is_numeric( $ip ) ) {
								$mask  = sprintf( '%u', ip2long( long2ip( - 1 << ( 32 - (int) $mask ) ) ) );
								$query .= "(0, 0, 0, $ip, 0, 0, 0, $mask, $status, 0, $is_personal),";
								// IPv6
							} else {
								$ip = substr( $ip, 1, - 1 ); // Cut ""
								$ip = SpbcHelper::ip__v6_normalize( $ip ); // Normalize
								$ip = explode( ':', $ip );

								$ip_1 = hexdec( $ip[0] . $ip[1] );
								$ip_2 = hexdec( $ip[2] . $ip[3] );
								$ip_3 = hexdec( $ip[4] . $ip[5] );
								$ip_4 = hexdec( $ip[6] . $ip[7] );

								$ip_1 = $ip_1 ? $ip_1 : 0;
								$ip_2 = $ip_2 ? $ip_2 : 0;
								$ip_3 = $ip_3 ? $ip_3 : 0;
								$ip_4 = $ip_4 ? $ip_4 : 0;

								for ( $k = 1; $k < 5; $k ++ ) {
									$curr = 'mask_' . $k;
									$curr = pow( 2, 32 ) - pow( 2, 32 - ( $mask - 32 >= 0 ? 32 : $mask ) );
									$mask = ( $mask - 32 <= 0 ? 0 : $mask - 32 );
								}
								$query .= "($ip_1, $ip_2, $ip_3, $ip_4, $mask_1, $mask_2, $mask_3, $mask_4, $status, 1, $is_personal),";
							}
						}

					};

					//Exclusion for servers IP (SERVER_ADDR)
					if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
						$exclusions[] = SpbcHelper::dns__resolve( $_SERVER['HTTP_HOST'] );
						$exclusions[] = '127.0.0.1';
						foreach ( $exclusions as $exclusion ) {
							if ( SpbcHelper::ip__validate( $exclusion ) && sprintf( '%u', ip2long( $exclusion ) ) ) {
								$query .= '(0, 0, 0, ' . sprintf( '%u', ip2long( $exclusion ) ) . ', 0, 0, 0, ' . sprintf( '%u', 4294967295 << ( 32 - 32 ) ) . ', 2, 0, 0),';
								$query .= '(0, 0, 0, ' . sprintf( '%u', ip2long( $exclusion ) ) . ', 0, 0, 0, ' . sprintf( '%u', 4294967295 << ( 32 - 32 ) ) . ', 2, 0, 0),';
							}
						}
					}

					$wpdb->query( substr( $query, 0, - 1 ) . ';' );

				}

				return $count_result;

			}else
				return array('error' => 'NO_REMOTE_FILE_FOUND');
		}
	}
	
	//Add entries to SFW log
	public function update_logs($ip, $status, $pattern = array())
	{
		if(empty($ip) || empty($status))
			return;
		
		// Parameters
		$time            = time();
		$page_url        = addslashes(Server::get('HTTPS') != 'off' ? 'https://' : 'http://') . Server::get('HTTP_HOST') . Server::get('REQUEST_URI');
		$page_url        = substr($page_url, 0 , 4096);
		$http_user_agent = Server::get('HTTP_USER_AGENT')
			? addslashes(htmlspecialchars(substr(Server::get('HTTP_USER_AGENT'), 0, 300)))
			: 'unknown';
		$request_method  = Server::get('REQUEST_METHOD');
		$x_forwarded_for = addslashes(htmlspecialchars(substr(Server::get('HTTP_X_FORWARDED_FOR'), 0 , 15)));
		$id              = md5($ip.$http_user_agent.$status);
		$pattern         = !empty($pattern)
			? json_encode($pattern)
			: '';
		
		$this->db->query(
			"INSERT INTO ". SPBC_TBL_FIREWALL_LOG ." SET
				entry_id = '$id',
				ip_entry = '$ip',
				entry_timestamp = $time,
				status = '$status',
				pattern = IF('$pattern' = '', NULL, '$pattern'),
				requests = 1,
				page_url = '$page_url',
				http_user_agent = '$http_user_agent',
				request_method = '$request_method',
				x_forwarded_for = IF('$x_forwarded_for' = '', NULL, '$x_forwarded_for')
			ON DUPLICATE KEY UPDATE
				ip_entry = ip_entry,
				entry_timestamp = $time,
				status = '$status',
				pattern = IF('$pattern' = '', NULL, '$pattern'),
				requests = requests + 1,
				page_url = '$page_url',
				http_user_agent = http_user_agent,
				request_method = '$request_method',
				x_forwarded_for = IF('$x_forwarded_for' = '', NULL, '$x_forwarded_for')"
		);
	}
	
	//*Send and wipe SFW log
	public static function send_logs($spbc_key){
		
		global $wpdb;
		
		//Getting logs
		$result = $wpdb->get_results("SELECT * FROM `". SPBC_TBL_FIREWALL_LOG ."` LIMIT ".SPBC_SELECT_LIMIT, ARRAY_A);
		
		if(count($result)){
			//Compile logs
			$data = array();
			
			foreach($result as $key => $value){
				
				//Compile log
				$to_data = array(
					'datetime'        => date('Y-m-d H:i:s', $value['entry_timestamp']),
					'page_url'        => $value['page_url'],
					'visitor_ip'      => SpbcHelper::ip__validate($value['ip_entry']) == 'v4' ? (int)sprintf('%u', ip2long($value['ip_entry'])) : (string)$value['ip_entry'],
					'http_user_agent' => $value['http_user_agent'],
					'request_method'  => $value['request_method'],
					'x_forwarded_for' => $value['x_forwarded_for'],
					'hits'            => (int)$value['requests'],
				);
				
				// Legacy
				switch($value['status']){
					case 'PASS_BY_TRUSTED_NETWORK': $to_data['status_efw'] = 3;  break;
					case 'PASS_BY_WHITELIST':       $to_data['status_efw'] = 2;  break;
					case 'PASS':                    $to_data['status_efw'] = 1;  break;
					case 'DENY':                    $to_data['status_efw'] = 0;  break;
					case 'DENY_BY_NETWORK':         $to_data['status_efw'] = -1; break;
					case 'DENY_BY_DOS':             $to_data['status_efw'] = -2; break;
					case 'DENY_BY_WAF_XSS':         $to_data['status_efw'] = -3; $to_data['waf_attack_type'] = 'XSS';           $to_data['waf_comment'] = $value['pattern']; break;
					case 'DENY_BY_WAF_SQL':         $to_data['status_efw'] = -4; $to_data['waf_attack_type'] = 'SQL_INJECTION'; $to_data['waf_comment'] = $value['pattern']; break;
					case 'DENY_BY_WAF_FILE':        $to_data['status_efw'] = -5; $to_data['waf_attack_type'] = 'MALWARE';       $to_data['waf_comment'] = $value['pattern']; break;
					case 'DENY_BY_WAF_EXPLOIT':     $to_data['status_efw'] = -6; $to_data['waf_attack_type'] = 'EXPLOIT';       $to_data['waf_comment'] = $value['pattern']; break;
				}
				
				switch($value['status']){
					case 'PASS_BY_TRUSTED_NETWORK': $to_data['status'] = 3;  break;
					case 'PASS_BY_WHITELIST':       $to_data['status'] = 2;  break;
					case 'PASS':                    $to_data['status'] = 1;  break;
					case 'DENY':                    $to_data['status'] = 0;  break;
					case 'DENY_BY_NETWORK':         $to_data['status'] = -1; break;
					case 'DENY_BY_DOS':             $to_data['status'] = -2; break;
					case 'DENY_BY_WAF_XSS':         $to_data['status'] = -3; $to_data['waf_attack_type'] = 'XSS';           $to_data['waf_comment'] = $value['pattern']; break;
					case 'DENY_BY_WAF_SQL':         $to_data['status'] = -4; $to_data['waf_attack_type'] = 'SQL_INJECTION'; $to_data['waf_comment'] = $value['pattern']; break;
					case 'DENY_BY_WAF_FILE':        $to_data['status'] = -5; $to_data['waf_attack_type'] = 'MALWARE';       $to_data['waf_comment'] = $value['pattern']; break;
					case 'DENY_BY_WAF_EXPLOIT':     $to_data['status'] = -6; $to_data['waf_attack_type'] = 'EXPLOIT';       $to_data['waf_comment'] = $value['pattern']; break;
				}
				
				$data[] = $to_data;
			
			} unset($key, $value, $result, $to_data);
			
			// Sendings request
			$result = API::method__security_logs__sendFWData($spbc_key, $data);
			
			// Checking answer and deleting all lines from the table
			if(empty($result['error'])){
				if($result['rows'] == count($data)){
					$wpdb->query("DELETE FROM `". SPBC_TBL_FIREWALL_LOG ."`");
					return count($data);
				}
			}else{
				return $result;
			}
		}else{
			return array(
				'error' => 'NO_LOGS_TO_SEND'
			);
		}
	}
}
