<?php

register_graphql_object_type( 'MediaItemVersionSources', [
	'description' => __( "Media Item SrcSet Sources property support ", 'wp-gatsby-image' ),
	'fields' => [
		'mimeType' => [
			'type' => 'String',
			'description' => __('Image mimetype', 'wp-gatsby-image'),
		],
		'srcSet' => [
			'type' => 'String',
			'description' => __('Image srcset', 'wp-gatsby-image'),
		],
	],
]);

register_graphql_object_type( 'MediaItemVersion', [
	'description' => __( "Srcset support for MediaItem ", 'wp-gatsby-image' ),
	'fields' => [
		'name' => [
			'type' => 'String',
			'description' => __('The name of the srcset', 'wp-gatsby-image'),
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
		'sources' => [
			'type' => [ 'list_of' => 'MediaItemVersionSources' ],
			'description' => __('Original image format srcset', 'wp-gatsby-image'),
		],
		'base64' => [
			'type' => 'String',
			'description' => __('Base64 image representation', 'wp-gatsby-image'),
		],
	],
]);
