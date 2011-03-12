<?php
/* 
 rtorrent for iPhone interface. 99% of this code is shamelessly stolen from rTWi (see http://rtwi.jmk.hu/wiki/)
 I just needed an easy fast way to communicate with rtorrent and rTWi was the ticket. 
 I apologize for any bad etiquette and I hope this attribution is enough :)

 Kramerican Industries (www.kramerican.dk) AHJ
*/

require_once( "config.inc.php" );
require_once( "includes/classes/xmlrpc_handler.inc.php" );
require_once( "includes/tools/functions.time.inc.php" );

function xmlrpc_multiappend( &$xml, &$dnode, &$responses ) {
	$keys = array_keys( $responses );
	reset( $responses );
	while ( list( $rkey, $rval ) = each( $responses ) ) {
		$rval = str_replace( "&", "&amp;", $rval );
		if ( array_key_exists( "{$rkey}_suffix", $responses ) ) {
			$node = $dnode->appendChild( $xml->createElement( $rkey, $rval ) );
			$node->setAttribute( "suffix", $responses["{$rkey}_suffix"] );
			$node->setAttribute( "value", $responses["{$rkey}_value"] );
			unset( $responses["{$rkey}_suffix"] );
			unset( $responses["{$rkey}_value"] );
		} elseif ( array_key_exists( "{$rkey}_value", $responses ) ) {
			$node = $dnode->appendChild( $xml->createElement( $rkey, $rval ) );
			$node->setAttribute( "value", $responses["{$rkey}_value"] );
			unset( $responses["{$rkey}_value"] );
		} elseif ( is_array( $rval ) ) {
		} else {
			$node = $dnode->appendChild( $xml->createElement( $rkey, $rval ) );
		}
	}

	return true;
}

function addviewtypes( &$xml, &$root, &$node, &$view_list, &$viewtypes ) {
	$root->setAttribute( "viewtype", $_SESSION["rtwi_view"] );

	// adding viewtypes
	$vcrnode = $node->appendChild( $xml->createElement( "viewtypes" ) );
	$vcrnode->setAttribute( "value", $_SESSION["rtwi_view"] );
	foreach ( $view_list as $key => $val ) {
		$vcnode = $vcrnode->appendChild( $xml->createElement( "viewtype" ) );
		$vcnode->setAttribute( "value", $val );
		$vcnode->setAttribute( "title", isset( $viewtypes["viewtype_{$val}"] ) ? $viewtypes["viewtype_{$val}"] : $val );
	}
}

function switch_bytes( $bytes, $d_suffix = "" ) {
	switch ( $d_suffix ) {
		case "GB" : {
			$ret = $bytes / 1024 / 1024 / 1024;
			$suffix = "GB";
			break;
		}
		case "MB" : {
			$ret = $bytes / 1024 / 1024;
			$suffix = "MB";
			break;
		}
		case "KB" : {
			$ret = $bytes / 1024;
			$suffix = "KB";
			break;
		}
		default : {
			if ( $bytes >= 1024 * 1024 * 1024 ) {
				$ret = $bytes / 1024 / 1024 / 1024;
				$suffix = "GB";
			} elseif ( $bytes >= 1024 * 1024 ) {
				$ret = $bytes / 1024 / 1024;
				$suffix = "MB";
			} elseif ( $bytes >= 1024 ) {
				$ret = $bytes / 1024;
				$suffix = "KB";
			} else {
				$ret = $bytes;
				$suffix = "B";
			}
		}
	}

	return array( $ret, $suffix );
}

