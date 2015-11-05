<?php

class IB_Retina {
	/**
	 * Initialize retina functionality.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_filter( 'wp_get_attachment_image_attributes', array( __CLASS__, 'add_retina_image_attributes' ), 10, 2 );
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public static function enqueue_scripts() {
		//wp_enqueue_script( 'ibfw-retina', IBFW_URL . '/framework/retina/retina.js', array(), '1.0.0', false );
		wp_enqueue_script( 'picturefill', IB_RETINA_URL . '/js/picturefill.min.js', array(), '2.2.0', false );
	}

	/**
	 * Check if retina image exists.
	 *
	 * @param string $src
	 * @return null|string
	 */
	public static function image_get_2x( $src ) {
		$image_2x = substr_replace( $src, '@2x.', strrpos( $src, '.' ), 1 );
		$parsed = parse_url( $image_2x );

		if ( ! $parsed ) return null;

		$filename = untrailingslashit( ABSPATH ) . $parsed['path'];

		if ( ! file_exists( $filename ) ) return null;

		$image = array(
			'w' => 0,
			'h' => 0,
			'src' => $image_2x,
		);

		$size = getimagesize( $filename );

		if ( $size ) {
			$image['w'] = $size[0];
			$image['h'] = $size[1];
		}

		return $image;
	}

	/**
	 * Add retina image attributes to the_post_thumbnail.
	 *
	 * @param array $attr
	 * @param WP_Post $attachment
	 * @return array
	 */
	public static function add_retina_image_attributes( $attr, $attachment ) {
		$image_2x = self::image_get_2x( $attr['src'] );

		if ( $image_2x && ! isset( $attr['srcset'] ) ) {
			if ( $image_2x['w'] ) {
				$condition = $image_2x['w'] . 'w';
			} else {
				$condition = '2x';
			}

			$attr['srcset'] = '';

			if ( '2x' != $condition ) {
				preg_match( '/([0-9]+)x([0-9]+)\./', $attr['src'], $size );

				if ( 3 == count( $size ) ) {
					$attr['srcset'] = $attr['src'] . ' ' . $size[1] . 'w';
					$attr['sizes'] = '(min-width: 768px) ' . $size[1] . 'px, 96vw';
				} else {
					$attr['srcset'] = $attr['src'];
				}
			} else {
				$attr['srcset'] = $attr['src'] . ' 1x';
			}

			$attr['srcset'] .= ', ' . $image_2x['src'] . ' ' . $condition;
		}

		return $attr;
	}

	/**
	 * Get 2x image HTML.
	 *
	 * @param int $attachment_id
	 * @param string $size
	 * @return string
	 */
	public static function get_2x_image_html( $attachment_id, $size ) {
		$attachment_attr = wp_get_attachment_image_src( $attachment_id, $size );

		if ( ! $attachment_attr ) return '';

		$attr = self::add_retina_image_attributes( array(
			'src'    => $attachment_attr[0],
			'width'  => $attachment_attr[1],
			'height' => $attachment_attr[2],
		), null );

		$output = '<img';

		foreach ( $attr as $name => $value ) {
			$output .= ' ' . $name . '="' . esc_attr( $value ) . '"';
		}

		$output .= ' alt="">';
		
		return $output;
	}
}
