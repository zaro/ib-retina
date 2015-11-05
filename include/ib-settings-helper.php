<?php

/**
 * Settings API helper class.
 */
class IB_Settings_Helper {
	/**
	 * Output checkbox field.
	 *
	 * @param array $atts
	 */
	public static function field_checkbox( $atts ) {
		if ( ! empty( $atts['group'] ) ) {
			$name = $atts['group'] . '[' . $atts['name'] . ']';
			$settings = get_option( $atts['group'], array() );
			$value = isset( $settings[ $atts['name'] ] ) ? $settings[ $atts['name'] ] : array();
		} else {
			$name = $atts['name'];
			$value = get_option( $atts['name'], '' );
		}

		$id_attr = '';
		if ( ! empty( $atts['id'] ) ) $id_attr = ' id="' . esc_attr( $atts['id'] ) . '"';

		echo '<input type="checkbox" name="' . esc_attr( $name ) . '"' . $id_attr . ' class="checkbox" value="1" ' . checked( 1, $value, false ) . '>';

		if ( ! empty( $atts['description'] ) ) {
			echo '<p class="description">' . $atts['description'] . '</p>';
		}
	}

	/**
	 * Output checkbox group field.
	 *
	 * @param array $atts
	 */
	public static function field_checkbox_group( $atts ) {
		if ( ! empty( $atts['group'] ) ) {
			$name = $atts['group'] . '[' . $atts['name'] . '][]';
			$settings = get_option( $atts['group'], array() );
			$value = isset( $settings[ $atts['name'] ] ) ? $settings[ $atts['name'] ] : array();
		} else {
			$name = $atts['name'] . '[]';
			$value = get_option( $atts['name'], array() );
		}

		if ( ! is_array( $value ) ) $value = array();

		foreach ( $atts['choices'] as $c_value => $c_label ) {
			$checkbox_value = in_array( $c_value, $value ) ? $c_value : '';

			echo '<div><label><input type="checkbox" name="' . esc_attr( $name ) . '" class="checkbox" value="'
				 . esc_attr( $c_value ) . '" ' . checked( $c_value, $checkbox_value, false ) . '>'
				 . esc_html( $c_label ) . '</label></div>';
		}

		if ( ! empty( $atts['description'] ) ) {
			echo '<p class="description">' . $atts['description'] . '</p>';
		}
	}

	/**
	 * A dummy section description.
	 */
	public static function dummy_section_description() {}
}