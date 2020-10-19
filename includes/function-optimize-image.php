<?php

namespace WP_Gatsby_Image;

use \Spatie\ImageOptimizer\OptimizerChainFactory;

function optimize_image( $filename ) {
	$optimizerChain = OptimizerChainFactory::create();
	$optimizerChain->optimize( $filename );
}
