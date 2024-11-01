<?php
defined( 'ABSPATH' ) or exit;
/**
 * Set encryption method
 */
define( 'EMMO_ENCRYPT_METHOD', 'AES-256-CBC' );

add_filter( 'admin_init', 'emmo_register_fields' );

/**
 * Registering fields by settings api in general tab
 *
 * @return void
 */
function emmo_register_fields() {

	register_setting( 'general', 'emmo_enable_smtp', array(
		'type'              => 'boolean',
		'sanitize_callback' => 'emmo_sanitize_checkbox',
		'default'           => null,
	) );
	add_settings_field( 'emmo_enable_smtp',
		'<label for="emmo_enable_smtp">' . __( 'Enable SMTP email sending', 'email-mod' ) . '</label>',
		'emmo_print_enable_smtp',
		'general', 'email-mod' );

	register_setting( 'general', 'emmo_sendername', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => null,
	) );
	add_settings_field( 'emmo_sendername',
		'<label for="emmo_sendername">' . __( 'Sender name', 'email-mod' ) . '</label>',
		'emmo_print_sendername', 'general', 'email-mod' );

	register_setting( 'general', 'emmo_senderemail', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_email',
		'default'           => null,
	) );
	add_settings_field( 'emmo_senderemail',
		'<label for="emmo_senderemail">' . __( 'Sender email', 'email-mod' ) . '</label>',
		'emmo_print_senderemail', 'general', 'email-mod' );

	register_setting( 'general', 'emmo_encryption', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => 'none',
	) );
	add_settings_field( 'emmo_encryption',
		'<label for="emmo_encryption">' . __( 'Encryption', 'email-mod' ) . '</label>',
		'emmo_print_encryption', 'general', 'email-mod' );

	register_setting( 'general', 'emmo_host', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => null,
	) );
	add_settings_field( 'emmo_host', '<label for="emmo_host">' . __( 'Host', 'email-mod' ) . '</label>',
		'emmo_print_host', 'general', 'email-mod' );

	register_setting( 'general', 'emmo_port', array(
		'type'              => 'integer',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => null,
	) );
	add_settings_field( 'emmo_port', '<label for="emmo_port">' . __( 'Port', 'email-mod' ) . '</label>',
		'emmo_print_port', 'general', 'email-mod' );

	register_setting( 'general', 'emmo_enable_smtp_auth', array(
		'type'              => 'boolean',
		'sanitize_callback' => 'emmo_sanitize_checkbox',
		'default'           => null,
	) );
	add_settings_field( 'emmo_enable_smtp_auth',
		'<label for="emmo_enable_smtp">' . __( 'Enable SMTP authentification', 'email-mod' ) . '</label>',
		'emmo_print_enable_smtp_auth',
		'general', 'email-mod' );

	register_setting( 'general', 'emmo_username', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => null,
	) );
	add_settings_field( 'emmo_username', '<label for="emmo_username">' . __( 'Username', 'email-mod' ) . '</label>',
		'emmo_print_username', 'general', 'email-mod' );

	register_setting( 'general', 'emmo_password', array(
		'type'              => 'string',
		'sanitize_callback' => 'emmo_sanitize_smtp_password',
		'default'           => null,
	) );
	add_settings_field( 'emmo_password', '<label for="emmo_password">' . __( 'Password', 'email-mod' ) . '</label>',
		'emmo_print_password', 'general', 'email-mod' );

	add_settings_section( 'email-mod', __( 'Advanced Email Settings', 'email-mod' ), null, 'general' );
}

/**
 * Sanitize Callback Function for Checkbox
 *
 * @param $input
 *
 * @return bool
 */
function emmo_sanitize_checkbox( $input ) {
	return ! empty( $input );
}

/**
 * Sanitize Callback Function for SMTP-Password
 *
 * @param $input
 *
 * @return false|mixed|string|null
 */
function emmo_sanitize_smtp_password( $input ) {
	if ( empty( $input ) ) {
		return '';
	} elseif ( '********' === $input ) {
		return get_option( 'emmo_password' );
	}

	return emmo_encrypt_password_for_db( $input );
}

/**
 * Print checkbox for enabling smtp sending (yes/no) in admin
 *
 * @return void
 */
