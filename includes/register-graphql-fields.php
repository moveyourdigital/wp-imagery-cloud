<?php

$allowed_post_types = \WPGraphQL::get_allowed_post_types();

if (!empty($allowed_post_types) && is_array($allowed_post_types)) {

	foreach ($allowed_post_types as $post_type) {
		$post_type_object = get_post_type_object($post_type);

		if ('attachment' === $post_type_object->name && true === $post_type_object->show_in_graphql && isset($post_type_object->graphql_single_name)) {

			/**
			 * Register fields custom to the MediaItem Type
			 */
			register_graphql_fields(
				$post_type_object->graphql_single_name,
				[
					'gatsbyImage'   => [
						'type'        => [ 'list_of' => 'MediaItemGatsbyImage' ],
						'description' => __('Gatsby Image array of objects', 'wp-graphql'),
						'args'        => [
							'sizes' => [
								'type'        => [ 'list_of' => 'String' ],
								'description' => __( 'Desired sizes of the MediaItem to return', 'wp-graphql'),
							],
						],
						'resolve' => function ( \WPGraphQL\Model\Post $image, $args, $context, $info ) {
							$metadata = get_post_meta( $image->ID, '_wp_gatsby_image_metadata', true );

							if ( $metadata && isset( $metadata['srcsets'] ) ) {
								if ( isset( $args['sizes'] ) ) {
									return array_filter( $metadata['srcsets'], function ( $item ) use ( $args ) {
										return in_array( $item['name'], $args['sizes'] );
									} );
								} else {
									return $metadata['srcsets'];
								}
							}
						},
					],
				]
			);
		}
	}
}
