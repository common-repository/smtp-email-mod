<?php
/**
 * Plugin Name: SMTP Email Mod
 * Description: Enables SMTP for email sending
 * Version:     1.0.0
 * Author:      Webwerkstatt Stauß GmbH & Co .KG
 * Author URI:  https://www.stauss.de
 * Text Domain: email-mod
 */

defined( 'ABSPATH' ) or exit;

/**
 * Include all files
 */
add_action( 'init', 'emmo_init_include' );

function emmo_init_include() {
	require_once( __DIR__ . '/includes/admin-settings.php' );
}

/**
 * Initialises the internationalisation domain.
 */
add_action( 'plugins_loaded', 'emmo_load_plugin_textdomain' );
function emmo_load_plugin_textdomain() {
	load_plugin_textdomain( 'email-mod', false, basename( __DIR__ ) . '/languages/' );
}

/**
 * Setting SMTP according to options
 */
add_action( 'phpmailer_init', 'emmo_phpmailer_smtp_func' );

function emmo_phpmailer_smtp_func( $phpmailer ) {
	if ( get_option( 'emmo_enable_smtp' ) == 1 ) {
		$phpmailer->IsSMTP();
		$phpmailer->SetFrom( sanitize_email( get_option( 'emmo_senderemail' ) ),
			sanitize_text_field( get_option( 'emmo_sendername' ) ) );
		if ( get_option( 'emmo_enable_smtp' ) == 1 ) {
			$phpmailer->SMTPAuth = true;
			$phpmailer->Username = sanitize_text_field( get_option( 'emmo_username' ) );
			$phpmailer->Password = emmo_decrypt_password_for_db( sanitize_text_field( get_option( 'emmo_password' ) ) );
		} else {
			$phpmailer->SMTPAuth = false;
		}
		if ( get_option( 'emmo_encryption' ) == 'ssl' ) {
			$phpmailer->SMTPSecure = 'ssl';
		} elseif ( get_option( 'emmo_encryption' ) == 'tls' ) {
			$phpmailer->SMTPSecure = 'tls';
		}
		$phpmailer->Host = sanitize_text_field( get_option( 'emmo_host' ) );
		$phpmailer->Port = sanitize_text_field( get_option( 'emmo_port' ) );
	}
}

/**
 * Adding debug/exceptions for phpmailer
 */
add_action( 'phpmailer_init', 'emmo_phpmailer_exception' );
function emmo_phpmailer_exception( $phpmailer ) {
	if ( ! defined( 'WP_DEBUG' ) or ! WP_DEBUG ) {
		$phpmailer->SMTPDebug = 0;
		$phpmailer->debug     = 0;

		return;
	}

	// Enable SMTP
	# $phpmailer->IsSMTP();
	$phpmailer->SMTPDebug = 2;
	$phpmailer->debug     = 1;

	$data = apply_filters(
		'wp_mail',
		compact( 'to', 'subject', 'message', 'headers', 'attachments' )
	);

	// Show what we got
	current_user_can( 'manage_options' )
	and print htmlspecialchars( var_export( $phpmailer, true ) );

	$error = null;
	try {
		$sent = $phpmailer->Send();
		! $sent and $error = new WP_Error( 'phpmailer-error', $sent->ErrorInfo );
	} catch ( phpmailerException $e ) {
		$error = new WP_Error( 'phpmailer-exception', $e->errorMessage() );
	} catch ( Exception $e ) {
		$error = new WP_Error( 'phpmailer-exception-unknown', $e->getMessage() );
	}

	if ( is_wp_error( $error ) ) {
		return printf(
			"%s: %s",
			$error->get_error_code(),
			$error->get_error_message()
		);
	}
}
