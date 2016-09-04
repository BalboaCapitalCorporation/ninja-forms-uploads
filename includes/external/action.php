<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NF_FU_External_Action
 */
class NF_FU_External_Action extends NF_Abstracts_Action {

	/**
	 * @var string
	 */
	protected $_name = 'file-upload-external';

	/**
	 * @var array
	 */
	protected $_tags = array();

	/**
	 * @var string
	 */
	protected $_timing = 'normal';

	/**
	 * @var int
	 */
	protected $_priority = '9';

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		$this->_nicename = __( 'External File Upload', 'ninja-forms-uploads' );

		$this->build_settings();
	}

	/**
	 * Load settings for the action
	 */
	protected function build_settings() {
		$settings = array();
		$external = NF_File_Uploads()->externals;

		foreach ( $external->get_services() as $service ) {
			if ( $external->get( $service )->is_compatible() && $external->get( $service )->is_connected() ) {

				$settings[ 'field_list_' . $service ] = array(
					'name'        => 'field_list_' . $service,
					'type'        => 'field-list',
					'label'       => $external->get( $service )->name,
					'width'       => 'full',
					'group'       => 'primary',
					'field_types' => array( NF_FU_File_Uploads::TYPE ),
					'settings'    => array(
						array(
							'name'  => 'toggle',
							'type'  => 'toggle',
							'label' => __( 'Field', 'ninja-forms-uploads' ),
							'width' => 'full',
						),
					),
				);
			}
		}

		$this->_settings = array_merge( $this->_settings, $settings );
	}

	/**
	 * Process the upload to the service for those files selected in the action
	 *
	 * @param array $file
	 *
	 * @return array|NF_Database_Models_Field
	 */
	protected function handle( $file, $service ) {
		$file['data'] = NF_File_Uploads()->externals->get( $service )->process_upload( $file['data'] );

		return $file;
	}

	/**
	 * Process the upload to the service for those files selected in the action
	 *
	 * @param array $action_settings
	 * @param int   $form_id
	 * @param array $data
	 *
	 * @return array
	 */
	public function process( $action_settings, $form_id, $data ) {
		$services = NF_File_Uploads()->externals->get_services();

		foreach ( $data['fields'] as $key => $field ) {
			if ( NF_FU_File_Uploads::TYPE !== $field['type'] ) {
				continue;
			}

			foreach ( $services as $service ) {
				$field_key = 'field_list_' . $service . '-' . $field['key'];

				if ( ! isset( $action_settings[ $field_key ] ) || 1 != $action_settings[ $field_key ] ) {
					continue;
				}

				foreach ( $field['files'] as $files_key => $file ) {
					$field['files'][ $files_key ] = $this->handle( $file, $service );
				}

				$data['fields'][ $key ] = $field;
			}
		}

		return $data;
	}
}