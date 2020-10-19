<?php

register_graphql_object_type( 'MediaItemGatsbyImage', [
	'description' => __( "All Gatsby Image support for MediaItem ", 'wp-gatsby-image' ),
	'fields' => [
		'name' => [
			'type' => 'String',
			'description' => __('The name of the size', 'wp-gatsby-image'),
		],
		'aspectRatio' => [
			'type' => 'Float',
			'description' => __('The ratio of width / height', 'wp-gatsby-image'),
		],
		'width' => [
			'type' => 'Integer',
			'description' => __('The fallback image width', 'wp-gatsby-image'),
		],
		'height' => [
			'type' => 'Integer',
			'description' => __('The fallback image height', 'wp-gatsby-image'),
		],
		'src' => [
			'type' => 'String',
			'description' => __('The fallback original image URI', 'wp-gatsby-image'),
		],
		'srcSet' => [
			'type' => 'String',
			'description' => __('Original image format srcset', 'wp-gatsby-image'),
		],
		'srcWebp' => [
			'type' => 'String',
			'description' => __('WebP version of image URI', 'wp-gatsby-image'),
		],
		'srcSetWebp' => [
			'type' => 'String',
			'description' => __('Srcset of WebP versions', 'wp-gatsby-image'),
		],
	],
]);