function emmo_print_enable_smtp() {
	?>
    <input type="checkbox" id="emmo_enable_smtp" name="emmo_enable_smtp"
           value="1" <?php checked( get_option( 'emmo_enable_smtp' ) ); ?> />
	<?php
}

/**
 * Print sender name field in admin
 *
 * @return void
 */
function emmo_print_sendername() {
	?>
    <input type="text" id="emmo_sendername" name="emmo_sendername"
           value="<?php echo esc_attr( get_option( 'emmo_sendername' ) ); ?>"/>
	<?php
}

/**
 * Print sender email field in admin
 *
 * @return void
 */
function emmo_print_senderemail() {
	?>
    <input type="email" id="emmo_senderemail" name="emmo_senderemail"
           value="<?php echo esc_attr( get_option( 'emmo_senderemail' ) ); ?>"/>
	<?php
}

/**
 * Print checkbox for smtp-auth (yes/no) in admin
 *
 * @return void
 */
function emmo_print_enable_smtp_auth() {
	?>
    <input type="checkbox" id="emmo_enable_smtp_auth" name="emmo_enable_smtp_auth"
           value="1" <?php checked( get_option( 'emmo_enable_smtp_auth' ) ); ?> />
	<?php
}

/**
 * Print username field in admin
 *
 * @return void
 */
function emmo_print_username() {
	?>
    <input type="text" id="emmo_username" name="emmo_username"
           value="<?php echo esc_attr( get_option( 'emmo_username' ) ); ?>"/>
	<?php
}

/**
 * Print user password field in admin
 *
 * @return void
 */
function emmo_print_password() {
	if ( empty( get_option( 'emmo_password', '' ) ) ) {
		$value = '';
	} else {
		$value = '********';
	}
	?>
    <input type="password" id="emmo_password" name="emmo_password" value="<?php echo esc_attr($value); ?>"/>
	<?php
}

/**
 * Print encryption select in admin
 *
 * @return void
 */
function emmo_print_encryption() {
	?>
    <select id="emmo_encryption" name="emmo_encryption">
        <option value="none" <?php selected( get_option( 'emmo_encryption' ), "none" ); ?>><?php _e( 'None',
				'email-mod' ); ?></option>
        <option value="ssl" <?php selected( get_option( 'emmo_encryption' ), "ssl" ); ?>><?php _e( 'SSL',
				'email-mod' ); ?></option>
        <option value="tls" <?php selected( get_option( 'emmo_encryption' ), "tls" ); ?>><?php _e( 'TLS',
				'email-mod' ); ?></option>
    </select>
	<?php
}

/**
 * Print host field in admin
 *
 * @return void
 */
function emmo_print_host() {
	?>
    <input type="text" id="emmo_host" name="emmo_host" value="<?php echo esc_attr( get_option( 'emmo_host' ) ); ?>"/>
	<?php
}

/**
 * Print port field in admin
 *
 * @return void
 */
function emmo_print_port() {
	?>
    <input type="number" id="emmo_port" name="emmo_port" class="small-text"
           value="<?php echo esc_attr( get_option( 'emmo_port' ) ); ?>"/>
	<?php
}

/**
 * Encrypt smtp password for saving in db
 *
 * @param $string
 *
 * @return string
 */
function emmo_encrypt_password_for_db( $string ) {
	$key    = hash( 'sha256', AUTH_KEY );
	$ivalue = substr( hash( 'sha256', SECURE_AUTH_KEY ), 0, 16 );
	$result = openssl_encrypt( $string, EMMO_ENCRYPT_METHOD, $key, 0, $ivalue );

	return base64_encode( $result );
}

/**
 * Decrypt smtp password for creating connection to smtp server
 *
 * @param $string
 *
 * @return false|string
 */
function emmo_decrypt_password_for_db( $string ) {
	$key    = hash( 'sha256', AUTH_KEY );
	$ivalue = substr( hash( 'sha256', SECURE_AUTH_KEY ), 0, 16 ); // sha256 is hash_hmac_algo

	return openssl_decrypt( base64_decode( $string ), EMMO_ENCRYPT_METHOD, $key, 0, $ivalue );
}