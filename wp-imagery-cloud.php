<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/moveyourdigital/wp-imagery-cloud
 * @since             0.1.0
 * @package           WP_Imagery_Cloud
 *
 * @wordpress-plugin
 * Plugin Name:     Imagery Cloud
 * Plugin URI:      https://github.com/moveyourdigital/wp-imagery-cloud
 * Description:     Generates image sizes, WebP version, art directions and srcset using Imagery Cloud service
 * Author:          Move Your Digital, Inc.
 * Author URI:      https://moveyourdigital.com
 * Text Domain:     wp-imagery-cloud
 * Domain Path:     /languages
 * Version:         0.1.1
 * License:			MIT
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/** This filter is documented in wp-settings.php */
add_action( 'plugins_loaded', function () {

	if ( ! defined( 'WP_IMAGERY_CLOUD_WEBP_ENABLED' ) ) {
		define( 'WP_IMAGERY_CLOUD_WEBP_ENABLED', true );
	}

	if ( ! defined( 'WP_IMAGERY_CLOUD_WORDPRESS_SIZES_DISABLED' ) ) {
		define( 'WP_IMAGERY_CLOUD_WORDPRESS_SIZES_DISABLED', false );
	}

	if ( ! defined( 'WP_IMAGERY_CLOUD_ART_DIRECTION_ENABLED' ) ) {
		define( 'WP_IMAGERY_CLOUD_ART_DIRECTION_ENABLED', true );
	}

	if ( ! defined( 'WP_IMAGERY_CLOUD_BASE64_ENABLED' ) ) {
		define( 'WP_IMAGERY_CLOUD_BASE64_ENABLED', true );
	}

	if ( ! defined( 'WP_IMAGERY_CLOUD_BLURHA_ENABLED' ) ) {
		define( 'WP_IMAGERY_CLOUD_BLURHA_ENABLED', true );
	}

	if ( ! defined( 'WP_IMAGERY_CLOUD_TRACED_SVG_ENABLED' ) ) {
		define( 'WP_IMAGERY_CLOUD_TRACED_SVG_ENABLED', false );
	}

	require_once dirname( __FILE__ ) . '/includes/function-image-size-sets.php';
} );


/** This filter is documented in wp-settings.php */
add_action( 'wp_loaded', function () {

	if ( class_exists( '\WPGraphQL' ) ) {
		require_once dirname( __FILE__ ) . '/includes/register-graphql-fields.php';
	}

} );


/** This filter is documented in wp-settings.php */
add_action( 'graphql_register_types', function () {

	if ( class_exists( '\WPGraphQL' ) ) {
		require_once dirname( __FILE__ ) . '/includes/register-graphql-object-type.php';
	}

} );

/** This filter is documented in wp-admin/includes/image.php */
add_filter( 'intermediate_image_sizes_advanced', function ( array $sizes, array $metadata, int $attachment_id = null ) {

	require_once dirname( __FILE__ ) . '/includes/class-imagery-cloud-client.php';

	$upload_dir = wp_get_upload_dir();
	$file_url = trailingslashit( $upload_dir['baseurl'] ) . $metadata['file'];

	if ( null === $attachment_id ) {
		$attachment_id = attachment_url_to_postid( $file_url );
	}

	$srcsets = \WP_Imagery_Cloud\get_registered_image_srcsets();

	$client = new \WP_Imagery_Cloud\ImageryCloudClient();
	$response = $client->enqueue(
		$file_url,
		array_map(function ($details) {
			return [
				'width' => $details['width'],
				'height' => $details['crop'] ? $details['height'] : null,
			];
		}, $sizes),
		array_map(function ($sizes) {
			return [
				'sizes' => $sizes,
				'useBase64' => WP_IMAGERY_CLOUD_BASE64_ENABLED,
				'useBlurHa' => WP_IMAGERY_CLOUD_BLURHA_ENABLED,
			];
		}, $srcsets),
		rest_url( "imagery-cloud/v1/media/$attachment_id" ),
		'jpeg',
		WP_IMAGERY_CLOUD_WEBP_ENABLED,

	);

	if (is_wp_error($response)) {
		//var_dump($response);
		// TODO: reschedule
	}

	return WP_IMAGERY_CLOUD_WORDPRESS_SIZES_DISABLED ? [] : $sizes;

}, 999, 3 );


/** This action is documented in wp-includes/rest-api.php */
add_action( 'rest_api_init', function ( WP_REST_Server $_ ) {

	register_rest_route( 'imagery-cloud/v1', '/media/(?P<wpid>\d+)', array(
		'methods' => 'POST',
		'callback' => function ( WP_REST_Request $request ) {
			$metadata = json_decode( $request->get_body(), true );

			update_post_meta(
				(int) $request->get_param('wpid'), '_wp_gatsby_image_metadata', $metadata['results']
			);
		},
	) );

	register_rest_field( 'attachment', 'mediaSets', [
		'get_callback' => function ($data) {
		  return [];
		}
  ] );

	register_rest_field( 'attachment', 'media_srcsets', [
	  	'get_callback' => function ($data) {
			$metadata = get_post_meta( $data['id'], '_wp_gatsby_image_metadata', true );

			if ( is_array( $metadata ) && array_key_exists( 'srcsets', $metadata )) {
				return $metadata['srcsets'];
			}
	  	}
	] );

} );

