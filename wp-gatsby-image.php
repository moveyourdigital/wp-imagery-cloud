<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/moveyourdigital/wp-gatsby-image
 * @since             0.1.0
 * @package           WP_Gatsby_Image
 *
 * @wordpress-plugin
 * Plugin Name:     WP Gatsby Image
 * Plugin URI:      https://github.com/moveyourdigital/wp-gatsby-image
 * Description:     Provides Art Direction for images generated by WordPress with an API that plays nicely with gatsby-image plugin
 * Author:          Move Your Digital, Inc.
 * Author URI:      https://moveyourdigital.com
 * Text Domain:     wp-gatsby-image
 * Domain Path:     /languages
 * Version:         0.1.0
 * License:			MIT
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

global $_wp_gatsby_image_queue;

/** This filter is documented in wp-settings.php */
add_action( 'plugins_loaded', function () {
	global $_wp_gatsby_image_queue;

	if ( ! class_exists( '\\Spatie\\ImageOptimizer\\OptimizerChainFactory' ) ) {
		require_once dirname( __FILE__ ) . '/vendor/autoload.php';
	}

	if ( ! defined( 'WP_GATSBY_IMAGE_WEBP_ENABLED' ) ) {
		define( 'WP_GATSBY_IMAGE_WEBP_ENABLED', true );
	}

	if ( ! defined( 'WP_GATSBY_IMAGE_ART_DIRECTION_ENABLED' ) ) {
		define( 'WP_GATSBY_IMAGE_ART_DIRECTION_ENABLED', true );
	}

	if ( ! defined( 'WP_GATSBY_IMAGE_BASE64_ENABLED' ) ) {
		define( 'WP_GATSBY_IMAGE_BASE64_ENABLED', true );
	}

	if ( ! defined( 'WP_GATSBY_IMAGE_BASE64_WIDTH' ) ) {
		define( 'WP_GATSBY_IMAGE_BASE64_WIDTH', '32' );
	}

	if ( ! defined( 'WP_GATSBY_IMAGE_TRACED_SVG_ENABLED' ) ) {
		define( 'WP_GATSBY_IMAGE_TRACED_SVG_ENABLED', false );
	}

	require_once dirname( __FILE__ ) . '/includes/function-image-size-sets.php';
	require_once dirname( __FILE__ ) . '/includes/function-image-processor.php';
	require_once dirname( __FILE__ ) . '/includes/class-background-image-process.php';

	$_wp_gatsby_image_queue = new \WP_Gatsby_Image\Background_Image_Process();

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

	global $_wp_gatsby_image_queue;

	$upload_dir = wp_get_upload_dir();
	$file_path = trailingslashit( $upload_dir['basedir'] ) . $metadata['file'];

	if ( null === $attachment_id ) {
		$file_url = trailingslashit( $upload_dir['baseurl'] ) . $metadata['file'];
		$attachment_id = attachment_url_to_postid( $file_url );
	}

	foreach ( $sizes as $size => $size_data ) {
		if ( ! isset( $size_data['width'] ) && ! isset( $size_data['height'] ) ) {
			continue;
		}

		if ( ! isset( $size_data['width'] ) ) {
			$size_data['width'] = null;
		}
		if ( ! isset( $size_data['height'] ) ) {
			$size_data['height'] = null;
		}

		if ( ! isset( $size_data['crop'] ) ) {
			$size_data['crop'] = false;
		}

		$_wp_gatsby_image_queue->push_to_queue( [
			'attachment_id' => $attachment_id,
			'file'          => $file_path,
			'size'		    => $size,
			'width'         => $size_data['width'],
			'height'        => $size_data['height'],
			'crop'          => $size_data['crop'],
		] );

		$_wp_gatsby_image_queue->save()->dispatch();

	}

	return [];

}, 999, 3 );


/** This action is documented in includes/class-background-image-process.php */
add_action( 'WP_Gatsby_Image\\after_image_size_process', function (
	array $metadata, int $attachment_id, string $filename, string $size, int $width, int $height, bool $crop
) {

	if ( ! function_exists( '\\WP_Gatsby_Image\\optimize_image' ) ) {
		require_once dirname( __FILE__ ) . '/includes/function-optimize-image.php';
	}

	if ( ! function_exists( '\\WP_Gatsby_Image\\convert_to_webp' ) ) {
		require_once dirname( __FILE__ ) . '/includes/function-convert-to-webp.php';
	}

	if ( ! class_exists( '\\WP_Gatsby_Image\\update_image_sources' ) ) {
		require_once dirname( __FILE__ ) . '/includes/function-update-image-sources.php';
	}

	if ( ! class_exists( '\\WP_Gatsby_Image\\update_image_srcset' ) ) {
		require_once dirname( __FILE__ ) . '/includes/function-update-image-srcset.php';
	}

	$temp_filename = wp_tempnam();
	copy( $filename, $temp_filename );

	\WP_Gatsby_Image\optimize_image( $temp_filename );
	copy( $temp_filename, $filename );

	if ( WP_GATSBY_IMAGE_WEBP_ENABLED === true ) {

		$base_upload_dir = trailingslashit( wp_get_upload_dir()['basedir'] );
		$full_upload_dir = $base_upload_dir . trailingslashit( dirname( $metadata['file'] ) );

		rename( $temp_webp_filename = wp_tempnam(), $temp_webp_filename .= '.webp' );

		\WP_Gatsby_Image\convert_to_webp( $temp_filename, $temp_webp_filename );

		$filepathinfo = pathinfo( $metadata['sizes'][ $size ]['file'] );
		$webpfilename = $filepathinfo['filename'] . '.webp';

		copy( $temp_webp_filename, $full_upload_dir . $webpfilename );
	}

	$extended_metadata = get_post_meta( $attachment_id, '_wp_gatsby_image_metadata', true );

	if ( ! is_array( $extended_metadata ) ) {
		$extended_metadata = [
			'file' 		=> $metadata['file'],
			'width'		=> $metadata['width'],
			'height'	=> $metadata['height'],
			'srcsets' 	=> [],
			'sources' 	=> [],
		];
	}

	$extended_metadata['sources'][ $size ] = \WP_Gatsby_Image\update_image_sources(
		$metadata['sizes'][ $size ], $webpfilename,
	);

	$registered_image_srcsets = \WP_Gatsby_Image\get_registered_image_srcsets();

	$base_upload_uri = trailingslashit( wp_get_upload_dir()['baseurl'] );
	$full_upload_uri = $base_upload_uri . trailingslashit( dirname( $metadata['file'] ) );

	$extended_metadata['srcsets'] = \WP_Gatsby_Image\update_image_srcset(
		$metadata['sizes'], $extended_metadata['sources'], $registered_image_srcsets, $full_upload_uri
	);

	update_post_meta( $attachment_id, '_wp_gatsby_image_metadata', $extended_metadata );

}, 10, 7 );


/** This action is documented in wp-includes/rest-api.php */
add_action( 'rest_api_init', function ( WP_REST_Server $_ ) {

	register_rest_field( 'attachment', 'mediaSets', [
	  	'get_callback' => function ($data) {
			$metadata = get_post_meta( $data['id'], '_wp_gatsby_image_metadata', true );

			if ( is_array( $metadata ) && array_key_exists( 'srcsets', $metadata )) {
				return $metadata['srcsets'];
			}
	  	}
	] );

} );