function prepare_serverinfo_responses( &$responses, &$message, &$config ) {
	$bytes_down_arr = switch_bytes( $responses["bytes_down"] );
	$bytes_up_arr = switch_bytes( $responses["bytes_up"] );
	$download_rate_arr = switch_bytes( $responses["download_rate"], "KB" );
	$hash_read_ahead_arr = switch_bytes( $responses["hash_read_ahead"] );
	$max_memory_usage_arr = switch_bytes( $responses["max_memory_usage"] );
	$memory_usage_arr = switch_bytes( $responses["memory_usage"] );
	$preload_min_size_arr = switch_bytes( $responses["preload_min_size"] );
	$preload_required_rate_arr = switch_bytes( $responses["preload_required_rate"] );
	$receive_buffer_size_arr = switch_bytes( $responses["receive_buffer_size"] );
	$send_buffer_size_arr = switch_bytes( $responses["send_buffer_size"] );
	$upload_rate_arr = switch_bytes( $responses["upload_rate"], "KB" );

	$responses["bytes_down_value"] = sprintf( "%.1f", $bytes_down_arr[0] );
	$responses["bytes_down_suffix"] = $bytes_down_arr[1];
	$responses["bytes_up_value"] = sprintf( "%.1f", $bytes_up_arr[0] );
	$responses["bytes_up_suffix"] = $bytes_up_arr[1];
	$responses["download_rate_value"] = sprintf( "%.0f", $download_rate_arr[0] );
	$responses["download_rate_suffix"] = $download_rate_arr[1];
	$responses["hash_read_ahead_value"] = sprintf( "%.1f", $hash_read_ahead_arr[0] );
	$responses["hash_read_ahead_suffix"] = $hash_read_ahead_arr[1];
	$responses["max_memory_usage_value"] = sprintf( "%.1f", $max_memory_usage_arr[0] );
	$responses["max_memory_usage_suffix"] = $max_memory_usage_arr[1];
	$responses["memory_usage_value"] = sprintf( "%.1f", $memory_usage_arr[0] );
	$responses["memory_usage_suffix"] = $memory_usage_arr[1];
	$responses["preload_min_size_value"] = sprintf( "%.1f", $preload_min_size_arr[0] );
	$responses["preload_min_size_suffix"] = $preload_min_size_arr[1];
	$responses["preload_required_rate_value"] = sprintf( "%.1f", $preload_required_rate_arr[0] );
	$responses["preload_required_rate_suffix"] = $preload_required_rate_arr[1];
	$responses["receive_buffer_size_value"] = sprintf( "%.1f", $receive_buffer_size_arr[0] );
	$responses["receive_buffer_size_suffix"] = $receive_buffer_size_arr[1];
	$responses["send_buffer_size_value"] = sprintf( "%.1f", $send_buffer_size_arr[0] );
	$responses["send_buffer_size_suffix"] = $send_buffer_size_arr[1];
	$responses["upload_rate_value"] = sprintf( "%.0f", $upload_rate_arr[0] );
	$responses["upload_rate_suffix"] = $upload_rate_arr[1];

	$responses["check_hash_value"] = $responses["check_hash"] == 0 ? $message["no"] : $message["yes"];
	$responses["safe_sync_value"] = $responses["safe_sync"] == 0 ? $message["no"] : $message["yes"];
	$responses["use_udp_trackers_value"] = $responses["use_udp_trackers"] == 0 ? $message["no"] : $message["yes"];

	$responses["bind_value"] = $responses["bind"] == "0.0.0.0" ? $message["bind_0000"] : $responses["bind"];
	$responses["ip_value"] = $responses["ip"] == "0.0.0.0" ? $message["ip_0000"] : $responses["ip"];
	$responses["max_peers_value"] = $responses["max_peers"] == -1 ? $message["disabled"] : $responses["max_peers"];
	$responses["max_peers_seed_value"] = $responses["max_peers_seed"] == -1 ? $message["disabled"] : $responses["max_peers_seed"];
	$responses["max_uploads_value"] = $responses["max_uploads"] == -1 ? $message["disabled"] : $responses["max_uploads"];
	$responses["min_peers_value"] = $responses["min_peers"] == -1 ? $message["disabled"] : $responses["min_peers"];
	$responses["min_peers_seed_value"] = $responses["min_peers_seed"] == -1 ? $message["disabled"] : $responses["min_peers_seed"];
	$responses["port_open_value"] = $responses["port_open"] == -1 ? $message["no"] : $message["yes"];
	$responses["port_random_value"] = $responses["port_random"] == -1 ? $message["no"] : $message["yes"];
	$responses["port_range_value"] = $responses["port_range"] == -1 ? $message["disabled"] : $responses["port_range"];
	$responses["proxy_address_value"] = $responses["proxy_address"] == "0.0.0.0" ? $message["proxy_address_0000"] : $responses["proxy_address"];
	$responses["tracker_numwant_value"] = $responses["tracker_numwant"] == -1 ? $message["disabled"] : $responses["tracker_numwant"];

	if ( $responses["max_file_size"] == -1 ) {
		$responses["max_file_size_value"] = $message["disabled"];
	} else {
		$max_file_size_arr = switch_bytes( $responses["max_file_size"] );
		$responses["max_file_size_value"] = sprintf( "%.1f", $max_file_size_arr[0] );
		$responses["max_file_size_suffix"] = $max_file_size_arr[1];
	}

	if ( $responses["split_file_size"] == -1 ) {
		$responses["split_file_size_value"] = $message["disabled"];
	} else {
		$split_file_size_arr = switch_bytes( $responses["split_file_size"] );
		$responses["split_file_size_value"] = sprintf( "%.1f", $split_file_size_arr[0] );
		$responses["split_file_size_suffix"] = $split_file_size_arr[1];
	}

	if ( $config["dht"] === "1" ) {
		if ( $responses["dht_statistics_active"] != 0 ) {
			$dht_statistics_bytes_read_arr = switch_bytes( $responses["dht_statistics_bytes_read"] );
			$dht_statistics_bytes_written_arr = switch_bytes( $responses["dht_statistics_bytes_written"] );

			$responses["dht_statistics_bytes_read_value"] = sprintf( "%.1f", $dht_statistics_bytes_read_arr[0] );
			$responses["dht_statistics_bytes_read_suffix"] = $dht_statistics_bytes_read_arr[1];
			$responses["dht_statistics_bytes_written_value"] = sprintf( "%.1f", $dht_statistics_bytes_written_arr[0] );
			$responses["dht_statistics_bytes_written_suffix"] = $dht_statistics_bytes_written_arr[1];
		}

		$responses["dht_statistics_active_value"] = $responses["dht_statistics_active"] == 0 ? $message["dht_0"] : $message["dht_1"];
	}

	ksort( $responses );

	return true;
}

