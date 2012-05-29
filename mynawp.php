<?php
/*
Plugin Name: Myna for WordPress
Plugin URI: http://mynaweb.com
Description: Myna Integration for WordPress
Version: 0.1
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

//Add placeholder options
register_activation_hook( __FILE__, 'mynawp_activate' );
function mynawp_activate() {
	update_option('mynawp_options', '');
	update_option('mynawp_uuids', '');
}

/* Options Menu Addition */

add_action('admin_menu', 'mynawp_admin_add_page');

function mynawp_admin_add_page() {
	add_options_page('Myna for WordPress', 'Myna', 'manage_options', 'mynawp', 'mynawp_options_page');
}

/* Writing Functions */

if ( isset($_GET['newvar']) && ( $_GET['newvar'] != '' ) ) {
	mynawp_addvariant();
} elseif ( isset($_GET['delvar']) && ( $_GET['delvar'] != '' ) ) {
	mynawp_delvariant();
} elseif ( isset($_GET['delexp']) && ( $_GET['delexp'] != '' ) ) {
	mynawp_delexp();
}

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
	if ( is_wp_error( $response ) ) {
  		echo 'An error occurred.';
	}
}

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
	if ( is_wp_error( $response ) ) {
  		echo 'An error occurred.';
	}
}

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
	$response = wp_remote_retrieve_body(wp_remote_request('https://api.mynaweb.com/v1/experiment/new', $args));
	$decoded = json_decode($response);
	if ( isset($decoded->{'uuid'}) ) {
		$addtoview = $decoded->{'uuid'};
		mynawp_add_uuid($addtoview);
	} else {
		$addtoview = '';
	}
	if ( is_wp_error( $response ) ) {
  		echo 'An error occurred.';
	}
	return $addtoview;
}

function mynawp_delexp() {
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
	);
	$response = wp_remote_request('https://api.mynaweb.com/v1/experiment/' . $uuid . '/delete', $args);
	if ( is_wp_error( $response ) ) {
  		echo 'An error occurred.';
	}
	mynawp_remove_uuid($uuid);
}

/* Options Page Functions */

// Add Scripts
add_action( 'admin_enqueue_scripts', 'mynawp_admin_add_script' );

function mynawp_admin_add_script() {
	wp_enqueue_script('jquery');
	wp_register_script('mynawp', plugins_url( 'mynawp.js' , __FILE__ ), array('jquery'), false, true);
	wp_enqueue_script('mynawp');
}

// Add Page
function mynawp_options_page() { ?>
	<div style="width: 90%">
		<h1>Myna for WordPress</h1>
		<p>Add and edit new experiments and variants below. You can also do this in your <a href="https://app.mynaweb.com" target="_blank">Myna Dashboard</a>. If you need help, read the <a href="http://mynaweb.com/docs/getting-started.html" target="_blank">Myna Documentation</a>.
		<form action="options.php" method="post" id="optionspost">
			<?php settings_fields('mynawp_options'); ?>
			<?php do_settings_sections('mynaplugin'); ?>
			<br />
			<input class="button-primary" name="Submit" type="submit" value="<?php esc_attr_e('Save Options'); ?>" />
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
	if ( isset($_GET['newexp']) ) {
		$uuidstring = $uuids . ',' . mynawp_addexp();
	} else {
		$uuidstring = $uuids;
	}	
	$uuid = explode(",", $uuidstring);
	$count = count($uuid);
	for ( $i = 0; $i <= $count; $i++ ) {
		$response = wp_remote_retrieve_body(wp_remote_request('https://api.mynaweb.com/v1/experiment/' . $uuid[$i] . '/info', $args));
		$decoded = json_decode($response);
		if ( $decoded->{'uuid'} ) {
			$output = '<h4>' . $decoded->{'name'} . ': <span id="thisuuid">' . $decoded->{'uuid'} . '</span></h4>';
			if ( $decoded->{'variants'} ) {
				$output .= '<table class="widefat"><thead><th>Name</th><th>Views</th><th>Total Reward</th><th>Confidence</th><th></th></thead><tbody>';
				foreach ( $decoded->{'variants'} as $variant ) {
    				$output .= '<tr>';
    				$output .= '<td>'. $variant->{'name'} . '</td><td>'. $variant->{'views'} . '</td><td>'. $variant->{'totalReward'} . '</td><td>'. $variant->{'confidenceBound'} . '</td><td><a href="' . admin_url( 'options-general.php?page=mynawp' ) . '&uuid=' . $decoded->{'uuid'} . '&delvar=' . $variant->{'name'} .'" class="delete_var">Delete</a></td></tr>';
				}
				$output .= '<tfoot><th>Name</th><th>Views</th><th>Total Reward</th><th>Confidence</th><th></th></tfoot></tbody></table>';
			}
			$output .= "<br /><input id='mynawp_new_variant' name='new_variant' size='53' type='text' /><a href='" . admin_url( 'options-general.php?page=mynawp' ) . "&uuid=" . $decoded->{'uuid'} . "&newvar=' id='newvarurl' class='button-primary'>Add This Variant</a><a href='" . admin_url( 'options-general.php?page=mynawp' ) . "&uuid=" . $decoded->{'uuid'} . "&delexp=1' id='delexpurl' class='button-primary alignright deleteexp' rel=" . $decoded->{'uuid'} . ">Delete This Experiment</a>";
			echo $output;
		}
	}
	echo "<h3>Create a New Experiment</h3><input id='mynawp_new_experiment' name='new_experiment' size='53' type='text' /><a href='" . admin_url( 'options-general.php?page=mynawp' ) . "&newexp=' class='button-primary' id='newexpurl'>Create This Experiment</a>";
	echo '<h2>Login Settings</h2>';
}

function mynawp_add_uuid($uuid) {
	$option = get_option('mynawp_uuids');
	if ( $option != '' ) {
		$uuids = explode(',', $option);
		if ( !in_array($uuid) ) {
			array_push($uuids, $uuid);
		}	
		$uuids = implode(',', $uuids);
	} else {
		$uuids = $uuid;
	}	
	update_option('mynawp_uuids', $uuids);
}

function mynawp_remove_uuid($uuid) {
	$option = get_option('mynawp_uuids');
	$newoption = str_replace($uuid, '', $option);
	update_option('mynawp_uuids', $newoption);
}

function mynawp_uuid_string() {
	$options = get_option('mynawp_uuids');
	echo "<input id='mynawp_email_string' name='mynawp_options[email_string]' size='53' type='text' value='{$options}' />";
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

function getMynaVar() {
	 
	$options = get_option('mynawp_options');
	//$uuid = $options['uuid_string'];
		
	$response = wp_remote_retrieve_body(wp_remote_get('http://api.mynaweb.com/v1/experiment/' . $uuid . '/suggest'));
	$decoded = json_decode($response);

	if ( is_wp_error( $response ) ) {
		echo 'An error occurred.';
	} else {
		if ( $decoded->{'choice'} == 'cta1' ) {
			echo $copyone;
		}
	}
  
}

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

//Be kind
//Add placeholder options
register_deactivation_hook( __FILE__, 'mynawp_deactivate' );
function mynawp_deactivate() {
	delete_option('mynawp_options');
}