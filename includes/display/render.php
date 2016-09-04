<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NF_FU_Display_Render {

	/**
	 * @var
	 */
	protected static $scripts_loaded = false;

	/**
	 * NF_FU_Display_Render constructor.
	 */
	public function __construct() {
		add_filter( 'ninja_forms_localize_fields', array( $this, 'enqueue_scripts' ) );
		add_filter( 'ninja_forms_localize_fields_preview', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts for the frontend
	 *
	 * @param array|object $field
	 *
	 * @return array|object $field
	 */
	public function enqueue_scripts( $field ) {
		if ( is_object( $field ) ) {
			$settings = $field->get_settings();
			if ( NF_FU_File_Uploads::TYPE !== $settings['type'] ) {
				return $field;
			}
		}

		if ( is_array( $field ) && ( ! isset( $field['settings']['type'] ) || NF_FU_File_Uploads::TYPE !== $field['settings']['type'] ) ) {
			return $field;
		}

		if ( self::$scripts_loaded ) {
			return $field;
		}

		$url = plugin_dir_url( NF_File_Uploads()->plugin_file_path );
		wp_enqueue_script( 'jquery-iframe-transport', $url . 'assets/js/lib/jquery.iframe-transport.js', array(
			'jquery',
		) );
		wp_enqueue_script( 'jquery-fileupload', $url . 'assets/js/lib/jquery.fileupload.js', array(
			'jquery',
			'jquery-ui-widget',
			'jquery-iframe-transport',
		) );

		wp_enqueue_script( 'jquery-fileupload-process', $url . 'assets/js/lib/jquery.fileupload-process.js', array(
			'jquery-fileupload',
		) );

		wp_enqueue_script( 'jquery-fileupload-validate', $url . 'assets/js/lib/jquery.fileupload-validate.js', array(
			'jquery-fileupload',
			'jquery-fileupload-process',
		) );

		wp_enqueue_script( 'nf-fu-file-upload', $url . 'assets/js/front-end/controllers/fieldFile.js', array(
			'jquery',
			'nf-front-end',
			'jquery-fileupload',
		) );

		wp_localize_script( 'nf-fu-file-upload', 'nf_upload', array(
			'nonces'  => array(
				'file_upload' => wp_create_nonce( 'nf-file-upload' ),
			),
			'strings' => array(
				'file_limit'           => __( 'Max %n files are allowed', 'ninja-forms-uploads' ),
				'upload_error'         => __( 'Nonce error, upload failed', 'ninja-forms-uploads' ),
				'unknown_upload_error' => __( 'Upload error, upload failed', 'ninja-forms-uploads' ),
				'max_file_size_error'  => __( 'File exceeds maximum file size. File must be under %nMB.', 'ninja-forms-uploads' ),
			),
		) );

		wp_enqueue_style( 'nf-fu-jquery-fileupload', $url . 'assets/css/file-upload.css' );

		self::$scripts_loaded = true;

		return $field;
	}
} 