function prepare_downloadlist_responses( &$responses, &$message, &$home_path, &$cwd ) {
	$d_bytes_done_arr = switch_bytes( $responses["d_bytes_done"] );
	$d_chunk_size_arr = switch_bytes( $responses["d_chunk_size"] );
	$d_completed_bytes_arr = switch_bytes( $responses["d_completed_bytes"] );
	$d_down_rate_arr = switch_bytes( $responses["d_down_rate"] );
	$d_down_total_arr = switch_bytes( $responses["d_down_total"] );
	$d_free_diskspace_arr = switch_bytes( $responses["d_free_diskspace"] );
	$d_left_bytes_arr = switch_bytes( $responses["d_left_bytes"] );
	$d_max_file_size_arr = switch_bytes( $responses["d_max_file_size"] );
	$d_size_bytes_arr = switch_bytes( $responses["d_size_bytes"] );
	$d_skip_rate_arr = switch_bytes( $responses["d_skip_rate"] );
	$d_skip_total_arr = switch_bytes( $responses["d_skip_total"] );
	$d_up_rate_arr = switch_bytes( $responses["d_up_rate"] );
	$d_up_total_arr = switch_bytes( $responses["d_up_total"] );

	$responses["d_bytes_done_value"] = sprintf( "%.1f", $d_bytes_done_arr[0] );
	$responses["d_bytes_done_suffix"] = $d_bytes_done_arr[1];
	$responses["d_chunk_size_value"] = sprintf( "%.1f", $d_chunk_size_arr[0] );
	$responses["d_chunk_size_suffix"] = $d_chunk_size_arr[1];
	$responses["d_completed_bytes_value"] = sprintf( "%.1f", $d_completed_bytes_arr[0] );
	$responses["d_completed_bytes_suffix"] = $d_completed_bytes_arr[1];
	$responses["d_down_rate_value"] = sprintf( "%.1f", $d_down_rate_arr[0] );
	$responses["d_down_rate_suffix"] = $d_down_rate_arr[1];
	$responses["d_down_total_value"] = sprintf( "%.1f", $d_down_total_arr[0] );
	$responses["d_down_total_suffix"] = $d_down_total_arr[1];
	$responses["d_free_diskspace_value"] = sprintf( "%.1f", $d_free_diskspace_arr[0] );
	$responses["d_free_diskspace_suffix"] = $d_free_diskspace_arr[1];
	$responses["d_left_bytes_value"] = sprintf( "%.1f", $d_left_bytes_arr[0] );
	$responses["d_left_bytes_suffix"] = $d_left_bytes_arr[1];
	$responses["d_max_file_size_value"] = sprintf( "%.1f", $d_max_file_size_arr[0] );
	$responses["d_max_file_size_suffix"] = $d_max_file_size_arr[1];
	$responses["d_size_bytes_value"] = sprintf( "%.1f", $d_size_bytes_arr[0] );
	$responses["d_size_bytes_suffix"] = $d_size_bytes_arr[1];
	$responses["d_skip_rate_value"] = sprintf( "%.1f", $d_skip_rate_arr[0] );
	$responses["d_skip_rate_suffix"] = $d_skip_rate_arr[1];
	$responses["d_skip_total_value"] = sprintf( "%.1f", $d_skip_total_arr[0] );
	$responses["d_skip_total_suffix"] = $d_skip_total_arr[1];
	$responses["d_up_rate_value"] = sprintf( "%.1f", $d_up_rate_arr[0] );
	$responses["d_up_rate_suffix"] = $d_up_rate_arr[1];
	$responses["d_up_total_value"] = sprintf( "%.1f", $d_up_total_arr[0] );
	$responses["d_up_total_suffix"] = $d_up_total_arr[1];

	$responses["d_active_value"] = $responses["d_active"] == 1 ? $message["d_active_1"] : $message["d_active_0"];
	$responses["d_complete_value"] = $responses["d_complete"] == 1 ? $message["d_complete_1"] : $message["d_complete_0"];
	$responses["d_creation_date_value"] = date( "Y-m-d H:i:s", $responses["d_creation_date"] );
	$responses["d_ignore_commands_value"] = $responses["d_ignore_commands"] == 1 ? $message["d_ignore_commands_1"] : $message["d_ignore_commands_0"];
	$responses["d_hash_checked_value"] = $responses["d_hash_checked"] == 1 ? $message["d_hash_checked_1"] : $message["d_hash_checked_0"];
	$responses["d_hash_checking_value"] = $responses["d_hash_checking"] == 1 ? $message["d_hash_checking_1"] : $message["d_hash_checking_0"];
	$responses["d_multi_file_value"] = $responses["d_multi_file"] == 1 ? $message["d_multi_file_multi"] : $message["d_multi_file_single"];
	$responses["d_open_value"] = $responses["d_open"] == 1 ? $message["d_open_1"] : $message["d_open_0"];
	$responses["d_peer_exchange_value"] = $responses["d_peer_exchange"] == 0 ? $message["disabled"] : $message["enabled"];
	$responses["d_peers_max_value"] = $responses["d_peers_max"] == -1 ? $message["disabled"] : $responses["d_peers_max"];
	$responses["d_peers_min_value"] = $responses["d_peers_min"] == -1 ? $message["disabled"] : $responses["d_peers_min"];
	$responses["d_pex_active_value"] = $responses["d_pex_active"] == 1 ? $message["yes"] : $message["no"];
	$responses["d_private_value"] = $responses["d_private"] == 1 ? $message["yes"] : $message["no"];
	$responses["d_state_value"] = $responses["d_state"] == 1 ? $message["d_state_1"] : $message["d_state_0"];
	$responses["d_state_changed_value"] = date( "Y-m-d H:i:s", $responses["d_state_changed"] );
	$responses["d_tracker_numwant_value"] = $responses["d_tracker_numwant"] == -1 ? $message["disabled"] : $responses["d_tracker_numwant"];
	$responses["d_uploads_max_value"] = $responses["d_uploads_max"] == -1 ? $message["disabled"] : $responses["d_uploads_max"];

	if ( $responses["d_down_rate"] > 0 ) {
		$responses["d_estimated_time"] = $responses["d_left_bytes"] / $responses["d_down_rate"];
		$responses["d_estimated_time_value"] = sprintf( "%dd %02d:%02d", floor( $responses["d_estimated_time"] / 86400 ), floor( $responses["d_estimated_time"] / 3600 ) % 24, floor( $responses["d_estimated_time"] / 60 ) % 60 );
		$responses["d_percentage"] = $responses["d_completed_bytes"] / $responses["d_size_bytes"];
		$responses["d_percentage_value"] = floor( $responses["d_completed_bytes"] / $responses["d_size_bytes"] * 100 );
	} elseif ( $responses["d_hashing"] != 0 ) {
		$responses["d_estimated_time"] = "";
		$responses["d_estimated_time_value"] = "";
		$responses["d_percentage"] = $responses["d_completed_bytes"] / $responses["d_size_bytes"];
		$responses["d_percentage_value"] = floor( $responses["d_completed_bytes"] / $responses["d_size_bytes"] * 100 );
		$responses["d_hpercentage"] = $responses["d_chunks_hashed"] / $responses["d_size_chunks"];
		$responses["d_hpercentage_value"] = floor( $responses["d_chunks_hashed"] / $responses["d_size_chunks"] * 100 );
	} elseif ( $responses["d_complete"] != 1 ) {
		$responses["d_estimated_time"] = "";
		$responses["d_estimated_time_value"] = "";
		$responses["d_percentage"] = $responses["d_completed_bytes"] / $responses["d_size_bytes"];
		$responses["d_percentage_value"] = floor( $responses["d_completed_bytes"] / $responses["d_size_bytes"] * 100 );
	} else {
		$responses["d_estimated_time"] = "";
		$responses["d_estimated_time_value"] = "";
		$responses["d_percentage"] = 1;
		$responses["d_percentage_value"] = 100;
	}

	switch ( $responses["d_connection_current"] ) {
		case "seed" : $responses["d_connection_current_value"] = $message["d_connection_current_seed"]; break;
		case "initial_seed" : $responses["d_connection_current_value"] = $message["d_connection_current_initial_seed"]; break;
		default : $responses["d_connection_current_value"] = $message["d_connection_current_leech"]; break;
	}
	switch ( $responses["d_hashing"] ) {
		case 1 : $responses["d_hashing_value"] = $message["d_hashing_1"]; break;
		case 2 : $responses["d_hashing_value"] = $message["d_hashing_2"]; break;
		case 3 : $responses["d_hashing_value"] = $message["d_hashing_3"]; break;
		default : $responses["d_hashing_value"] = $message["d_hashing_0"]; break;
	}
	switch ( $responses["d_priority"] ) {
		case 1 : $responses["d_priority_value"] = $message["d_priority_1"]; break;
		case 2 : $responses["d_priority_value"] = $message["d_priority_2"]; break;
		case 3 : $responses["d_priority_value"] = $message["d_priority_3"]; break;
		default : $responses["d_priority_value"] = $message["d_priority_0"]; break;
	}

	$responses["d_ratio_value"] = sprintf( "%.3f", $responses["d_ratio"] / 1000 );

	ksort( $responses );

	return true;
}

