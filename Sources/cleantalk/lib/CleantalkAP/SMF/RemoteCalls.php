<?php

namespace CleantalkAP\SMF;

class RemoteCalls
{
	
	const COOLDOWN = 10;
	
	public static function check() {
		return isset($_GET['spbc_remote_call_token'], $_GET['spbc_remote_call_action'], $_GET['plugin_name']) && in_array(filter_input(INPUT_GET, 'plugin_name'), array('security','spbc'))
			? true
			: false;
	}
	
	public static function perform(){
		
		global $spbc;
		
		$action = strtolower(filter_input(INPUT_GET, 'spbc_remote_call_action'));
		$token  = strtolower(filter_input(INPUT_GET, 'spbc_remote_call_token'));
		
		if(array_key_exists($action, $spbc->remote_calls)){
			
			$cooldown = isset($spbc->remote_calls[$action]['cooldown']) ? $spbc->remote_calls[$action]['cooldown'] : self::COOLDOWN;
//			$pass_cooldown = SpbcHelper::ip__get(array('real')) === filter_input(INPUT_SERVER, 'SERVER_ADDR');
//			$pass_cooldown = false; // Temp crutch
			
			if(time() - $spbc->remote_calls[$action]['last_call'] > $cooldown
//				 || $pass_cooldown
			){
				
				$spbc->remote_calls[$action]['last_call'] = time();
				$spbc->save('remote_calls');
				
				// Check API key
				if($token == strtolower(md5($spbc->settings['spbc_key'])) ){
					
					$action = 'action__'.$action;
					
					if(method_exists('SpbcRemoteCalls', $action)){
						
						if(get_option('spbc_deactivation_in_process') === false){ // Continue if plugin is active
							
							if(!empty($_GET['delay'])) sleep($_GET['delay']); // Delay before perform action;
							$out = static::$action();
						
							// Stop execution if plguin is deactivated
						}else{
							delete_option('spbc_deactivation_in_process');
							$out = 'FAIL '.json_encode(array('error' => 'PLUGIN_DEACTIVATION_IN_PROCESS'));
						}
					}else
						$out = 'FAIL '.json_encode(array('error' => 'UNKNOWN_ACTION_METHOD'));
				}else
					$out = 'FAIL '.json_encode(array('error' => 'WRONG_TOKEN'));
			}else
				$out = 'FAIL '.json_encode(array('error' => 'TOO_MANY_ATTEMPTS'));
		}else
			$out = 'FAIL '.json_encode(array('error' => 'UNKNOWN_ACTION'));
		
		die($out);
	}
	
	/**
	 * 
	 */
	static function action__close_renew_banner() {
		global $spbc;
		$spbc->data['notice_show'] = 0;
		$spbc->save('data');
		// Updating cron task
		Cron::updateTask('access_key_notices', 'spbc_access_key_notices', 86400);
		die('OK');
	}
	
	static function action__update_plugin() {
		add_action('template_redirect', 'spbc_update', 1);
	}
	
	static function action__update_security_firewall() {
		$result = spbc_security_firewall_update(true);
		die(empty($result['error']) ? 'OK' : 'FAIL '.json_encode(array('error' => $result['error'])));
	}
	
	static function action__drop_security_firewall() {
		$result = spbc_security_firewall_drop();
		die(empty($result['error']) ? 'OK' : 'FAIL '.json_encode(array('error' => $result['error'])));
	}
	
	static function action__download__quarantine_file() {
		$result = spbc_scanner_file_download(true, $_GET['file_id']);
		if(empty($result['error'])){
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.$result['file_name']);
		}
		die(empty($result['error']) 
			? $result['file_content']
			: 'FAIL '.json_encode(array('error' => $result['error'])));
	}
	
	static function action__update_settings() {
		global $spbc;
		$source = $_GET;
		foreach($spbc->def_settings as $setting => $value){
			if(array_key_exists($setting, $source)){
				$var = $source[$setting];
				$type = gettype($spbc->settings[$setting]);
				settype($var, $type);
				if($type == 'string')
					$var = preg_replace(array('/=/', '/`/'), '', $var);
				$spbc->settings[$setting] = $var;
			}
		}
		$spbc->save('settings');
		die('OK');
	}
	
	static function action__backup_signatures_files() {
		$result = spbc_backup__files_with_signatures();
		die(empty($result['error']) 
			? 'OK'
			: 'FAIL '.json_encode(array('error' => $result['error'])));
	}
	
	static function action__rollback_repair() {
		$result = spbc_rollback($_GET['backup_id']);
		die(empty($result['error']) 
			? 'OK'
			: 'FAIL '.json_encode(array('error' => $result['error'])));
	}
	
	static function action__scanner_clear_hashes() {
		$result = true;
		switch($_GET['type']){
			case 'plugins':            delete_option(SPBC_PLUGINS);                             break;
			case 'themes':             delete_option(SPBC_THEMES);                              break;
			case 'plugins_and_themes': delete_option(SPBC_THEMES); delete_option(SPBC_PLUGINS); break;
			case 'all':                $result = spbc_scanner_clear();                          break;
			default:                   $result = spbc_scanner_clear();                          break;
		}
		die(empty($result['error']) 
			? 'OK'
			: 'FAIL '.json_encode(array('error' => 'COULDNT_CLEAR_ALL_DB_ERROR')));
	}
	
	static function action__scanner_signatures_update() {
		$result = spbc_scanner__signatures_update();
		die(empty($result['error']) 
			? 'OK' . ' ' . (!empty($result['success']) ? $result['success'] : '')
			: 'FAIL '.json_encode(array('error' => $result['error'])));
	}
		
	static function action__scanner__controller() {
		return spbc_scanner__controller();
	}
	
	static function action__scanner__get_remote_hashes() {
		spbc_scanner_get_remote_hashes();
	}
	
	static function action__scanner__count_hashes_plug() {
		spbc_scanner_count_hashes_plug();
	}
	
	static function action__scanner__get_remote_hashes__plug() {
		spbc_scanner_get_remote_hashes__plug();
	}
	
	static function action__scanner__clear_table() {
		spbc_scanner_clear_table();
	}
	
	static function action__scanner__count_files() {
		spbc_scanner_count_files();
	}
	
	static function action__scanner__scan() {
		spbc_scanner_scan();
	}
	
	static function action__scanner__count_files__by_status() {
		spbc_scanner_count_files__by_status();
	}
	
	static function action__scanner__scan_heuristic() {
		spbc_scanner_scan_signatures();
	}
	
	static function action__scanner__scan_signatures() {
		spbc_scanner_scan_signatures();
	}
	
	static function action__scanner__backup_sigantures() {
		spbc_backup__files_with_signatures();
	}
	
	static function action__scanner__count_cure() {
		spbc_scanner_count_cure();
	}
	
	static function action__scanner__cure() {
		spbc_scanner_cure();
	}
	
	static function action__scanner__links_count() {
		spbc_scanner_links_count();
	}
	
	static function action__scanner__links_scan() {
		spbc_scanner_links_scan();
	}
	
	static function action__scanner__frontend_scan() {
		spbc_scanner_frontend__scan();
	}
	
	static function action_scanner__send_results() {
		spbc_scanner_send_results();
	}
}
