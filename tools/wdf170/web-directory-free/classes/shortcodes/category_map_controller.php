<?php 

/**
 *  [webdirectory-category-map] shortcode
 *
 *
 */
class w2dc_category_map_controller extends w2dc_frontend_controller {
	public $controller;

	public function init($args = array()) {
		
		parent::init($args);

		$this->args = $args;
		
		if ($shortcode_controller = w2dc_getShortcodeController()) {
			if ($shortcode_controller->is_category) {
				$this->controller = $shortcode_controller;
			}
		}
		
		apply_filters('w2dc_category_map_controller_construct', $this);
	}
	
	public function display() {
		
		if ($this->controller && $this->controller->map) {
			
			ob_start();
			
			$this->controller->map->display(
					array(
							'show_directions' => false,
							'static_image' => false,
							'enable_radius_circle' => get_option('w2dc_enable_radius_search_circle'),
							'enable_clusters' => get_option('w2dc_enable_clusters'),
							'show_summary_button' => true,
							'show_readmore_button' => true,
							'width' => false,
							'height' => get_option('w2dc_default_map_height'),
							'sticky_scroll' => false,
							'sticky_scroll_toppadding' => 10,
							'map_style' => w2dc_getSelectedMapStyleName(),
							'search_form' => get_option('w2dc_search_on_map'),
							'draw_panel' => get_option('w2dc_enable_draw_panel'),
							'custom_home' => false,
							'enable_full_screen' => get_option('w2dc_enable_full_screen'),
							'enable_wheel_zoom' => get_option('w2dc_enable_wheel_zoom'),
							'enable_dragging_touchscreens' => get_option('w2dc_enable_dragging_touchscreens'),
							'center_map_onclick' => get_option('w2dc_center_map_onclick'),
					)
			);
		
			return ob_get_clean();
		}
	}
}

?>