<?php
/*
Plugin Name: Myna for WordPress
Plugin URI: http://mynaweb.com
Description: Myna Integration for WordPress
<<<<<<< HEAD
Version: 0.2
=======
Version: 0.2.1
>>>>>>> Fix experiments bug; bump version
Author: Cliff Seal (Pardot)
Author URI: http://pardot.com
Author Email: cliff.seal@pardot.com	
License:

  Copyright 2012 Pardot (cliff.seal@pardot.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

/* Activation */

// Add placeholder options
register_activation_hook( __FILE__, 'mynawp_activate' );
function mynawp_activate() {
	update_option('mynawp_options', '');
	$array = array('uuids' => '', 'names' => '');
	update_option('mynawp_uuids', $array);
}

/* Options Menu Addition */

add_action('admin_menu', 'mynawp_admin_add_page');

function mynawp_admin_add_page() {
	add_options_page('Myna for WordPress', 'Myna', 'manage_options', 'mynawp', 'mynawp_options_page');
}

/* Writing Functions */

// Detect Actions via GETs
if ( isset($_GET['newvar']) && ( $_GET['newvar'] != '' ) ) {
	mynawp_addvariant();
} elseif ( isset($_GET['delvar'] ) && ( $_GET['delvar'] != '' ) ) {
	mynawp_delvariant();
} elseif ( isset($_GET['delexp'] ) && ( $_GET['delexp'] != '' ) ) {
	$uuid = $_GET['updexp'];
	mynawp_delexp($uuid);
} elseif ( isset($_GET['updexp'] ) && ( $_GET['updexp'] != '' ) ) {
	$uuid = trim($_GET['updexp'], ',');
	mynawp_update_uuid($uuid);
}

// Add a Variant
function mynawp_addvariant() {
	$newvar = $_GET['newvar'];
	$uuid = $_GET['uuid'];
	$options = get_option('mynawp_options');
	$username = mynawp_decrypt($options['email_string'], 'mynawp_key');
	$password = mynawp_decrypt($options['pwd_string'], 'mynawp_key');
	$args = array(
		'headers' => array(
			'Accept' => 'application/json',
			'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password )
		),
		'method' => 'POST',
		'body' => json_encode(array(
			'variant' => $newvar
		))
	);
	$response = wp_remote_request('https://api.mynaweb.com/v1/experiment/' . $uuid . '/new-variant', $args);
	if ( is_wp_error($response) ) {
  		echo 'An error occurred.';
	}
}

// Delete a Variant
function mynawp_delvariant() {
	$delvar = $_GET['delvar'];
	$uuid = $_GET['uuid'];
	$options = get_option('mynawp_options');
	$username = mynawp_decrypt($options['email_string'], 'mynawp_key');
	$password = mynawp_decrypt($options['pwd_string'], 'mynawp_key');
	$args = array(
		'headers' => array(
			'Accept' => 'application/json',
			'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password )
		),
		'method' => 'POST',
		'body' => json_encode(array(
			'variant' => $delvar
		))
	);
	$response = wp_remote_request('https://api.mynaweb.com/v1/experiment/' . $uuid . '/delete-variant', $args);
	if ( is_wp_error($response) ) {
  		echo 'An error occurred.';
	}
}

// Add an Experiment
function mynawp_addexp() {
	$newexp = $_GET['newexp'];
	$options = get_option('mynawp_options');
	$username = mynawp_decrypt($options['email_string'], 'mynawp_key');
	$password = mynawp_decrypt($options['pwd_string'], 'mynawp_key');
	$args = array(
		'headers' => array(
			'Accept' => 'application/json',
			'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password )
		),
		'method' => 'POST',
		'body' => json_encode(array(
			'experiment' => $newexp
		))
	);
	$option = get_option('mynawp_uuids');
	$pos = strpos($option['names'],$newexp);
	if ( $pos === false ) {
		$response = wp_remote_retrieve_body(wp_remote_request('https://api.mynaweb.com/v1/experiment/new', $args));
		$decoded = json_decode($response);
		if ( isset($decoded->{'uuid'}) ) {
			$addtoview = $decoded->{'uuid'};
			mynawp_add_uuid($addtoview);
		} else {
			$addtoview = '';
		}
		if ( is_wp_error($response) ) {
	  		echo 'An error occurred.';
		}
		return $addtoview;
	} else {
		return false;
	}
}

