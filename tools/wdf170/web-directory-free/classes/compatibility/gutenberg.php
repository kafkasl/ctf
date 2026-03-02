<?php

add_filter('w2dc_the_content_listing_page', 'w2dc_gutenberg_the_content_listing_page');
add_filter('w2dc_the_content_index_page', 'w2dc_gutenberg_the_content_listing_page');
function w2dc_gutenberg_the_content_listing_page($content) {
	
	if (has_blocks($content)) {
		$content = do_blocks($content);
	}
	
	return $content;
}

?>