// initializing xmlrpc methods
$xmlrpc_methods = array(
	"si"	=> $config["dht"] === "1" ? 
			array( "dht_statistics", "get_bind", "get_check_hash", "get_connection_leech", "get_connection_seed", "get_dht_port", "get_directory", "get_download_rate", "get_hash_interval", "get_hash_max_tries", "get_hash_read_ahead", "get_http_cacert", "get_http_capath", "get_http_proxy", "get_ip", "get_key_layout", "get_max_downloads_div", "get_max_downloads_global", "get_max_file_size", "get_max_memory_usage", "get_max_open_files", "get_max_open_http", "get_max_open_sockets", "get_max_peers", "get_max_peers_seed", "get_max_uploads", "get_max_uploads_div", "get_max_uploads_global", "get_memory_usage", "get_min_peers", "get_min_peers_seed", "get_name", "get_peer_exchange", "get_port_open", "get_port_random", "get_port_range", "get_preload_min_size", "get_preload_required_rate", "get_preload_type", "get_proxy_address", "get_receive_buffer_size", "get_safe_free_diskspace", "get_safe_sync", "get_scgi_dont_route", "get_send_buffer_size", "get_session", "get_session_lock", "get_session_on_completion", "get_split_file_size", "get_split_suffix", "get_stats_not_preloaded", "get_stats_preloaded", "get_timeout_safe_sync", "get_timeout_sync", "get_tracker_numwant", "get_upload_rate", "get_use_udp_trackers", "system.client_version", "system.get_cwd", "system.hostname", "system.library_version", "system.pid", "view_list" ) :
			array( "get_bind", "get_check_hash", "get_connection_leech", "get_connection_seed", "get_directory", "get_download_rate", "get_hash_interval", "get_hash_max_tries", "get_hash_read_ahead", "get_http_cacert", "get_http_capath", "get_http_proxy", "get_ip", "get_key_layout", "get_max_downloads_div", "get_max_downloads_global", "get_max_file_size", "get_max_memory_usage", "get_max_open_files", "get_max_open_http", "get_max_open_sockets", "get_max_peers", "get_max_peers_seed", "get_max_uploads", "get_max_uploads_div", "get_max_uploads_global", "get_memory_usage", "get_min_peers", "get_min_peers_seed", "get_name", "get_peer_exchange", "get_port_open", "get_port_random", "get_port_range", "get_preload_min_size", "get_preload_required_rate", "get_preload_type", "get_proxy_address", "get_receive_buffer_size", "get_safe_free_diskspace", "get_safe_sync", "get_scgi_dont_route", "get_send_buffer_size", "get_session", "get_session_lock", "get_session_on_completion", "get_split_file_size", "get_split_suffix", "get_stats_not_preloaded", "get_stats_preloaded", "get_timeout_safe_sync", "get_timeout_sync", "get_tracker_numwant", "get_upload_rate", "get_use_udp_trackers", "system.client_version", "system.get_cwd", "system.hostname", "system.library_version", "system.pid", "view_list" ),
	"d"	=> array( "d.get_base_filename=", "d.get_base_path=", "d.get_bytes_done=", "d.get_chunk_size=", "d.get_chunks_hashed=", "d.get_complete=", "d.get_completed_bytes=", "d.get_completed_chunks=", "d.get_connection_current=", "d.get_connection_leech=", "d.get_connection_seed=", "d.get_creation_date=", "d.get_custom1=", "d.get_custom2=", "d.get_custom3=", "d.get_custom4=", "d.get_custom5=", "d.get_directory=", "d.get_down_rate=", "d.get_down_total=", "d.get_free_diskspace=", "d.get_hash=", "d.get_hashing=", "d.get_ignore_commands=", "d.get_left_bytes=", "d.get_local_id=", "d.get_local_id_html=", "d.get_max_file_size=", "d.get_max_size_pex=", "d.get_message=", "d.get_name=", "d.get_peer_exchange=", "d.get_peers_accounted=", "d.get_peers_complete=", "d.get_peers_connected=", "d.get_peers_max=", "d.get_peers_min=", "d.get_peers_not_connected=", "d.get_priority=", "d.get_priority_str=", "d.get_ratio=", "d.get_size_bytes=", "d.get_size_chunks=", "d.get_size_files=", "d.get_size_pex=", "d.get_skip_rate=", "d.get_skip_total=", "d.get_state=", "d.get_state_changed=", "d.get_tied_to_file=", "d.get_tracker_focus=", "d.get_tracker_numwant=", "d.get_tracker_size=", "d.get_up_rate=", "d.get_up_total=", "d.get_uploads_max=", "d.is_active=", "d.is_hash_checked=", "d.is_hash_checking=", "d.is_multi_file=", "d.is_open=", "d.is_pex_active=", "d.is_private=" ),
	"f"	=> array( "f.get_completed_chunks=", "f.get_frozen_path=", "f.is_created=", "f.is_open=", "f.get_last_touched=", "f.get_match_depth_next=", "f.get_match_depth_prev=", "f.get_offset=", "f.get_path=", "f.get_path_components=", "f.get_path_depth=", "f.get_priority=", "f.get_range_first=", "f.get_range_second=", "f.get_size_bytes=", "f.get_size_chunks=" ),
	"p"	=> array( "p.get_address=", "p.get_client_version=", "p.get_completed_percent=", "p.get_down_rate=", "p.get_down_total=", "p.get_id=", "p.get_id_html=", "p.get_options_str=", "p.get_peer_rate=", "p.get_peer_total=", "p.get_port=", "p.get_up_rate=", "p.get_up_total=", "p.is_encrypted=", "p.is_incoming=", "p.is_obfuscated=", "p.is_snubbed=" ),
	"t"	=> array( "t.get_group=", "t.get_id=", "t.get_min_interval=", "t.get_normal_interval=", "t.get_scrape_complete=", "t.get_scrape_downloaded=", "t.get_scrape_incomplete=", "t.get_scrape_time_last=", "t.get_type=", "t.get_url=", "t.is_enabled=", "t.is_open=" ),
	"g"	=> array( "d.get_name", "d.get_directory", "f.get_path", "f.get_frozen_path", "f.get_size_bytes" ),
);


	// initializing rtorrent connection
	$xmlrpc = new xmlrpc_handler( $config['server_interface'], 10 );

	if ( $xmlrpc->getconntype() == "http" ) {
		$xmlrpc->setaccount( $config['server_user'], $config['server_pass'] );
	}

	
