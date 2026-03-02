<?php 

/**
 *  [webdirectory-category-search] shortcode
 *
 *
 */
class w2dc_category_search_controller extends w2dc_frontend_controller {
	public $controller;

	public function init($args = array()) {
		
		parent::init($args);
		
		$this->args = $args;
		
		if ($shortcode_controller = w2dc_getShortcodeController()) {
			if ($shortcode_controller->is_category) {
				$this->controller = $shortcode_controller;
			}
		}
		
		apply_filters('w2dc_category_search_controller_construct', $this);
	}
	
	public function display() {
		
		if ($this->controller && $this->controller->search_form) {
			ob_start();
			
			$this->controller->search_form->display();
		
			return ob_get_clean();
		}
	}
}

?>