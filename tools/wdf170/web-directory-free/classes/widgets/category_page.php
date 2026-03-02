<?php

/*
 * place shortcode [webdirectory-category-page] on a page, do not use widget, instead shortcode,
* then use category page widgets: listings, map, search
*
* */
global $w2dc_category_page_widget_params;
$w2dc_category_page_widget_params = array(
		array(
				'type' => 'textfield',
				'param_name' => 'category_id',
				'heading' => __("Enter specific category ID", "W2DC"),
		),
		array(
				'type' => 'formid',
				'param_name' => 'search_form_id',
				'heading' => esc_html__("Select search form for category page or leave default", "W2DC"),
		),
);

?>