// initializing xml
$xml = new DOMDocument( "1.0", "utf-8" );
$xml->formatOutput = true;

$root = $xml->appendChild( $xml->createElement( "root" ) );

$root->appendChild( $xml->createElement( "mod", $mod ) );
$root->appendChild( $xml->createElement( "hash", $hash ) );
$root->appendChild( $xml->createElement( "page", $page ) );
$root->appendChild( $xml->createElement( "id", $id ) );
$root->appendChild( $xml->createElement( "burl", $config["base"] ) );
$root->appendChild( $xml->createElement( "url", $config["index"] ) );
$root->appendChild( $xml->createElement( "iurl", $config["input"] ) );
$root->appendChild( $xml->createElement( "curl", str_replace( "&", "&amp;", $_SERVER["REQUEST_URI"] ) ) );
$root->appendChild( $xml->createElement( "purl", isset( $_SESSION["rtwi_lastpage"] ) ? str_replace( "&", "&amp;", $_SESSION["rtwi_lastpage"] ) : "" ) );
$root->appendChild( $xml->createElement( "datetime", date( "Y. m. d. H:i:s", time() ) ) );
$root->appendChild( $xml->createElement( "language", $_SESSION["rtwi_language"] ) );


 // torrents
			// retrieving server info
			if ( !$xmlrpc->setmrequest( $xmlrpc_methods["si"] ) || !$xmlrpc->call() || !$xmlrpc->parse() ) {
			 echo "errored out1";
				print_r( $xmlrpc->geterrors() );
				exit;
				
			}
			$si_responses = $xmlrpc->mfetch( $xmlrpc_methods["si"] );

			// creating torrents node
			$tnode = $root->appendChild( $xml->createElement( "torrents" ) );
			// adding sort view select
			addviewtypes( $xml, $root, $tnode, $si_responses["view_list"], $viewtypes );
			// adding auto-refresh select
			if ( $config["refresh"] ) {
				addrefreshrates( $xml, $root, $tnode, $refreshrates );
			}
			// adding language select
			if ( $config["language"] ) {
				addlanguages( $xml, $root, $tnode, $languages );
			}
			// retrieving download info
			if ( !$xmlrpc->setrequest( "d.multicall", array_merge( array( "default" ), $xmlrpc_methods["d"] ) ) || !$xmlrpc->call() || !$xmlrpc->parse() ) {
			echo "errored out2";
				print_r( $xmlrpc->geterrors() );
				exit;
			}
			$response = $xmlrpc->fetch();
			
			//print_r($response);

			// valirables for counting total up/down rate
			$bytes_down = 0;
			$bytes_up = 0;
			// process the download info
			for ( $i = 0; $i < count( $response ); $i++ ) {
				for ( $r = 0; $r < count( $xmlrpc_methods["d"] ); $r++ ) {
					$methodval = preg_replace( "/(.)\.(get|is)_(.*)=/", "$1_$3", $xmlrpc_methods["d"][$r] );
					$d_responses[$i][$methodval] = $response[$i][$r];
				}
				// oincreasing total up/down rate with the current download's rates
				$bytes_down += $d_responses[$i]["d_down_rate"];
				$bytes_up += $d_responses[$i]["d_up_rate"];

				// formatting and inserting values to xml
				prepare_downloadlist_responses( $d_responses[$i], $message, $home_path, $si_responses["get_cwd"] );
				$ttnode = $tnode->appendChild( $xml->createElement( "torrent" ) );
				xmlrpc_multiappend( $xml, $ttnode, $d_responses[$i] );
			}

			// setting total up/down rate
			$si_responses["bytes_down"] = $bytes_down;
			$si_responses["bytes_up"] = $bytes_up;

			// adding values to xml
			prepare_serverinfo_responses( $si_responses, $message, $config );

			// addng some more values to xml (total up/down rate, limits)
			$node = $tnode->appendChild( $xml->createElement( "bytes_down", $si_responses["bytes_down"] ) );
			$node->setAttribute( "suffix", $si_responses["bytes_down_suffix"] );
			$node->setAttribute( "value", $si_responses["bytes_down_value"] );
			$node = $tnode->appendChild( $xml->createElement( "bytes_up", $si_responses["bytes_up"] ) );
			$node->setAttribute( "suffix", $si_responses["bytes_up_suffix"] );
			$node->setAttribute( "value", $si_responses["bytes_up_value"] );
			$node = $tnode->appendChild( $xml->createElement( "download_rate", $si_responses["download_rate"] ) );
			$node->setAttribute( "suffix", $si_responses["download_rate_suffix"] );
			$node->setAttribute( "value", $si_responses["download_rate_value"] );
			$node = $tnode->appendChild( $xml->createElement( "upload_rate", $si_responses["upload_rate"] ) );
			$node->setAttribute( "suffix", $si_responses["upload_rate_suffix"] );
			$node->setAttribute( "value", $si_responses["upload_rate_value"] );

			$layout_file = "layout.main.index.xsl";
			
			//echo $xml->saveXML();

			$torrenta = $xml->getElementsByTagName('torrents')