// Delete an Experiment
function mynawp_delexp( $uuid = null ) {
	$uuid = isset($uuid) ? $uuid : $_GET['uuid'];
	$options = get_option('mynawp_options');
	$username = mynawp_decrypt($options['email_string'], 'mynawp_key');
	$password = mynawp_decrypt($options['pwd_string'], 'mynawp_key');
	$args = array(
		'headers' => array(
			'Accept' => 'application/json',
			'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password )
		),
		'method' => 'POST',
	);
	$response = wp_remote_retrieve_body(wp_remote_request('https://api.mynaweb.com/v1/experiment/' . $uuid . '/info', $args));
	$storename = json_decode($response);
	$storename = $storename->{'name'};
	$response = wp_remote_request('https://api.mynaweb.com/v1/experiment/' . $uuid . '/delete', $args);
	if ( is_wp_error($response) ) {
  		echo 'An error occurred.';
	}
	mynawp_remove_uuid($uuid,$storename);
}

/* Options Page Functions */

// Add Scripts
add_action( 'admin_enqueue_scripts', 'mynawp_admin_add_script' );

function mynawp_admin_add_script() {
	wp_enqueue_script('jquery');
	wp_register_script('mynawp', plugins_url( 'mynawp-admin.js' , __FILE__ ), array('jquery'), false, true);
	wp_enqueue_script('mynawp');
}

// Add Page
function mynawp_options_page() { ?>
	<div style="width: 90%">
		<h1>Myna for WordPress</h1>
		<p>Add and edit new experiments and variants below. You can also do this in your <a href="https://mynaweb.com/dashboard" target="_blank">Myna Dashboard</a>. If you need help, read the <a href="https://mynaweb.com/help/overview" target="_blank">Myna Documentation</a>.
		<form action="options.php" method="post" id="optionspost">
			<?php settings_fields('mynawp_options'); ?>
			<?php do_settings_sections('mynaplugin'); ?>
			<br />
			<input class="button-primary" name="Submit" type="submit" value="<?php esc_attr_e('Save Login Settings'); ?>" />
		</form>
	</div>

<?php }

// Add Settings
add_action('admin_init', 'mynawp_admin_init');

function mynawp_admin_init(){
	register_setting('mynawp_options', 'mynawp_options', 'mynawp_encrypt_options');
	add_settings_section('mynawp_main', 'Myna Experiments', 'mynawp_section_text', 'mynaplugin');
	add_settings_field('mynawp_email_text_string', 'Email', 'mynawp_email_string', 'mynaplugin', 'mynawp_main');
	add_settings_field('mynawp_pwd_text_string', 'Password', 'mynawp_pwd_string', 'mynaplugin', 'mynawp_main');
}

function mynawp_section_text() {
	$options = get_option('mynawp_options');
	$uuids = get_option('mynawp_uuids');
	$username = mynawp_decrypt($options['email_string'], 'mynawp_key');
	$password = mynawp_decrypt($options['pwd_string'], 'mynawp_key');
	$args = array(
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password )
		)
	);
	$response = wp_remote_retrieve_body(wp_remote_request('https://api.mynaweb.com/v1/user/info', $args));
	$decoded = json_decode($response);
	
	$cachedresponse = get_transient( 'myna_info_check' );
	
	if ( $cachedresponse !== false && $decoded === $cachedresponse ) {
	
		$decoded = $cachedresponse;
		
	} else {
	
		set_transient( 'myna_info_check', $decoded );
		
	}
	
		if ( $decoded != '' ) {
		
			$output = '';
		
<<<<<<< HEAD
			for ( $i=0; $i<=count($decoded); $i++ ) {
=======
			for ( $i=0; $i<count($decoded->experiments); $i++ ) {
>>>>>>> Fix experiments bug; bump version
				$output .= '<div id="' . $decoded->experiments[$i]->{'uuid'} . '"><h4>' . $decoded->experiments[$i]->{'name'} . ': <span id="thisuuid">' . $decoded->experiments[$i]->{'uuid'} . '</span></h4>';
				if ( $decoded->experiments[$i]->{'variants'} ) {
					$output .= '<table class="widefat"><thead><th>Name</th><th>Views</th><th>Total Reward</th><th>Lower Confidence Bound</th><th>Upper Confidence Bound</th><th></th></thead><tbody>';
					foreach ( $decoded->experiments[$i]->{'variants'} as $variant ) {
						$output .= '<tr>';
						$output .= '<td>'. $variant->{'name'} . '</td><td>'. $variant->{'views'} . '</td><td>'. $variant->{'totalReward'} . '</td><td>'. $variant->{'lowerConfidenceBound'} . '</td><td>'. $variant->{'upperConfidenceBound'} . '</td><td><a href="' . admin_url( 'options-general.php?page=mynawp' ) . '&uuid=' . $decoded->experiments[$i]->{'uuid'} . '&delvar=' . $variant->{'name'} .'" class="delete_var">Delete</a></td></tr>';
					}
					$output .= '</tbody></table>';
				}
				$output .= "<br /><input class='mynawp_new_variant " . $decoded->experiments[$i]->{'uuid'} . "' name='new_variant' size='53' type='text' /><a href='" . admin_url( 'options-general.php?page=mynawp' ) . "&uuid=" . $decoded->experiments[$i]->{'uuid'} . "&newvar=' class='newvarurl button-primary'>Add This Variant</a><a href='" . admin_url( 'options-general.php?page=mynawp' ) . "&uuid=" . $decoded->experiments[$i]->{'uuid'} . "&delexp=1' id='delexpurl' class='button-primary alignright deleteexp' rel=" . $decoded->experiments[$i]->{'uuid'} . ">Delete This Experiment</a></div>";
			}
			
			echo $output;
			
			set_transient( 'myna_fallback_admin', $output);
			set_transient( 'myna_info_check', $decoded );
		
		} else {
			
			echo get_transient( 'myna_fallback_admin' );
			
		}	
	
	echo "<h3>Create a New Experiment</h3><input id='mynawp_new_experiment' name='new_experiment' size='53' type='text' /><a href='" . admin_url( 'options-general.php?page=mynawp' ) . "&newexp=' class='button-primary' id='newexpurl'>Create This Experiment</a>";
	echo '<h2>Login Settings</h2>';
	
	
}

