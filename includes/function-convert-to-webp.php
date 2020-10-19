<?php

namespace WP_Gatsby_Image;

use \WebPConvert\WebPConvert;
use \WebPConvert\Convert\Exceptions\ConversionFailedException;
use \Spatie\ImageOptimizer\OptimizerChainFactory;

function convert_to_webp( $source_filename, $destination_filename ) {

	try {
		WebPConvert::convert( $source_filename, $destination_filename );

	} catch( ConversionFailedException $e ) {
		return new \WP_Error(
			'WP_GATSBY_IMAGE_CONVERSION_ERROR',
			"Unable to convert $source_filename to WebP", [
				'code' => $e->getCode(),
				'message' => $e->getMessage(),
			]
		);
	}

	$optimizerChain = OptimizerChainFactory::create();
	$optimizerChain->optimize( $destination_filename );
}
