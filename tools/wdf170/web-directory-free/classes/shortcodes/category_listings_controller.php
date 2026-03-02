<?php 

/**
 *  [webdirectory-category-listings] shortcode
 *
 *
 */
class w2dc_category_listings_controller extends w2dc_frontend_controller {
	public $controller;

	public function init($args = array()) {
		
		parent::init($args);

		$this->args = $args;
		
		if ($shortcode_controller = w2dc_getShortcodeController()) {
			if ($shortcode_controller->is_category) {
				$this->controller = $shortcode_controller;
			}
		}
		
		apply_filters('w2dc_category_listings_controller_construct', $this);
	}
	
	public function display() {
		
		if ($this->controller) {
			ob_start();
			
			w2dc_renderTemplate('frontend/listings_block.tpl.php', array('frontend_controller' => $this->controller));
		
			return ob_get_clean();
		}
	}
}

?>