// Save Options
function mynawp_add_uuid($uuid) {
	$newexp = $_GET['newexp'];
	$option = get_option('mynawp_uuids');
	$names = explode(',', $option['names']);
	if ( ($option != '') && (!in_array($newexp, $names)) ) {
		$uuids = explode(',', $option['uuid']);
		if ( !in_array($uuid, $uuids) ) {
			array_push($uuids, $uuid);
			array_push($names, $newexp);
		}
		$uuids = implode(',', $uuids);
		$names = implode(',', $names);
	} else {
		$uuids = $uuid;
		$names = implode(',', $names);
	}
	$uuids = trim($uuids, ',');
	$names = trim($names, ',');
	$option['uuid'] = $uuids;
	$option['names'] = $names;
	update_option('mynawp_uuids', $option);
}

function mynawp_remove_uuid($uuid,$name) {
	$option = get_option('mynawp_uuids');
	$newuuid = str_replace($uuid, '', $option['uuid']);
	if ( $newuuid[0] == ',' ) {
		$newuuid = substr($newuuid, 1);
	} 
	$newoption['uuid'] = trim($newuuid, ',');
	$newname = str_replace($name, '', $option['names']);
	if ( $newname[0] == ',' ) {
		$newname = substr($newname, 1);
	} 
	$newoption['names'] = trim($newname, ',');
	update_option('mynawp_uuids', $newoption);
}

function mynawp_update_uuid($uuid) {
	$uuids = get_option('mynawp_uuids');
	$uuids['names'] = $uuids['names'];
	$uuids['uuid'] = $uuid;
	update_option('mynawp_uuids', $uuids);
}

function mynawp_uuid_string() {
	$options = get_option('mynawp_uuids');
	return "<input id='mynawp_uuid_string' name='mynawp_options[email_string]' size='53' type='text' value='{$options['uuid']}' />";
}

function mynawp_email_string() {
	$options = get_option('mynawp_options');
	if ( $options ) {
		$option = mynawp_decrypt($options['email_string'], 'mynawp_key');
	} else {
		$option = '';
	}
	echo "<input id='mynawp_email_string' name='mynawp_options[email_string]' size='53' type='text' value='{$option}' />";
}

function mynawp_pwd_string() {
	$options = get_option('mynawp_options');
	if ( $options ) {
		$option = mynawp_decrypt($options['pwd_string'], 'mynawp_key');
	} else {
		$option = '';
	}
	echo "<input id='mynawp_pwd_string' name='mynawp_options[pwd_string]' size='53' type='password' value='{$option}' />";
}

/* Output Functions for Theme */

// Add Scripts
add_action( 'wp_enqueue_scripts', 'mynawp_add_script' );

function mynawp_add_script() {
	wp_enqueue_script('jquery');
	wp_register_script('mynawp', plugins_url( 'mynawp.js' , __FILE__ ), array('jquery'), false, true);
	
	// Check CDN Availability
	$test_url = @fopen('http://cdn.mynaweb.com/clients/myna-1.latest.min.js','r');
	if ( $test_url !== false ) {
		// Use the CDN
		wp_register_script('myna', 'http://cdn.mynaweb.com/clients/myna-1.latest.min.js', array('jquery'), false, true);
	} else {
		// Fallback to Local
		wp_register_script('myna', plugins_url( 'myna-1.1.0.min.js' , __FILE__ ), array('jquery'), false, true);
	}
	
	wp_enqueue_script('myna');
	wp_enqueue_script('mynawp');
}

