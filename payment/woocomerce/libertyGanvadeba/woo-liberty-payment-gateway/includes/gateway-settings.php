<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Liberty Gateway
 */
return array(
	'enabled' => array(
		'title'   => __( 'Enable/Disable', 'woo-liberty' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Liberty', 'woo-liberty' ),
		'default' => 'yes',
	),
	'title' => array(
		'title'       => __( 'Title', 'woo-liberty' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'woo-liberty' ),
		'default'     => __( 'Liberty', 'woo-liberty' ),
		'desc_tip'    => true,
	),
	'description' => array(
		'title'       => __( 'Description', 'woo-liberty' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'woo-liberty' ),
		'default'     => __( 'Pay with your credit card via Liberty', 'woo-liberty' ),
	),
	'debug' => array(
		'title'       => __( 'Debug Log', 'woo-liberty' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'woo-liberty' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log Liberty events, such as IPN requests, inside <code>%s</code>', 'woo-liberty' ), wc_get_log_file_path( 'liberty' ) ),
	),
	'testmode' => array(
		'title'       => __( 'Test mode', 'woo-liberty' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable test mode', 'woo-liberty' ),
		'default'     => 'no',
		'description' =>   __( 'Enable test mode', 'woo-liberty' ) ,
	),
//	'cert_path' => array(
//		'title'       => __( 'Certificate path', 'woo-liberty' ),
//		'type'        => 'text',
//		'description' => __( 'Absolute path to certificate in .pem format.', 'woo-liberty' ),
//		'default'     => __( '/', 'woo-liberty' ),
//	),
//	'cert_pass' => array(
//		'title'       => __( 'Certificate passphrase', 'woo-liberty' ),
//		'type'        => 'text',
//	),
    
    'codename' => array(
		'title'       => __( 'Codename', 'woo-liberty' ),
		'type'        => 'text',
	),
    'secretkey' => array(
		'title'       => __( 'Secretkey', 'woo-liberty' ),
		'type'        => 'text',
	),
	'ok_slug' => array(
		'title'       => __( 'Ok slug', 'woo-liberty' ),
		'type'        => 'text',
		'description' => sprintf( __( 'User is redirected here after payment, full url looks like this: <code>%s</code>', 'woo-liberty' ), get_bloginfo( 'url' ) . '/wc-api/ok_slug' ),
		'default'     => __( 'okliberty', 'woo-liberty' ),
	),
	'fail_slug' => array(
		'title'       => __( 'Fail slug', 'woo-liberty' ),
		'type'        => 'text',
		'description' => sprintf( __( 'User is redirected here if there was a technical error during payment, full url looks like this: <code>%s</code>', 'woo-liberty' ), get_bloginfo( 'url' ) . '/wc-api/fail_slug' ),
		'default'     => __( 'faillilberty', 'woo-liberty' ),
	),
);
