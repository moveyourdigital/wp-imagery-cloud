<?php

namespace WP_Gatsby_Image;

function update_image_srcset( array $sizes, array $sources, array $registered_image_srcsets, $full_upload_uri ) {

	$_srcsets = [];

	foreach ( $registered_image_srcsets as $name => $sizes ) {
		$src = '';
		$width = 1;
		$height = 1;
		$srcSet = [];

		if ( WP_GATSBY_IMAGE_WEBP_ENABLED === true ) {
			$srcSetWebp = [];
		}

		foreach ( $sizes as $size ) {
			if ( ! array_key_exists( $size, $sources ) ) continue;

			$jpeg = $sources[ $size ][0];
			$srcSet[] = $full_upload_uri . $jpeg['file'] . ' ' . $jpeg['width'] . 'w';

			if ( WP_GATSBY_IMAGE_WEBP_ENABLED === true ) {
				if ( isset( $sources[ $size ][1] ) ) {
					$webp = $sources[ $size ][1];
					$srcSetWebp[] = $full_upload_uri . $webp['file'] . ' ' . $webp['width'] . 'w';
				}
			}

			if ( $jpeg['width'] > $width ) {
				$width = $jpeg['width'];
				$height = $jpeg['height'];
				$src = $full_upload_uri . $jpeg['file'];

				if ( isset( $webp['file'] ) ) {
					$srcWebp = $full_upload_uri . $webp['file'];
				}
			}
		}

		if ( $src && $srcSet ) {
			$_srcset = [
				'name'			=> $name,
				'aspectRatio' 	=> $width / $height,
				'width'			=> $width,
				'height'		=> $height,
				'src'			=> $src,
				'srcSet'		=> join( ', ', $srcSet ),
			];

			if ( WP_GATSBY_IMAGE_WEBP_ENABLED === true ) {
				if ( isset( $srcWebp ) && ! empty( $srcWebp ) ) {
					$_srcset['srcWebp'] = $srcWebp;
				}

				if ( isset( $srcSetWebp ) && ! empty( $srcSetWebp ) ) {
					$_srcset['srcSetWebp'] = join( ', ', $srcSetWebp );
				}
			}

			$_srcsets[] = $_srcset;
		}
	}

	return $_srcsets;
}
