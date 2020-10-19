<?php

namespace WP_Gatsby_Image;

class Background_Image_Process extends \WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'gatsby_image_process';

	/**
	 * Background resizing task.
	 *
	 * This gets called on each of the items in the queue.
	 * Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $item ) {

		if ( ! isset( $item['attempts'] ) ) {
			$item['attempts'] = 0;
		}

		$success = image_processor(
			$item['attachment_id'], $item['size'], $item['file'], $item['width'], $item['height'], $item['crop']
		);

		/**
		 * Everything was successful - remove from queue
		 */
		if ( true === $success ) {
			return false;
		}

		/**
		 * If we're here, something errored.
		 *
		 * If we've already tried this image 5 times, remove it.
		 * If not, bump the attempts and add it back onto the queue
		 */
		if ( $item['attempts'] == 5 ) {
			return false;
		}

		$item['attempts'] ++;

		return $item;
	}
}