?>
<li>
<em style="color:#009900">
Total UP 
<?php echo $torrenta->item(0)->getElementsByTagName('bytes_up')->item(0)->getAttribute('value'); ?><?php echo $torrenta->item(0)->getElementsByTagName('bytes_up')->item(0)->getAttribute('suffix'); ?>&uarr;
 - 
 Total DOWN
 <?php echo $torrenta->item(0)->getElementsByTagName('bytes_down')->item(0)->getAttribute('value'); ?><?php echo $torrenta->item(0)->getElementsByTagName('bytes_down')->item(0)->getAttribute('suffix'); ?>&darr;
</em>
 
</li>
<?php			
			
			
 foreach ($xml->getElementsByTagName('torrent') as $torrent) {
 //echo $torrent->saveXML();

 ?>
<li>
<small><?php echo $torrent->getElementsByTagName('d_percentage')->item(0)->getAttribute('value'); ?>%</small>

 <?php 
 echo wordwrap($torrent->getElementsByTagName('d_name')->item(0)->firstChild->nodeValue, 30, "<br />\n", true);
 ?>

<br>
<em>
UP <?php echo $torrent->getElementsByTagName('d_up_rate')->item(0)->getAttribute('value'); ?><?php echo $torrent->getElementsByTagName('d_up_rate')->item(0)->getAttribute('suffix'); ?> 
 - DOWN <?php echo $torrent->getElementsByTagName('d_down_rate')->item(0)->getAttribute('value'); ?><?php echo $torrent->getElementsByTagName('d_down_rate')->item(0)->getAttribute('suffix'); ?> 
 - Est. <?php echo $torrent->getElementsByTagName('d_estimated_time')->item(0)->getAttribute('value'); ?> 
</em>



</li>



<?php 
 }//end foreach torrent
?>