// Fetch and return the Myna Suggestion for use in PHP (i.e. get_myna_var('2382dbab-3ed5-406b-be36-08032fab8042'); echo $myna->choice; ) - http://mynaweb.com/docs/api.html#suggest
function get_myna_var($uuid) {
	 		
	$response = wp_remote_retrieve_body(wp_remote_get('http://api.mynaweb.com/v1/experiment/' . $uuid . '/suggest'));
	global $myna;
	$myna = json_decode($response);

	if ( is_wp_error($response) ) {
		return '';
	}
  
}

// Create and Echo a Myna Link for Templating (i.e. myna_link('72a3dd4f-73f2-4a18-a99f-a14a6b3e8e0d','http://google.com','Default Text',true); produces <a href="http://google.com" rel="72a3dd4f-73f2-4a18-a99f-a14a6b3e8e0d" target="_blank">Myna Suggestion</a>)- http://mynaweb.com/docs/api.html#suggest
function myna_link($uuid,$link,$text='Click Here',$newwin=false) {
	 		
	$response = wp_remote_retrieve_body(wp_remote_get('http://api.mynaweb.com/v1/experiment/' . $uuid . '/suggest'));
	$myna = json_decode($response);

	if ( is_wp_error($response) ) {
		return '';
	} else {
		$output = '<a href="' . $link . '" ';
		$output .= 'rel="' . $uuid . '" ';
		if ( $newwin == true ) {
			$output .= ' target="_blank"';
		}
		$output .= ' class="mynaSuggest">' . $text . '</a>';
		echo $output;
	}
  
}

// Shortcode that outputs the myna_link function (i.e. [myna uuid="72a3dd4f-73f2-4a18-a99f-a14a6b3e8e0d" link="http://google.com" newwin=true nofollow=true]Testing This[/myna] produces <a href="http://google.com" rel="72a3dd4f-73f2-4a18-a99f-a14a6b3e8e0d nofollow" target="_blank" class="mynaSuggest">Myna Suggestion</a>)- http://mynaweb.com/docs/api.html#suggest
function myna_shortcode($atts, $content = 'Click Here'){

	extract( shortcode_atts( array(
		'uuid' => '',
		'link' => '',
		'newwin' => false
	), $atts ) );

	$response = wp_remote_retrieve_body(wp_remote_get('http://api.mynaweb.com/v1/experiment/' . $uuid . '/suggest'));
	$myna = json_decode($response);

	if ( is_wp_error($response) ) {
		return '';
	} else {
		$output = '<a href="' . esc_attr($link) . '" ';
		$output .= 'rel="' . esc_attr($uuid) . '" ';
		if ( $newwin == true ) {
			$output .= ' target="_blank"';
		}
		$output .= ' class="mynaSuggest">' . $content . '</a>';
		return $output;
	}
}
add_shortcode( 'myna', 'myna_shortcode' );

/* Encryption */

// Hook for Options Submission
function mynawp_encrypt_options($input) {
	$options = get_option('mynawp_options');

	if ( !$options ) {
		$input['email_string'] = mynawp_encrypt($input['email_string'], 'mynawp_key');
	} else {
		$email = $options['email_string'];
		if ( mynawp_encrypt($input['email_string'], 'mynawp_key') != $email ) {
			$input['email_string'] = mynawp_encrypt($input['email_string'], 'mynawp_key');
		}
	}
		
	if ( !$options ) {
		$input['pwd_string'] = mynawp_encrypt($input['pwd_string'], 'mynawp_key');
	} else {
		$pwd = $options['pwd_string'];
		if ( mynawp_encrypt($input['pwd_string'], 'mynawp_key') != $pwd ) {
			$input['pwd_string'] = mynawp_encrypt($input['pwd_string'], 'mynawp_key');
		}
	}
		
	return $input;
}

// Make It Difficult
function mynawp_encrypt($input_string, $key='mynawp_key'){
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$h_key = hash('sha256', $key, TRUE);
	return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $h_key, $input_string, MCRYPT_MODE_ECB, $iv));
}

// Make It Readable
function mynawp_decrypt($encrypted_input_string, $key='mynawp_key'){
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $h_key = hash('sha256', $key, TRUE);
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $h_key, base64_decode($encrypted_input_string), MCRYPT_MODE_ECB, $iv));
}

/* Deactivation */

// Be Kind (Clean Up Options)
register_deactivation_hook( __FILE__, 'mynawp_deactivate' );
function mynawp_deactivate() {
	delete_option('mynawp_options');
	delete_option('mynawp_uuids');
}