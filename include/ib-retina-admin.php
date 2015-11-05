<?php

class IB_Retina_Admin {
	/**
	 * @var string
	 */
	protected static $capability;

	/**
	 * Initialize.
	 */
	public static function init() {
		add_filter( 'wp_generate_attachment_metadata', array( __CLASS__, 'create_2x_images' ), 10, 2 );
		add_filter( 'delete_attachment', array( __CLASS__, 'delete_2x_images' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'setup_settings' ) );
		add_action( 'admin_head-upload.php', array( __CLASS__, 'add_bulk_action' ) );
		add_action( 'admin_action_ib_retina_bulk_delete', array( __CLASS__, 'bulk_delete' ) );
		add_action( 'admin_action_-1', array( __CLASS__, 'bulk_delete' ) );
		
		self::$capability = apply_filters( 'ib_retina_cap', 'manage_options' );
	}

	/**
	 * Create a 2x version of an attachment.
	 *
	 * @param array $metadata
	 * @param int $attachment_id
	 * @return array
	 */
	public static function create_2x_images( $metadata, $attachment_id ) {
		if ( empty( $metadata['sizes'] ) ) return $metadata;

		$settings = get_option( 'ib_retina_settings' );
		$sizes = null;

		if ( is_array( $settings ) && ! empty( $settings['sizes'] ) ) {
			$sizes = $settings['sizes'];
		}

		if ( ! is_array( $sizes ) ) $sizes = array();

		foreach ( $metadata['sizes'] as $size => $data ) {
			if ( empty( $data ) || ! in_array( $size, $sizes ) ) continue;

			self::create_2x_image( get_attached_file( $attachment_id ), $data['width'], $data['height'], true );
		}

		return $metadata;
	}

	/**
	 * Create @2x image.
	 *
	 * @param string $file
	 * @param int $width
	 * @param int $height
	 * @param bool $crop
	 * @return bool|array
	 */
	public static function create_2x_image( $file, $width, $height, $crop = false ) {
		if ( ! $width && ! $height ) return false;

		$resized_file = wp_get_image_editor( $file );

		if ( is_wp_error( $resized_file ) ) return false;

		$filename = $resized_file->generate_filename( $width . 'x' . $height . '@2x' );
		$resized_file->set_quality( 80 );
		$resized_file->resize( $width * 2, $height * 2, $crop );
		$resized_file->save( $filename );
		$info = $resized_file->get_size();

		return array(
			'file'   => wp_basename( $filename ),
			'width'  => $info['width'],
			'height' => $info['height'],
		);
	}

	/**
	 * Delete @2x image for a given attachment.
	 *
	 * @param int $attachment_id
	 */
	public static function delete_2x_images( $attachment_id ) {
		$meta = wp_get_attachment_metadata( $attachment_id );

		if ( ! $meta ) return;

		$upload_dir = wp_upload_dir();
		$path = pathinfo( $meta['file'] );

		if ( empty( $meta['sizes'] ) ) return;

		foreach ( $meta['sizes'] as $size => $size_data ) {
			$original_filename = $upload_dir['basedir'] . '/' . $path['dirname'] . '/' . $size_data['file'];
			$retina_filename = substr_replace( $original_filename, '@2x.', strrpos( $original_filename, '.' ), 1 );

			if ( file_exists( $retina_filename ) ) {
				unlink( $retina_filename );
			}
		}
	}

	/**
	 * Add admin menu page.
	 */
	public static function admin_menu() {
		add_media_page(
			__( 'IB Retina', 'ib-retina' ),
			__( 'IB Retina', 'ib-retina' ),
			self::$capability,
			'ib_retina_admin',
			array( __CLASS__, 'admin_page' )
		);
	}

	/**
	 * Display settings page.
	 */
	public static function admin_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'IB Retina', 'ib-retina' ); ?></h2>

			<?php
				settings_errors( 'general' );
				echo '<form action="options.php" method="post">';
				settings_fields( 'ib_retina_settings' );
				do_settings_sections( 'ib_retina_admin' ); // page slug
				submit_button();
				echo '</form>';
			?>
		</div>
		<?php
	}

	/**
	 * Add plugin settings.
	 * Uses WordPress Settings API.
	 */
	public static function setup_settings() {
		require_once 'ib-settings-helper.php';

		add_settings_section(
			'ib_retina_settings', // id
			__( 'Settings', 'ib-retina' ),
			array( 'IB_Settings_Helper', 'dummy_section_description' ),
			'ib_retina_admin' // page
		);

		$sizes_tmp = get_intermediate_image_sizes();
		$image_sizes = array();
		foreach ( $sizes_tmp as $size ) {
			$image_sizes[ $size ] = $size;
		}
		unset( $sizes_tmp );

		add_settings_field(
			'ib_retina_sizes',
			__( 'Image Sizes', 'ib-retina' ),
			array( 'IB_Settings_Helper', 'field_checkbox_group' ),
			'ib_retina_admin', // page
			'ib_retina_settings', // section
			array(
				'name'        => 'sizes',
				'group'       => 'ib_retina_settings',
				'id'          => 'ib_retina_sizes',
				'choices'     => $image_sizes,
				'description' => __( '2x images will be created for the selected image sizes only.', 'ib-retina' ),
			)
		);

		register_setting(
			'ib_retina_settings', // option group
			'ib_retina_settings',
			array( __CLASS__, 'validate_settings' )
		);
	}

	/**
	 * Validate IB Retina settings.
	 *
	 * @param array $input
	 * @return array
	 */
	public static function validate_settings( $input ) {
		if ( ! is_array( $input ) ) return array();

		$clean = array();

		foreach ( $input as $key => $value ) {
			switch ( $key ) {
				case 'sizes':
					if ( ! is_array( $value ) ) continue;

					$clean[ $key ] = array();
					$sizes = get_intermediate_image_sizes();

					foreach ( $value as $size ) {
						if ( in_array( $size, $sizes ) ) {
							$clean[ $key ][] = $size;
						}
					}

					break;
			}
		}

		return $clean;
	}

	/**
	 * Add "Delete 2x Images" to bulk actions on Media page.
	 * Example from Regenerate Thumbnails plugin.
	 */
	public static function add_bulk_action() {
		if ( ! current_user_can( self::$capability ) ) {
			return;
		}

		?>
		<script>
		jQuery(document).ready(function($) {
			var option = '<option value="ib_retina_bulk_delete"><?php _e( 'Delete 2x Images', 'ib-retina' ); ?></option>';
			$('select[name^="action"] option:last-child').before(option);
		});
		</script>
		<?php
	}

	/**
	 * Process "Delete 2x Images" bulk action on Media page.
	 * Example from Regenerate Thumbnails plugin.
	 */
	public static function bulk_delete() {
		if ( ! current_user_can( self::$capability ) ) {
			return;
		}

		if ( empty( $_REQUEST['action'] ) || ( 'ib_retina_bulk_delete' != $_REQUEST['action'] && 'ib_retina_bulk_delete' != $_REQUEST['action2'] ) ) {
			return;
		}

		if ( empty( $_REQUEST['media'] ) || ! is_array( $_REQUEST['media'] ) ) {
			return;
		}

		check_admin_referer( 'bulk-media' );

		$ids = array_map( 'intval', $_REQUEST['media'] );

		foreach ( $_REQUEST['media'] as $attachment_id ) {
			if ( is_numeric( $attachment_id ) ) {
				self::delete_2x_images( $attachment_id );
			}
		}
	}
}