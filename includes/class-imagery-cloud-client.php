<?php

namespace WP_Imagery_Cloud;

/**
 *
 */
class ImageryCloudClient {
	public function __construct() {

	}

	/**
	 *
	 * @param $url
	 * @param $sizes
	 * @param $srcsets
	 * @param $forceFormat bool
	 * @param $callbackUrl string
	 * @param $useWebP bool
	 * @param $discardOriginal bool
	 * @param $focalPoint 'attention' | 'entropy' | 'center' | 'north' | 'northeast' | 'east' |
	 * 'southeast' | 'south' | 'southwest' | 'west' | 'northwest'
	 * @param $interpolationAlgorithm 'lanczos3' | 'lanczos2' | 'mitchell' | 'cubic' | 'nearest'
	 */
	public function enqueue(
		string $url, array $sizes, array $srcsets, string $callbackUrl = null,
		bool $forceFormat = false, bool $useWebP = true,
		int $desired_timestamp = null, $discardOriginal = false,
		string $focalPoint = 'attention', string $interpolationAlgorithm = 'lanczos3'
	) {
		$response = wp_remote_post("https://api.imagerycloud.com/v1/jobs", [
			"body" => json_encode([
				"url" => $url,
				"sizes" => $sizes,
				"srcsets" => $srcsets,
				"callbackUrl" => $callbackUrl,
				"useWebP" => $useWebP,
				"focalPoint" => $focalPoint,
				"desiredTimestamp" => $desired_timestamp,
			]),
			"headers" => [
				"Authorization" => "Token " . WP_IMAGERY_CLOUD_TOKEN,
				"Content-Type" => "application/json",
			],
			"data_format" => "body",
			"blocking" => true,
			"timeout" => 10,
		]);

		return $response;
	}

	public function get() {

	}
}
