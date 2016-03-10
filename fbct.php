<?php

add_action('parse_request', 'vp_pc_custom_url_handler');

function vp_pc_custom_url_handler() {
   if($_SERVER["REQUEST_URI"] == '/fbpc.xml') {
      vp_pc_createXml();
      die();
   }
}

function vp_pc_createXml(){
	header('Content-type: rss/xml; charset=utf-8');
	date_default_timezone_set('Europe/Athens');
	echo '<?xml version="1.0" encoding="UTF-8"?>
	<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
	<channel>
	<title>'. get_bloginfo('name') . '</title>
	<link>'. get_bloginfo('url') . '</link>';
	
	// WP_Query arguments
	$args = array (
		'post_type'              => array( 'product' ),
		'posts_per_page'         => '-1',
	);
	
	// The Query
	$prod_query = new WP_Query( $args );
	
	// The Loop
	if ( $prod_query->have_posts() ) {
		while ( $prod_query->have_posts() ) {
			$prod_query->the_post();
			
			global $woocommerce, $product, $woocommerce_loop;
			
			// do something
			$id = get_the_id();
			
			$sale_price = get_post_meta( get_the_ID(), '_price', true);
		    if ($sale_price == '') {
			    continue;
		    }
		    
		    $attributes = $product->get_attributes();
		    
		    $post_thumbnail_id = get_post_thumbnail_id( $id );	
			$var_stock_status = get_post_meta( $product->id, '_stock_status', true );
			
			if ($var_stock_status == 'instock' && $post_thumbnail_id) {
				//if ($custom_avail != 'Αναμένεται' || $custom_avail != 'Εξαντλήθηκε') {
				//echo '**break**';
				//continue;
				echo '<item>';
			    echo '<g:id>'. get_the_ID() .'</g:id>';
			    echo '<g:title>' . get_the_title() . '</g:title>';
	/*
			    if ( ! $post->post_excerpt ) {
				    $description = $post->post_content;
			    }
			    else {
				    $description = $post->post_excerpt;
			    }
	*/
			    $description = str_replace('[&hellip;]', '', get_the_excerpt());
			    $description = str_replace(array('&', '<'), array('&#x26;', '&#x3C;'), $description);
			    $description = str_replace('&nbsp;', '', $description);
			    echo '<g:description>'. esc_html($description) . '</g:description>';
			    echo '<g:link>'. get_permalink() .'</g:link>';
			    $image_link = wp_get_attachment_url( $post_thumbnail_id );
				echo '<g:image_link>'. $image_link .'</g:image_link>';
				$sku = $product->get_sku();
			    if ($sku == '') {
				    $sku = 'SKUERROR';
			    }
			    echo '<g:mpn>'. $sku . '</g:mpn>';
			    echo '<g:condition>new</g:condition>';
			    $availability = '';
				if ($var_stock_status = 'instock') {
					$availability = 'in stock';
				}
				else {
					$availability = 'out of stock';
				}
			    
				echo '<g:availability> '. $availability .'</g:availability>';
				
				//echo  '<g:price>'. $price .' EUR</g:price>';
			    
			    echo  '<g:price>'. $sale_price .' EUR</g:price>';
			    
			    
			    
			    
			    echo '</item>';
				//}
				
				$counter++;
			}
		}
	} else {
		// no posts found
	}
	
	// Restore original Post Data
	wp_reset_postdata();
	
	//
	//wp_reset_postdata();
	echo '</channel>'; 
	echo '</rss>';
	echo $counter;
}