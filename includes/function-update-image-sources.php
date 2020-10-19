<?php

namespace WP_Gatsby_Image;

function update_image_sources( array $size_detail, string $webpfilename = null ) {

	$size_formats = [
		$size_detail,
	];

	if ( WP_GATSBY_IMAGE_WEBP_ENABLED === true ) {
		if ( $webpfilename ) {
			$size_formats[] = [
				'file' => $webpfilename,
				'width' => $size_detail['width'],
				'height' => $size_detail['height'],
				'mime-type' => 'image/webp',
			];
		}
	}

	return $size_formats;
}
