<?php

global $w2dc_category_search_widget_params;
$w2dc_category_search_widget_params = array(
);

class w2dc_category_search_widget extends w2dc_widget {

	public function __construct() {
		global $w2dc_instance, $w2dc_category_search_widget_params;

		parent::__construct(
				'w2dc_category_search_shortcode_widget',
				__('Directory - category page search', 'W2DC')
		);

		$this->convertParams($w2dc_category_search_widget_params);
	}
	
	public function render_widget($instance, $args) {
		global $w2dc_instance;
		
		if (empty($instance['category']) && !w2dc_iscategory()) {
			return false;
		}

		if (isset($instance['title'])) {
			$title = apply_filters('widget_title', $instance['title']);
		}
			
		echo $args['before_widget'];
		if (!empty($title)) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo '<div class="w2dc-content w2dc-widget w2dc-category-search-widget">';
		$controller = new w2dc_category_search_controller();
		$controller->init($instance);
		echo $controller->display();
		echo '</div>';
		echo $args['after_widget'];
	}
}

?>