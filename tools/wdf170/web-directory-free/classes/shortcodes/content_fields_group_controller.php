<?php 

/**
 *  [webdirectory-content-fields-group] shortcode
 *
 *
 */
class w2dc_content_fields_group_controller extends w2dc_frontend_controller {
	public $listing;
	
	public function init($args = array()) {
		global $w2dc_instance;
	
		parent::init($args);
	
		$shortcode_atts = array_merge(array(
				'listing' => '',
				'content_fields_group_id' => '',
				'classes' => '',
		), $args);
	
		$this->args = $shortcode_atts;
	
		if (empty($this->args['listing'])) {
			if ($shortcode_controller = w2dc_getShortcodeController()) {
				if ($shortcode_controller->is_single) {
					if ($shortcode_controller->is_listing) {
						$this->listing = $shortcode_controller->listing;
					}
				}
			}
		} else {
			$this->listing = w2dc_getListing($this->args['listing']);
		}
	
		apply_filters('w2dc_content_fields_group_controller_construct', $this);
	}
	
	public function display() {
		if ($this->listing && $this->args['content_fields_group_id']) {
			ob_start();
			
			echo '<div class="w2dc-content">';
			$this->listing->renderContentFieldsGroup($this->args['content_fields_group_id'], $this->args['classes']);
			echo '</div>';
			
			return ob_get_clean();
		}
	}
}

?>