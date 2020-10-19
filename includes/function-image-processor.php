<?php

namespace WP_Gatsby_Image;

function image_processor(
	string $attachment_id, string $size, string $file, int $width, int $height, bool $crop
) {

	$editor = wp_get_image_editor( $file );

	if ( is_wp_error( $editor ) ) {
		return false;
	}

	$result = $editor->resize( $width, $height, $crop );

	if ( is_wp_error( $result ) ) {
		return false;
	}

	$result = $editor->save();

	if ( is_wp_error( $result ) ) {
		return false;
	}

	$metadata = wp_get_attachment_metadata( $attachment_id, true );

	// Update the image meta
	$metadata['sizes'][ $size ] = [
		'file'      => $result['file'],
		'width'     => $result['width'],
		'height'    => $result['height'],
		'mime-type' => $result['mime-type'],
	];

	wp_update_attachment_metadata( $attachment_id, $metadata );

	/**
	 * After the image is saved, this filter runs off.
	 */
	do_action(
		'WP_Gatsby_Image\\after_image_size_process',
		$metadata, $attachment_id, $file, $size, $width, $height, $crop,
	);

	return true;
}
