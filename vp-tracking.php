<?php
/*
  Plugin Name: Viral Passion Tracking
  Description: Viral Passion Tracking.
  Version: 3.1
  Author: Viral Passion
  Text Domain: vpt
  Domain Path: /vp-t
*/  


defined( 'ABSPATH' ) or die();

include('fbct.php');
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = PucFactory::buildUpdateChecker(
    'http://assets.vpsites.eu/my_plugins/vp-tracking-versions/update.json',
    __FILE__
);
/*run plugin */

add_action('wp_head', 'VP_echo_googleAnalytics');
add_action('wp_head', 'VP_echo_tagManagerCode');
add_action('wp_head', 'VP_echo_facebookPixel');
add_action('wp_head', 'VP_echo_trackEvents');
//add_action('admin_head', 'VP_admin_echo_googleAnalytics');



function VP_echo_googleAnalytics(){
	settings_fields( 'VP_tracking_settings-group' );
	if(esc_attr( get_option('ga_id') )!=""){
		echo ("<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		
		  ga('create', '".esc_attr( get_option('ga_id'))."', 'auto');
		  ga('require', 'displayfeatures');
		  ga('send', 'pageview');
		
		</script>");
	}
}

function VP_echo_facebookPixel(){
	settings_fields( 'VP_tracking_settings-group' );
	if(esc_attr( get_option('fb_pixel_id') )!=""){
	echo ("<script>
					!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
					n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
					n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
					t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
					document,'script','//connect.facebook.net/en_US/fbevents.js');
					fbq('init', '".esc_attr( get_option('fb_pixel_id') )."');
					fbq('track', 'PageView');
					</script>
				");
				if ( class_exists( 'WooCommerce' ) )add_action( 'woocommerce_thankyou', 'VP_fb_Purchase_thankutracking' );
				if ( class_exists( 'WooCommerce' ) )add_action('wp_footer','VP_ViewContent_addToCart_Pixel');
	}
}

function VP_admin_echo_googleAnalytics(){
	settings_fields( 'VP_tracking_settings-group' );
	if((esc_attr( get_option('ga_id') )!="")&&(esc_attr( get_option('enable_backend'))=='1'))echo ("<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		
		  ga('create', '".esc_attr( get_option('ga_id'))."', 'auto');
		  ga('require', 'displayfeatures');
		  ga('send', 'pageview');
		
		</script>");
		
}

function VP_admin_echo_facebookPixel(){
	settings_fields( 'VP_tracking_settings-group' );
	if((esc_attr( get_option('fb_pixel_id') )!="")&&(esc_attr( get_option('enable_backend'))=='1'))echo ("<script>
					!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
					n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
					n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
					t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
					document,'script','//connect.facebook.net/en_US/fbevents.js');
					fbq('init', '".esc_attr( get_option('fb_pixel_id') )."');
					fbq('track', 'PageView');
					</script>
				");
}


function VP_echo_trackEvents(){
	echo("<script>
	function vp_track_lead()
	{
		if (typeof fbq == 'function') fbq('track', 'Lead');
		if (typeof ga == 'function') ga('send', 'event', 'button', 'lead','to_product');
	}  
	</script>");
}


function VP_ViewContent_addToCart_Pixel(){
	global $product;
	global $woocommerce; 
	
	if ($product){/*On single product*/
		$output="<script> window._fbq = window._fbq || []; window._fbq.push(['track', 'ViewContent', { content_ids:['".$product->id."'], content_type: 'product' }]);</script>";
		echo $output;
	}
	/* Cart items */
	$items_ids_string='';
	$count=0;
	$items = $woocommerce->cart->get_cart();
		foreach($items as $item) {
	   		$count++;
		    $product_name = $item['name'];
		    $product_id = $item['product_id'];
		    $product_variation_id = $item['variation_id'];
		    
		    $items_ids_string .= "'$product_id'";
		    if($count != count($items)) $items_ids_string .=", ";
		}
		$cart_total = $woocommerce->cart->subtotal;
		if($count>0) echo ("<script> fbq('track', 'AddToCart', {content_name: 'Shopping Cart', content_ids: [$items_ids_string],content_type: 'product',value: $cart_total,currency: 'EUR'}); </script>");
}

 
function VP_fb_Purchase_thankutracking( $order_id ) {
   $order = new WC_Order( $order_id );
   $order_total = $order->get_total();
   $items = $order->get_items();
   $items_ids_string='';
   $count=0;
   foreach ( $items as $item ) {
   		$count++;
	    $product_name = $item['name'];
	    $product_id = $item['product_id'];
	    $product_variation_id = $item['variation_id'];
	    
	    $items_ids_string .= "'$product_id'";
	    if($count != count($items)) $items_ids_string .=", ";
    }
   echo ("<script> fbq('track', 'Purchase', {content_ids: [$items_ids_string],content_type: 'product',value: ".$order_total.",currency: 'EUR'}); </script>");
}






function VP_echo_tagManagerCode(){
	settings_fields( 'VP_tracking_settings-group' );
	$tagmanager_code = esc_attr( get_option('gtm_id') );
	if ($tagmanager_code!="")echo ("<!-- Google Tag Manager -->
	<noscript><iframe src='//www.googletagmanager.com/ns.html?id=".$tagmanager_code."'
	height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','".$tagmanager_code."');</script>
	<!-- End Google Tag Manager -->");
	
	
}


/* tsar */

if ( class_exists( 'WooCommerce' ) ) add_action( 'woocommerce_thankyou', 'VP_gtm_purchase_tracking' );
 
function VP_gtm_purchase_tracking( $order_id ) {
 
// Lets grab the order
$order = new WC_Order( $order_id );
 
/**
 * Put your tracking code here
 * You can get the order total etc e.g. $order->get_order_total();
 **/
 ?>
 <script>
	 
	 
	 
	dataLayer.push({
	  'ecommerce': {
	    'purchase': {
	      'actionField': {
	        'id': '<?php echo $order->get_order_number(); ?>',                         // Transaction ID. Required for purchases and refunds.
	        'affiliation': '<?php echo get_option( "blogname" );?>',
	        'tax':'<?php echo $order->get_total_tax(); ?>',
	        'revenue': '<?php echo $order->get_total(); ?>',                     // Total transaction value (incl. tax and shipping)
	        'shipping': '<?php echo $order->get_total_shipping();?>'
	      },
	      'products': [
	      
		  	<?php
			//Item Details
				if ( sizeof( $order->get_items() ) > 0 ) {
					foreach( $order->get_items() as $item ) {
						$product_cats = get_the_terms( $item["product_id"], 'product_cat' );
							if ($product_cats) { 
								$cat = $product_cats[0];
							} ?>
							
				{
					'id': '<?php echo $item['product_id'];?>',
					'sku': '<?php echo get_post_meta($item["product_id"], '_sku', true);?>',
					'name': '<?php echo $item['name'];?>',
					'category': '<?php echo $cat->name;?>',
					'price': '<?php echo $item['line_subtotal'];?>',
					'quantity': '<?php echo $item['qty'];?>',
				},
				
					<?php
					}	
				} 
			?>
			
			]
	    }
	  }
	});
</script>
 <?php
}


/* end tsar */

/* track backend orders */
/* Under development */
//if ( class_exists( 'WooCommerce' ) )add_action( 'wp_insert_post', 'vpt_newOrder' );

function vpt_newOrder($post_ID,$post,$update){
	$slug = 'shop_order';
    //if ( $slug != $post->post_type ) return;
	update_post_meta( $post_ID, 'show_tracking_code', 1 );
}
//add_action('admin_init', 'vp_check_new_order');
function vp_check_new_order() {
//post=20717&action=edit	
    if (isset($_GET['post'])&&isset($_GET['action'])){
        if ($_GET['action'] == "edit") {
        	if(get_post_meta( $_GET['post'], 'show_tracking_code', true )==1){
        		update_post_meta($_GET['post'], 'show_tracking_code', 0);
	        	$order = new WC_Order($_GET['post']);
				$order_total = $order->get_total();
				$items = $order->get_items();
				$items_ids_string='';
				$count=0;
				foreach ( $items as $item ) {
			   		$count++;
				    $product_name = $item['name'];
				    $product_id = $item['product_id'];
				    $product_variation_id = $item['variation_id'];
				    
				    $items_ids_string .= "'$product_id'";
				    if($count != count($items)) $items_ids_string .=", ";
				}
				settings_fields( 'VP_tracking_settings-group' );
				
				
				/* Google Tag Manager Track Purchase */
				settings_fields( 'VP_tracking_settings-group' );
				$tagmanager_code = esc_attr( get_option('gtm_id') );
				if ($tagmanager_code!=""):
				
					echo ("<!-- Google Tag Manager -->
						<noscript><iframe src='//www.googletagmanager.com/ns.html?id=".$tagmanager_code."'
						height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>
						<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
						new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
						j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
						'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
						})(window,document,'script','dataLayer','".$tagmanager_code."');</script>
						<!-- End Google Tag Manager -->");
				
				?>
				<script>
				 
				 
				 
				dataLayer.push({
				  'ecommerce': {
				    'purchase': {
				      'actionField': {
				        'id': '<?php echo $order->get_order_number(); ?>',                         // Transaction ID. Required for purchases and refunds.
				        'affiliation': '<?php echo get_option( "blogname" );?>',
				        'tax':'<?php echo $order->get_total_tax(); ?>',
				        'revenue': '<?php echo $order->get_total(); ?>',                     // Total transaction value (incl. tax and shipping)
				        'shipping': '<?php echo $order->get_total_shipping();?>'
				      },
				      'products': [
				      
					  	<?php
						//Item Details
							if ( sizeof( $order->get_items() ) > 0 ) {
								foreach( $order->get_items() as $item ) {
									$product_cats = get_the_terms( $item["product_id"], 'product_cat' );
										if ($product_cats) { 
											$cat = $product_cats[0];
										} ?>
										
							{
								'id': '<?php echo $item['product_id'];?>',
								'sku': '<?php echo get_post_meta($item["product_id"], '_sku', true);?>',
								'name': '<?php echo $item['name'];?>',
								'category': '<?php echo $cat->name;?>',
								'price': '<?php echo $item['line_subtotal'];?>',
								'quantity': '<?php echo $item['qty'];?>',
							},
							
								<?php
								}	
							} 
						?>
						
						]
				    }
				  }
				});
				</script>
				<?php
				endif;
		   
		   }
        }
    }
}

/* end of backend orders */






// create custom plugin settings menu
add_action('admin_menu', 'vp_tracking');

function vp_tracking() {

	//create new top-level menu
	add_menu_page('VP Tracking', 'Tracking Settings', 'administrator', __FILE__, 'VP_tracking_settings_page','dashicons-share'  );

	//call register settings function
	add_action( 'admin_init', 'register_VP_tracking_settings' );
}


function register_VP_tracking_settings() {
	//register our settings
	register_setting( 'VP_tracking_settings-group', 'fb_pixel_id' );
	register_setting( 'VP_tracking_settings-group', 'ga_id' );
	register_setting( 'VP_tracking_settings-group', 'gtm_id' );
	register_setting( 'VP_tracking_settings-group', 'enable_backend' );
}

function VP_tracking_settings_page() {
?>
<div class="wrap">
<h2>Tracking Settings</h2>

<h3>Facebook Product Catalog Settings</h3>
<h4><strong>Feed Url: </strong><?php echo get_site_url(); ?>/fbpc.xml</h4> 
<p>To learn how to upload your feed visit <a href="https://www.facebook.com/business/help/1397294963910848">Facebook Help</a></p>

<form method="post" action="options.php">
    <?php settings_fields( 'VP_tracking_settings-group' ); ?>
    <?php do_settings_sections( 'VP_tracking_settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Facebook Pixel Id</th>
        <td><input type="text" name="fb_pixel_id" value="<?php echo esc_attr( get_option('fb_pixel_id') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Google Analytics Id</th>
        <td><input type="text" name="ga_id" value="<?php echo esc_attr( get_option('ga_id') ); ?>" /></td>
        </tr> 
        <tr valign="top">
        <th scope="row">Google Tag Manager</th>
        <td><input type="text" name="gtm_id" value="<?php echo esc_attr( get_option('gtm_id') ); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Track Backend Orders</th>
        <td><input name="enable_backend" type="checkbox" value="1" <?php checked( '1', get_option( 'enable_backend' ) ); ?> /></td>
        </tr>
        
        <!--tr valign="top">
        <th scope="row">Track Facebook Product Catalogue Actions<br/><span style="font-size:9px;color:gray;">You will be able to crate automate ads</span></th>
        <td><input name="enable_backend" type="checkbox" value="1" <?php checked( '1', get_option( 'enable_backend' ) ); ?> /></td>
        </tr-->

        
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php } ?>