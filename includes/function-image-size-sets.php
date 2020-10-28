<?php

namespace WP_Imagery_Cloud;

global $_wp_gatsby_image_srcsets;
$_wp_gatsby_image_srcsets = [];

function add_image_srcset ( $name, $sizes ) {
	global $_wp_gatsby_image_srcsets;
	$_wp_gatsby_image_srcsets[ $name ] = $sizes;
}

function get_registered_image_srcsets () {
	global $_wp_gatsby_image_srcsets;
	return $_wp_gatsby_image_srcsets;
}
