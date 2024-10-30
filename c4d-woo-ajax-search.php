<?php
/*
Plugin Name: C4D Woocommerce Ajax Search
Plugin URI: http://coffee4dev.com/
Description: Ajax Search Product for Woocomemerce. Please install C4D Plugin Manager and Redux Framework to enable all features.
Author: Coffee4dev.com
Author URI: http://coffee4dev.com/
Text Domain: c4d-ajax-search
Version: 2.0.1
*/

define('C4DAJAXSEARCH_PLUGIN_URI', plugins_url('', __FILE__));
define('C4DAJAXSEARCH_PREFIX', 'c4d-woo-ajax-search-');
add_action('wp_enqueue_scripts', 'c4d_ajax_search_safely_add_stylesheet_to_frontsite');
add_action('wp_ajax_c4d_woo_ajax_search', 'c4d_woo_ajax_search_products');
add_action('wp_ajax_nopriv_c4d_woo_ajax_search', 'c4d_woo_ajax_search_products');
add_filter('get_product_search_form', 'c4d_woo_ajax_search_wrap');
add_shortcode('c4d-woo-ajax-search-form', 'c4d_woo_ajax_search_form');
add_filter( 'plugin_row_meta', 'c4d_woo_ajax_search_plugin_row_meta', 10, 2 );
add_filter('widget_text', 'do_shortcode');

function c4d_woo_ajax_search_plugin_row_meta( $links, $file ) {
    if ( strpos( $file, basename(__FILE__) ) !== false ) {
        $new_links = array(
            'visit' => '<a href="http://coffee4dev.com">Visit Plugin Site</<a>',
            'forum' => '<a href="http://coffee4dev.com/forums/">Forum</<a>',
            'redux' => '<a href="https://wordpress.org/plugins/redux-framework/">Redux Framework</<a>',
            'c4dpluginmanager' => '<a href="https://wordpress.org/plugins/c4d-plugin-manager/">C4D Plugin Manager</a>'
        );
        
        $links = array_merge( $links, $new_links );
    }
    
    return $links;
}

function c4d_woo_ajax_search_wrap($content){
	return '<div class="c4d-woo-ajax-search">'.$content.'</div>';
}

function c4d_ajax_search_safely_add_stylesheet_to_frontsite( $page ) {
	if(!defined('C4DPLUGINMANAGER_OFF_JS_CSS')) {
		wp_enqueue_style( 'c4d-woo-ajax-search-frontsite-style', C4DAJAXSEARCH_PLUGIN_URI.'/assets/default.css' );
		wp_enqueue_script( 'c4d-woo-ajax-search-frontsite-plugin-js', C4DAJAXSEARCH_PLUGIN_URI.'/assets/default.js', array( 'jquery' ), false, true ); 
	}
    wp_localize_script( 'jquery', 'c4d_woo_ajax_search',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

function c4d_woo_ajax_search_form($params) {
	return get_product_search_form(false);
}

function c4d_woo_ajax_search_products() {
	$transient_enabled  = true;
    $transient_duration = 12;
	$search_keyword =  esc_sql($_REQUEST['s']);
    $suggestions   = array();
	$transient_name = C4DAJAXSEARCH_PREFIX . $search_keyword;
	
	if (!$search_keyword) {
		echo json_encode( $suggestions ); die();
	}
	if ( false === ( $suggestions = get_transient( $transient_name ) ) ) {
        $args = array(
	        's'                   => $search_keyword,
	        'post_type'           => 'product',
	        'orderby'   		=> 'date',
        	'order'     		=> 'desc',
	        'post_status'       => 'publish',
	        'ignore_sticky_posts' => 1,
	        'posts_per_page'      => 5,
	        'suppress_filters'    => false,
	        'meta_query'          => array(
		        array(
			        'key'     => '_visibility',
			        'value'   => array( 'search', 'visible' ),
			        'compare' => 'IN'
		        )
	        )
        );

        // if ( isset( $_REQUEST['product_cat'] ) ) {
	       //  $args['tax_query'] = array(
		      //   'relation' => 'AND',
		      //   array(
			     //    'taxonomy' => 'product_cat',
			     //    'field'    => 'slug',
			     //    'terms'    => esc_sql($_REQUEST['product_cat'])
		      //   )
	       //  );
        // }

        $products = get_posts( $args );
        
        if ( ! empty( $products ) ) {
	        foreach ( $products as $post ) {
	        	$product = wc_get_product( $post );
		        $suggestions[] = array(
			        'id'    => $post->ID,
			        'img'	=> wp_get_attachment_url( get_post_thumbnail_id($post->ID), 'thumbnail' ),
			        'title' => strip_tags( $post->post_title ),
			        'url'   => get_permalink($post->ID),
			        'price' => $product->get_price_html()
		        );
	        }
        } else {
	        $suggestions[] = array(
		        'id'    => - 1,
		        'title' => __( 'No results', 'c4d-ajax-search' ),
		        'url'   => '',
	        );
        }
        wp_reset_postdata();
		set_transient( $transient_name, $suggestions, $transient_duration * HOUR_IN_SECONDS );
    }

    echo json_encode( $suggestions );
    die();
}