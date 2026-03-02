<?php

add_action('wp_footer', 'w2dc_elementor_support_wp_footer');
function w2dc_elementor_support_wp_footer() {
	if (!defined('ELEMENTOR_VERSION')) {
		return;
	}
	?>
	<script>
		jQuery(function($) {
			var interval = setInterval(function() {
				if (typeof elementorFrontend != 'undefined' && typeof elementorFrontend.hooks != 'undefined') {
					elementorFrontend.hooks.addAction('frontend/element_ready/global', function(el) {
						if (el.data("widget_type") == 'map.default' && typeof w2dc_load_maps != 'undefined') {
							for (var i=0; i<w2dc_map_markers_attrs_array.length; i++) {
								w2dc_load_map(i);
							}
						}
					});

					clearInterval(interval);
				}
			}, 100);
		});
	</script>
	<?php
}


// ---------------------- Directory pages -----------------------------------------------------------

global $w2dc_directory_elements_list;
$w2dc_directory_elements_list = array(
		'directory',
);

/**
 * add pages built by Elementor,
 * w2dc_getAllDirectoryPages() function from functions.php
 *
 */
add_filter('w2dc_get_all_directory_pages', 'w2dc_elementor_directory_pages');
function w2dc_elementor_directory_pages($directory_pages) {
	global $wpdb, $w2dc_instance, $w2dc_directory_elements_list;

	if (!defined('ELEMENTOR_VERSION')) {
		return $directory_pages;
	}

	foreach ($w2dc_directory_elements_list AS $el) {
		$sql_or[] = "pm.meta_value LIKE '%\"widgetType\":\"" . $el . "\"%'";
	}

	$elem_directory_pages = $wpdb->get_results("
			SELECT p.ID, pm.meta_value FROM {$wpdb->posts} AS p
			LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
			WHERE (pm.meta_key = '_elementor_data' AND " . implode(" OR ", $sql_or) . ") AND p.post_status = 'publish' AND p.post_type = 'page'", ARRAY_A);

	foreach ($elem_directory_pages AS $row) {
		$post_id = $row['ID'];

		$directory_pages[] = array(
				'id' => $post_id,
				'slug' => get_post($post_id)->post_name,
		);
	}

	return $directory_pages;
}

add_filter('w2dc_get_directory_of_page', 'w2dc_elementor_get_directory_of_page', 10, 2);
function w2dc_elementor_get_directory_of_page($current_directory, $page_id) {
	global $wpdb, $w2dc_instance;
	
	if (!defined('ELEMENTOR_VERSION')) {
		return $current_directory;
	}
	
	$elementor_data = get_post_meta($page_id, '_elementor_data' ,true);

	if (is_string($elementor_data) && !empty($elementor_data)) {
		$elementor_data = json_decode($elementor_data, true);
		
		if ($elementor_data) {
				
			$widget = w2dc_elementor_find_directory_elem($elementor_data);
			
			if ($widget) {
				
				if (isset($widget['settings']['wp'])) {
					$widget_settings = $widget['settings']['wp'];
				} else {
					$widget_settings = $widget['settings'];
				}
				
				if (!empty($widget_settings['directories'])) {
					$directory_id = $widget_settings['directories'];
				} else {
					$directory_id = $w2dc_instance->directories->getDefaultDirectory()->id;
				}
				
				$current_directory = $w2dc_instance->directories->getDirectoryById($directory_id);
			}
		}
	}

	return $current_directory;
}

/**
 * this will give complete URL of directory after current Directory was loaded,
 * will run in 'wp' hook from directories.php
 */
add_filter('w2dc_get_directory_url_of_page', 'w2dc_elementor_get_directory_url_of_page', 10, 3);
function w2dc_elementor_get_directory_url_of_page($current_page_url, $page_id, $directory_id) {
	global $wpdb, $w2dc_instance;
	
	if (!defined('ELEMENTOR_VERSION')) {
		return $current_page_url;
	}
	
	$elementor_data = get_post_meta($page_id, '_elementor_data' ,true);

	if (is_string($elementor_data) && !empty($elementor_data)) {
		$elementor_data = json_decode($elementor_data, true);
		
		if ($elementor_data) {
				
			$widget = w2dc_elementor_find_directory_elem($elementor_data);
			
			if ($widget) {
				
				if (isset($widget['settings']['wp'])) {
					$widget_settings = $widget['settings']['wp'];
				} else {
					$widget_settings = $widget['settings'];
				}
				
				if (!empty($widget_settings['directories'])) {
					$page_directory_id = $widget_settings['directories'];
				} else {
					$page_directory_id = $w2dc_instance->directories->getDefaultDirectory()->id;
				}
				
				if ($directory_id == $page_directory_id) {
					$current_page_url = get_permalink($page_id);
				}
			}
		}
	}

	return $current_page_url;
}

function w2dc_elementor_find_directory_elem($elementor_data) {

	global $w2dc_elementor_directory_element_widget, $w2dc_directory_elements_list;

	$w2dc_elementor_directory_element_widget = false;

	array_map(
	function($item) use ($w2dc_directory_elements_list) { return _w2dc_elementor_find_directory_elem($item, $w2dc_directory_elements_list); },
	$elementor_data
	);

	return $w2dc_elementor_directory_element_widget;
}

function _w2dc_elementor_find_directory_elem($item, $elements_list) {
	global $w2dc_elementor_directory_element_widget;

	if (is_array($item)) {
		if (!isset($item['widgetType']) || !in_array($item['widgetType'], $elements_list)) {
			array_map(
			function($item) use ($elements_list) { return _w2dc_elementor_find_directory_elem($item, $elements_list); },
			$item
			);
		} else {
			$w2dc_elementor_directory_element_widget = $item;
		}
	}
}

/**
 * load directory controller if the page contains directory widget,
 * is used when Imitation mode enabled
 */
add_action('w2dc_load_frontend_controllers', 'w2dc_elementor_load_directory_controllers');
function w2dc_elementor_load_directory_controllers($post) {
	global $w2dc_instance;

	if (!empty($post->ID)) {
		$post_id = $post->ID;
		$post_meta = get_post_meta($post_id, '_elementor_data', true);

		if (is_string($post_meta) && !empty($post_meta)) {
				
			$elementor_data = json_decode($post_meta, true);

			if ($elementor_data) {

				$widget = w2dc_elementor_find_directory_elem($elementor_data);

				if ($widget) {

					if (isset($widget['settings']['wp'])) {
						$widget_settings = $widget['settings']['wp'];
					} else {
						$widget_settings = $widget['settings'];
					}

					if (!empty($widget_settings['directories'])) {
						$directory_id = $widget_settings['directories'];
					} else {
						$directory_id = $w2dc_instance->directories->getDefaultDirectory()->id;
					}
						
					$settings = array_merge(array(
							'directories' => $directory_id,
					), $widget_settings);

					$controller = new w2dc_directory_controller();
					$controller->init($settings, W2DC_MAIN_SHORTCODE);

					$w2dc_instance->frontend_controllers[W2DC_MAIN_SHORTCODE][] = $controller;
				}
			}
		}
	}
}


// ------------------ Listing single page -----------------------------------------------------------

global $w2dc_listing_elements_list;
$w2dc_listing_elements_list = array(
		'listing_page',
		'listing_header',
		'listing_gallery',
		'listing_map',
		'listing_videos',
		'listing_contact',
		'listing_report',
		'listing_comments',
		'listing_fields',
);

/**
 * returns content of the listing page built by Elementor,
 * used in the_content filter in w2dc.php
 *
 */
add_action('w2dc_the_content_listing_page', 'w2dc_elementor_listing_page_the_content');
function w2dc_elementor_listing_page_the_content($page_content) {
	global $w2dc_instance;
	
	if (!defined('ELEMENTOR_VERSION') || empty($w2dc_instance->listing_page_id)) {
		return $page_content;
	}

	$elementor_content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($w2dc_instance->listing_page_id);

	if (empty($elementor_content) && $page_content) {
		return $page_content;
	} else {
		return $elementor_content;
	}
}

/**
 * add pages built by Elementor,
 * w2dc_getAllListingPages() function from functions.php
 * 
 */
add_filter('w2dc_get_all_listing_pages', 'w2dc_elementor_listing_pages');
function w2dc_elementor_listing_pages($listing_pages) {
	global $wpdb, $w2dc_instance, $w2dc_listing_elements_list;
	
	if (!defined('ELEMENTOR_VERSION')) {
		return $listing_pages;
	}
	
	foreach ($w2dc_listing_elements_list AS $el) {
		$sql_or[] = "pm.meta_value LIKE '%\"widgetType\":\"" . $el . "\"%'";
	}
	
	$elem_listing_pages = $wpdb->get_results("
		SELECT p.ID, pm.meta_value FROM {$wpdb->posts} AS p
		LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
		WHERE (pm.meta_key = '_elementor_data' AND " . implode(" OR ", $sql_or) . ") AND p.post_status = 'publish' AND p.post_type = 'page'", ARRAY_A);
	
	foreach ($elem_listing_pages AS $row) {
		$post_id = $row['ID'];
		$elementor_data = json_decode($row['meta_value'], true);
		
		if ($elementor_data) {
			
			$widget = w2dc_elementor_find_listing_elem($elementor_data, $w2dc_listing_elements_list[0]); // find main 'listing_page' widget
			
			if (isset($widget['settings']['wp'])) {
				$widget_settings = $widget['settings']['wp'];
			} else {
				$widget_settings = $widget['settings'];
			}
			
			if (!empty($widget_settings['directory'])) {
				$directory_id = $widget_settings['directory'];
				
				$listing_pages[$directory_id] = $post_id;
			} else {
				$listing_pages[$w2dc_instance->directories->getDefaultDirectory()->id] = $post_id;
			}
		}
	}
	
	return $listing_pages;
}

/**
 * detect is page contains any listing Elementor widget
 * 
 */
add_filter('w2dc_is_listing_elements_on_page', 'w2dc_is_listing_elements_on_page');
function w2dc_is_listing_elements_on_page($is_on_page) {

	if (!defined('ELEMENTOR_VERSION')) {
		return $is_on_page;
	}
	
	global $w2dc_instance;
	
	$elementor_data = get_post_meta($w2dc_instance->listing_page_id, '_elementor_data' ,true);

	if (is_string($elementor_data) && !empty($elementor_data)) {
		$elementor_data = json_decode($elementor_data, true);
	}

	if ($elementor_data) {
		$is_on_page = w2dc_elementor_find_listing_elem($elementor_data);
	}

	return $is_on_page;
}

function w2dc_elementor_find_listing_elem($elementor_data, $element = false) {
	
	global $w2dc_elementor_listing_element_widget, $w2dc_listing_elements_list;
	
	$w2dc_elementor_listing_element_widget = false;
	
	$elements_list = array();
	
	if ($element) {
		$elements_list = array($element);
	} else {
		$elements_list = $w2dc_listing_elements_list;
	}
	
	array_map(
			function($item) use ($elements_list) { return _w2dc_elementor_find_listing_elem($item, $elements_list); },
			$elementor_data
	);
	
	return $w2dc_elementor_listing_element_widget;
}
/**
 * recursion needed here, as widgets can be nested in each other
 * 
 */
function _w2dc_elementor_find_listing_elem($item, $elements_list) {
	global $w2dc_elementor_listing_element_widget;

	if (is_array($item)) {
		if (!isset($item['widgetType']) || !in_array($item['widgetType'], $elements_list)) {
			
			array_map(
					function($item) use ($elements_list) { return _w2dc_elementor_find_listing_elem($item, $elements_list); },
					$item
			);
		} else {
			$w2dc_elementor_listing_element_widget = $item;
		}
	}
}

/**
 * load listing controller if the page contains listing page or any listing Elementor widget,
 * is used when Imitation mode enabled
 * 
 */
add_action('w2dc_load_frontend_controllers', 'w2dc_elementor_load_directory_post_controllers');
function w2dc_elementor_load_directory_post_controllers($post) {
	global $w2dc_instance, $w2dc_listing_elements_list;
	
	if (!empty($post->ID)) {
		$post_id = $post->ID;
		$post_meta = get_post_meta($post_id, '_elementor_data', true);
		
		if (is_string($post_meta) && !empty($post_meta)) {
			
			$elementor_data = json_decode($post_meta, true);
	
			if ($elementor_data) {
				
				$widget = w2dc_elementor_find_listing_elem($elementor_data, $w2dc_listing_elements_list[0]); // find main 'listing_page' widget
				
				if ($widget) {
					
					if (isset($widget['settings']['wp'])) {
						$widget_settings = $widget['settings']['wp'];
					} else {
						$widget_settings = $widget['settings'];
					}
	
					if (!empty($widget_settings['directory'])) {
						$directory_id = $widget_settings['directory'];
					} else {
						$directory_id = $w2dc_instance->directories->getDefaultDirectory()->id;
					}
					
					$settings = array_merge(array(
							'directory' => $directory_id,
					), $widget_settings);
	
					$controller = new w2dc_directory_controller();
					$controller->init($settings, W2DC_LISTING_SHORTCODE);
	
					$w2dc_instance->frontend_controllers[W2DC_LISTING_SHORTCODE][] = $controller;
				}
			}
		}
	}
}


// ------------------ Category page -----------------------------------------------------------

global $w2dc_category_elements_list;
$w2dc_category_elements_list = array(
		'category_page',
		'category_listings',
		'category_map',
		'category_search',
);

/**
 * get the settings of category page shortcode
 * 
 */
add_filter("w2dc_get_shortcode_atts_on_page", "w2dc_get_category_page_shortcode_atts_on_page", 10, 3);
function w2dc_get_category_page_shortcode_atts_on_page($atts, $shortcode, $page_id) {
	global $w2dc_category_elements_list;
	
	if ($shortcode == W2DC_CATEGORY_PAGE_SHORTCODE) {
		$elementor_data = get_post_meta($page_id, '_elementor_data' ,true);
		
		if (is_string($elementor_data) && !empty($elementor_data)) {
			$elementor_data = json_decode($elementor_data, true);
		}
		
		if ($elementor_data) {
			$widget = w2dc_elementor_find_category_elem($elementor_data, $w2dc_category_elements_list[0]);
			
			if (isset($widget['settings']['wp'])) {
				$atts = $widget['settings']['wp'];
			} else {
				$atts = $widget['settings'];
			}
		}
	}
	
	return $atts;
}

/**
 * returns content of the category page built by Elementor,
 * used in the_content filter in w2dc.php
 *
 */
add_action('w2dc_the_content_category_page', 'w2dc_elementor_category_page_the_content');
function w2dc_elementor_category_page_the_content($page_content) {
	global $w2dc_instance;
	
	if (!defined('ELEMENTOR_VERSION') || empty($w2dc_instance->category_page_id)) {
		return $page_content;
	}

	$elementor_content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($w2dc_instance->category_page_id);

	if (empty($elementor_content) && $page_content) {
		return $page_content;
	} else {
		return $elementor_content;
	}
}

/**
 * add pages built by Elementor,
 * w2dc_getAllCategoryPages() function from functions.php
 * 
 */
add_filter('w2dc_get_all_category_pages', 'w2dc_elementor_category_pages');
function w2dc_elementor_category_pages($category_pages) {
	global $wpdb, $w2dc_instance, $w2dc_category_elements_list;
	
	if (!defined('ELEMENTOR_VERSION')) {
		return $category_pages;
	}
	
	foreach ($w2dc_category_elements_list AS $el) {
		$sql_or[] = "pm.meta_value LIKE '%\"widgetType\":\"" . $el . "\"%'";
	}
	
	$elem_category_pages = $wpdb->get_results("
		SELECT p.ID, pm.meta_value FROM {$wpdb->posts} AS p
		LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
		WHERE (pm.meta_key = '_elementor_data' AND " . implode(" OR ", $sql_or) . ") AND p.post_status = 'publish' AND p.post_type = 'page'", ARRAY_A);
	
	foreach ($elem_category_pages AS $row) {
		$post_id = $row['ID'];
		$elementor_data = json_decode($row['meta_value'], true);
		
		if ($elementor_data) {
			
			$widget = w2dc_elementor_find_category_elem($elementor_data, $w2dc_category_elements_list[0]);
			
			if (isset($widget['settings']['wp'])) {
				$widget_settings = $widget['settings']['wp'];
			} else {
				$widget_settings = $widget['settings'];
			}
			
			if (!empty($widget_settings['category_id'])) {
				$category_id = $widget_settings['category_id'];
				
				$category_pages[$category_id] = $post_id;
			} else {
				$category_pages[0] = $post_id;
			}
		}
	}
	
	return $category_pages;
}

/**
 * detect is page contains any category Elementor widget
 * 
 */
add_filter('w2dc_is_category_elements_on_page', 'w2dc_is_category_elements_on_page');
function w2dc_is_category_elements_on_page($is_on_page) {

	if (!defined('ELEMENTOR_VERSION')) {
		return $is_on_page;
	}
	
	global $w2dc_instance;
	
	$elementor_data = get_post_meta($w2dc_instance->category_page_id, '_elementor_data' ,true);

	if (is_string($elementor_data) && !empty($elementor_data)) {
		$elementor_data = json_decode($elementor_data, true);
	}

	if ($elementor_data) {
		$is_on_page = w2dc_elementor_find_category_elem($elementor_data);
	}

	return $is_on_page;
}

function w2dc_elementor_find_category_elem($elementor_data, $element = false) {
	
	global $w2dc_elementor_category_element_widget, $w2dc_category_elements_list;
	
	$w2dc_elementor_category_element_widget = false;
	
	if ($element) {
		$elements_list = array($element);
	} else {
		$elements_list = $w2dc_category_elements_list;
	}
	
	array_map(
			function($item) use ($elements_list) { return _w2dc_elementor_find_category_elem($item, $elements_list); },
			$elementor_data
	);
	
	return $w2dc_elementor_category_element_widget;
}

/**
 * recursion needed here, as widgets can be nested in each other
 *
 */
function _w2dc_elementor_find_category_elem($item, $elements_list) {
	global $w2dc_elementor_category_element_widget;

	if (is_array($item)) {
		if (!isset($item['widgetType']) || !in_array($item['widgetType'], $elements_list)) {
			array_map(
					function($item) use ($elements_list) { return _w2dc_elementor_find_category_elem($item, $elements_list); },
					$item
			);
		} else {
			$w2dc_elementor_category_element_widget = $item;
		}
	}
}

/**
 * load category controller if the page contains category page or any category Elementor widget,
 * is used when Imitation mode enabled
 * 
 */
add_action('w2dc_load_frontend_controllers', 'w2dc_elementor_load_directory_category_controllers');
function w2dc_elementor_load_directory_category_controllers($post) {
	global $w2dc_instance, $w2dc_category_elements_list;
	
	if (!empty($post->ID)) {
		$post_id = $post->ID;
		$post_meta = get_post_meta($post_id, '_elementor_data', true);
		
		if (is_string($post_meta) && !empty($post_meta)) {
			
			$elementor_data = json_decode($post_meta, true);
	
			if ($elementor_data) {
				
				$widget = w2dc_elementor_find_category_elem($elementor_data, $w2dc_category_elements_list[0]);
				
				if ($widget) {
					
					if (isset($widget['settings']['wp'])) {
						$widget_settings = $widget['settings']['wp'];
					} else {
						$widget_settings = $widget['settings'];
					}
	
					if (!empty($widget_settings['category_id'])) {
						$category_id = $widget_settings['category_id'];
					} else {
						$category_id = 0;
					}
					
					$settings = array_merge(array(
							'category_id' => $category_id,
					), $widget_settings);
	
					$controller = new w2dc_directory_controller();
					$controller->init($settings, W2DC_CATEGORY_PAGE_SHORTCODE);
	
					$w2dc_instance->frontend_controllers[W2DC_CATEGORY_PAGE_SHORTCODE][] = $controller;
				}
			}
		}
	}
}



?>