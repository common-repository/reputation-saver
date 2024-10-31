<?php
/*
	Plugin Name: Reputation Saver
	Version: 1.0
	Description: This plugin enables users to create a form and social sahring of rating of their site.
	Author: Charlotteseoofgreenville
	Author URI: http://www.reputationsaver.io/ 
*/

/* Create page for plugin settings */
add_action( 'admin_menu', 'rs_register_custom_menu_page' );
function rs_register_custom_menu_page(){
    add_menu_page( 
        'Reputation Saver Settings',
        'Reputation Saver Settings',
        'manage_options',
        'rs-settings',
        'rs_register_custom_menu_page_cb'
    ); 
}

// add pro links to the plugin entry in the plugins actions
add_filter('plugin_action_links', 'rs_plugin_action_links', 10, 2);
function rs_plugin_action_links($links, $file) {
    static $this_plugin;
 
    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }
 
    // check to make sure we are on the correct plugin
    if ($file == $this_plugin) {
	
		// link to what ever you want
        $links[] = '<a style="font-weight: bold; color: green;" href="https://www.reputationsaver.io/product/online-reputation-management-software/" target="_BLANK">Upgrade To Pro</a>';
    }
 
    return $links;
}

// register settings
add_action( 'admin_init', 'rs_register_setting' );
function rs_register_setting() {
    register_setting( 'rs_options', 'rs_options', 'rs_options_callback' ); 
    register_setting( 'rs_options_social', 'rs_options_social', 'rs_options_callback' ); 
}

function rs_options_callback($a = array()){
	return $a;
}

/* Add the media uploader script */
add_action('admin_enqueue_scripts', 'rs_media_lib_uploader_enqueue');
function rs_media_lib_uploader_enqueue() {
	wp_enqueue_media();
    wp_enqueue_style( 'wp-color-picker' ); 
	wp_register_script( 'rs-media-lib-uploader-js', plugins_url( 'rs-media-lib-uploader.js' , __FILE__ ), array('jquery', 'wp-color-picker') );
	wp_enqueue_script( 'rs-media-lib-uploader-js' );
}

// add page on plugin load
register_activation_hook( __FILE__, 'rs_plugin_activate' );
function rs_plugin_activate() {

    $page = array(
				  'post_title'    => wp_strip_all_tags( 'Reputation Saver' ),
				  'post_content'  => '[rs-reputation-saver-form]',
				  'post_type'  => 'page',
				  'post_status'   => 'publish',
				  'post_author'   => 1
				);
 
 	$p = get_page_by_title('Reputation Saver');
 	if(!$p){
		// Insert the post into the database
		$page_ID = wp_insert_post( $page );
		update_option('rs_page', $page_ID);
 	}

}

add_filter( 'template_include', 'rs_page_template', 99 );
function rs_page_template( $template ) {
	global $post;
	if(get_option('rs_page') == $post->ID){
		$new_template = dirname( __FILE__ ) . '/template-reputation-saver.php';
		if ( '' != $new_template ) {
			return $new_template ;
		}
	}

	return $template;
}


function rs_register_custom_menu_page_cb(){

	global $wpdb;

	if ( ! is_plugin_active( 'gravityforms/gravityforms.php' ) && ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
	  	?>
	  		<div class="notice notice-error">
	  			<p><b>Reputation Saver:</b> This plugin requires gravityforms plugin or contact form 7. Please install "Gravity Forms" plugin or "Contact Form 7" first.</p>
	  		</div>
	  	<?php 
	  	return;
	} 

    ?>
    	<div class="wrap">
    		<h2>Reputation Saver Settings <a class="button button-primary" style="float:right;" target="_blank" href="https://www.reputationsaver.io/product/online-reputation-management-software/">Upgrade To Pro</a></h2>
    		<form method="post" action="options.php">
    			<?php 
					if(isset($_GET['settings-updated'])){
						?>
							<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
								<p><strong>Settings saved.</strong></p>
								<button type="button" class="notice-dismiss">
									<span class="screen-reader-text">Dismiss this notice.</span>
								</button>
							</div>
						<?php 
					}
				?>

				<?php 
					settings_fields('rs_options');
					$rs_options = get_option('rs_options');

					if(isset($rs_options['page']) && !empty($rs_options['page'])){
						update_option('rs_page', $rs_options['page']);
					}
				?>

				<table class="form-table">
					<tbody>
						<tr>
							<th>
								<label>Client Logo</label>
							</th>
							<td>
								<input type="text" name="rs_options[client_logo]" value="<?php echo (isset($rs_options['client_logo']) && !empty($rs_options['client_logo'])) ? $rs_options['client_logo'] : '' ?>" class="rs-upload-url regular-text" />
  								<input type="button" class="button rs-upload-button" value="Upload Image" />
							</td>
						</tr>
						<tr>
							<th>
								<label>Description</label>
							</th>
							<td>
								<?php 
									$content = (isset($rs_options['description']) && !empty($rs_options['description'])) ? $rs_options['description'] : '';
									$editor_id = 'description';
									$settings = array(
													'textarea_name' => 'rs_options[description]',
												);
									wp_editor( $content, $editor_id, $settings );
								?>
							</td>
						</tr>
						<tr>
							<th>
								<label>Use Form</label>
							</th>
							<td>
								<?php 
									$form_type = (isset($rs_options['form_type']) && !empty($rs_options['form_type'])) ? $rs_options['form_type'] : 'contact_form_7';
								?>
								<label>
									<input type="radio" name="rs_options[form_type]" value="contact_form_7" <?php echo $form_type == 'contact_form_7' ? 'checked' : ''; ?>> Contact Form 7
								</label>
								<br>
								<label>
									<input type="radio" name="rs_options[form_type]" value="gravity_form" <?php echo $form_type == 'gravity_form' ? 'checked' : ''; ?>> Gravity Forms
								</label>
								<br>
								<p class="description" id="timezone-description">This type of form will be used to show in rating form.</p>
							</td>
						</tr>
						<tr>
							<th>
								<label>Select Gravity Form</label>
							</th>
							<td>
								<select name="rs_options[form]" id="form">
									<option value="">Select any form to show on rating page....</option>
									<?php 
										$t = $wpdb->prefix . 'rg_form';
										$forms = $wpdb->get_results( 
											"
											SELECT * FROM `$t` WHERE `is_active` =1 AND  `is_trash` =0
											"
										);
										

										if($forms){
											foreach ($forms as $form) {
												$selected = '';
												if(isset($rs_options['form']) && $rs_options['form'] == $form->id){
													$selected = 'selected=selected';
												}
												echo '<option '.$selected.' value="'.($form->id).'">'.($form->title).'</option>';
											}
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								<label>Select Contact Form 7</label>
							</th>
							<td>
								 
								 <select name="rs_options[form_7]" id="form_7">
									<option value="">Select any form to show on rating page....</option> 
									<?php 
										$forms = get_posts(
														array(
															'post_type' => 'wpcf7_contact_form',
															'posts_per_page' => -1
														)
													);

										
										if($forms){
											foreach ($forms as $form) {
												$selected = ''; 
												if(isset($rs_options['form_7']) && $rs_options['form_7'] == $form->ID){
													$selected = 'selected=selected';
												}
												
												echo '<option '.$selected.' value="'.($form->ID).'">'.($form->post_title).'</option>';
											}
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								<label>Page Background Color</label>
							</th>
							<td>
								<input type="text" class="regular-text rs-color-picker" name="rs_options[bg_color]" value="<?php echo isset($rs_options['bg_color']) ? trim($rs_options['bg_color']) : '#91c9e8'; ?>">
							</td>
						</tr>
						<tr>
							<th>
								<label>Footer Text Color</label>
							</th>
							<td>
								<input type="text" class="regular-text rs-color-picker" name="rs_options[txt_color]" value="<?php echo isset($rs_options['txt_color']) ? trim($rs_options['txt_color']) : '#ffffff'; ?>">
							</td>
						</tr>
						<tr>
							<th>
								<label>Reputation Saver Page</label>
							</th>
							<td>
								<select name="rs_options[page]" id="page">
									<option value="">Select Page where you are showing form...</option>
								<?php 
									$pages = get_posts(
													array(
														'post_type' => 'page',
														'posts_per_page' => -1,
													)
												);
									if(!empty($pages)){
										foreach ($pages as $page) {
											$selected = '';
											if(get_option('rs_page') == $page->ID){
												$selected = 'selected';
											}
											echo '<option '.$selected.' value="'.($page->ID).'">'.($page->post_title).'</option>';
										}
									}

								?>
								</select>
							</td>
						</tr>
					</tbody>
				</table>



    			<?php submit_button(); ?>
    		</form>
    	</div>

    	<div class="wrap">
    		<h2>Social Share Links</h2>
    		<form method="post" action="options.php">
    			<?php 
    				settings_fields('rs_options_social');
					$rs_options_social = get_option('rs_options_social');
    			?>

    				<div class="social_share_links_container">

						<div class="social_share_links_single">
							<!-- <h4>Google</h4> -->
							<table class="form-table">
								<tbody>
									<tr>
										<th>Enable Google</th>
										<td>
											<input type="hidden" name="rs_options_social[google][enable]" value="no">
											<input type="checkbox" name="rs_options_social[google][enable]" value="yes" <?php echo (isset($rs_options_social['google']['enable']) && $rs_options_social['google']['enable']  == 'yes') ? 'checked' : ''; ?>>
											<input type="text" class="regular-text" name="rs_options_social[google][link]" value="<?php echo isset($rs_options_social['google']['link']) ? trim($rs_options_social['google']['link']) : ''; ?>">
										</td>
									</tr>
									<!-- <tr>
										<th>Google Image</th>
										<td>
											<input type="checkbox" name="rs_options_social[enable]" value="yes">
										</td>
									</tr> -->
									<!-- <tr>
										<th>Google Share Link</th>
										<td>
										</td>
									</tr> -->
								</tbody>
							</table>
						</div>
						<div class="social_share_links_single">
							<table class="form-table">
								<tbody>
									<tr>
										<th>Enable BBB</th>
										<td>
											<input type="hidden" name="rs_options_social[BBB][enable]" value="no">
											<input type="checkbox" name="rs_options_social[BBB][enable]" value="yes" <?php echo (isset($rs_options_social['BBB']['enable']) && $rs_options_social['BBB']['enable']  == 'yes') ? 'checked' : ''; ?>>
											<input type="text" class="regular-text" name="rs_options_social[BBB][link]" value="<?php echo isset($rs_options_social['BBB']['link']) ? trim($rs_options_social['BBB']['link']) : ''; ?>">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="social_share_links_single">
							<!-- <h4>Facebook</h4> -->
							<table class="form-table">
								<tbody>
									<tr>
										<th>Enable Facebook</th>
										<td>
											<input type="hidden" name="rs_options_social[facebook][enable]" value="no">
											<input type="checkbox" name="rs_options_social[facebook][enable]" value="yes" <?php echo (isset($rs_options_social['facebook']['enable']) && $rs_options_social['facebook']['enable'] == 'yes') ? 'checked' : ''; ?>>
											<input type="text" class="regular-text" name="rs_options_social[facebook][link]" value="<?php echo isset($rs_options_social['facebook']['link']) ? trim($rs_options_social['facebook']['link']) : ''; ?>">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="social_share_links_single">
							<!-- <h4>YelloPages</h4> -->
							<table class="form-table">
								<tbody>
									<tr>
										<th>Enable YelloPages</th>
										<td>
											<input type="hidden" name="rs_options_social[yp][enable]" value="no">
											<input type="checkbox" name="rs_options_social[yp][enable]" value="yes" <?php echo (isset($rs_options_social['yp']['enable']) && $rs_options_social['yp']['enable'] == 'yes') ? 'checked' : ''; ?>>
											<input type="text" class="regular-text" name="rs_options_social[yp][link]" value="<?php echo isset($rs_options_social['yp']['link']) ? trim($rs_options_social['yp']['link']) : ''; ?>">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="social_share_links_single">
							<!-- <h4>Yelp</h4> -->
							<table class="form-table">
								<tbody>
									<tr>
										<th>Enable Yelp</th>
										<td>
											<input type="hidden" name="rs_options_social[yelp][enable]" value="no">
											<input type="checkbox" name="rs_options_social[yelp][enable]" value="yes" <?php echo (isset($rs_options_social['yelp']['enable']) && $rs_options_social['yelp']['enable'] == 'yes') ? 'checked' : ''; ?>>
											<input type="text" class="regular-text" name="rs_options_social[yelp][link]" value="<?php echo isset($rs_options_social['yelp']['link']) ? trim($rs_options_social['yelp']['link']) : ''; ?>">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						

					</div>

    			<?php submit_button(); ?>
    		</form>
    	</div>
    <?php 
}


// create short code to enable rating
add_shortcode('rs-reputation-saver-form', 'rs_rating_form');
function rs_rating_form($atts = array()){
	$atts = shortcode_atts(
					array(),
					$atts,
					'rs-reputation-saver-form'
			);

	if ( ! function_exists( 'is_plugin_active' ) ){
      require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}
	if ( ! is_plugin_active( 'gravityforms/gravityforms.php' ) && ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
	  	return;
	}

	// check if bootstrap exist ot not

	$no_form = false;
	$shortcode = '';
	$form_ID = '';
	$rs_options = get_option('rs_options');
	$form_type = (isset($rs_options['form_type']) && !empty($rs_options['form_type'])) ? $rs_options['form_type'] : 'contact_form_7';
	if($form_type == 'contact_form_7'){
		if(isset($rs_options['form_7']) && !empty($rs_options['form_7'])){
			$form_ID = $rs_options['form_7'];
			$form = get_post($form_ID);
			$shortcode = '[contact-form-7 id="'.$form_ID.'" title="'.$form->post_title.'"]';
		}
		else{
			$no_form = true;
		}
	}
	else{
		if(isset($rs_options['form']) && !empty($rs_options['form'])){
			$form_ID = $rs_options['form'];
			$shortcode = '[gravityform ajax="true" id="'.$form_ID.'"]';
		}
		else{
			$no_form = true;
		}
	}
	if($no_form){
		ob_start();
		
			if(isset($rs_options['bg_color']) && !empty($rs_options['bg_color'])){
				?>
					<style type="text/css">
						body{
							background-color: <?php echo $rs_options['bg_color']; ?> !important;
						}
					</style>
				<?php 
			}
			else{
				?>
					<style type="text/css">
						body{
							background-color: #91c9e8 !important;
						}
					</style>
				<?php 
			}
		
		return ob_get_clean();
	}

	$rs_options_social = get_option('rs_options_social');

	ob_start();
		?>
			<div class="rs-rating-form">
				<div class="rs-rating-form-inner">
					<h1 class="rs-rating-form-title">
						<?php 
							if(isset($rs_options['client_logo']) && !empty($rs_options['client_logo'])){
								echo '<img class="rs-client-logo" src="'.($rs_options['client_logo']).'">';
							}
						?>
					</h1>
					<div class="rs-rating-form-description"><?php echo isset($rs_options['description']) ? trim($rs_options['description']) : '' ?></div>
					<div class="rs-rating-form-rates">
						<!-- <?php // echo do_shortcode($shortcode); ?> -->
						<div class="rs-rate" data-toggle="modal" data-target-modal="#rs-rate-form-modal" type="button">
							<span class="rate-star">&#9733;</span>
							<span class="rate-label">Bad</span>
						</div>
						<div class="rs-rate" data-toggle="modal" data-target-modal="#rs-rate-form-modal" type="button">
							<span class="rate-star">&#9733;</span>
							<span class="rate-label">Subpar</span>
						</div>
						<div class="rs-rate" data-toggle="modal" data-target-modal="#rs-rate-form-modal" type="button">
							<span class="rate-star">&#9733;</span>
							<span class="rate-label">Okay</span>
						</div>
						<div class="rs-rate" data-toggle="modal" data-target-modal="#rs-rate-form-modal" type="button">
							<span class="rate-star">&#9733;</span>
							<span class="rate-label">Good</span>
						</div>
						<div class="rs-rate" data-toggle="modal" data-target-modal="#rs-rate-share-modal" type="button">
							<span class="rate-star">&#9733;</span>
							<span class="rate-label">Amazing</span>
						</div>
					</div>
				</div>
			</div>

			<div class="rs-modal" id="rs-rate-form-modal">
				<div class="rs-model-content">
					<div class="rs-modal-header">
						<button type="button" class="close-modal" data-dismiss-modal="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="rs-modal-body">
						<?php echo do_shortcode($shortcode); ?>
					</div>
				</div>
			</div>

			<div class="rs-modal" id="rs-rate-share-modal">
				<div class="rs-model-content">
					<div class="rs-modal-header">
						<button type="button" class="close-modal" data-dismiss-modal="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="rs-modal-body">
						
						<div class="rs-rate-share-content">
				        	<p>Please take a moment to share your experience with us on one of these review sites.</p>
				        	<div class="rs-share-links">
				        		<?php 
				        			if(isset($rs_options_social['google']['enable']) && $rs_options_social['google']['enable'] == 'yes'){
				        				?>
				        					<div class="rs-share-link">
				        						<a target="_BLANK" href="<?php echo $rs_options_social['google']['link']; ?>"><img src="<?php echo plugin_dir_url(__FILE__) . 'img/google-logo.png'; ?>"></a>
				        					</div>
				        				<?php 
				        			}
				        			if(isset($rs_options_social['yelp']['enable']) && $rs_options_social['yelp']['enable'] == 'yes'){
				        				?>
				        					<div class="rs-share-link">
				        						<a target="_BLANK" href="<?php echo $rs_options_social['yelp']['link']; ?>"><img src="<?php echo plugin_dir_url(__FILE__) . 'img/yelp-logo.png'; ?>"></a>
				        					</div>
				        				<?php 
				        			}
				        			if(isset($rs_options_social['facebook']['enable']) && $rs_options_social['facebook']['enable'] == 'yes'){
				        				?>
				        					<div class="rs-share-link">
				        						<a target="_BLANK" href="<?php echo $rs_options_social['facebook']['link']; ?>"><img src="<?php echo plugin_dir_url(__FILE__) . 'img/facebook-logo.png'; ?>"></a>
				        					</div>
				        				<?php 
				        			}
				        			if(isset($rs_options_social['yp']['enable']) && $rs_options_social['yp']['enable'] == 'yes'){
				        				?>
				        					<div class="rs-share-link">
				        						<a target="_BLANK" href="<?php echo $rs_options_social['yp']['link']; ?>"><img src="<?php echo plugin_dir_url(__FILE__) . 'img/yp.png'; ?>"></a>
				        					</div>
				        				<?php 
				        			}
				        			if(isset($rs_options_social['BBB']['enable']) && $rs_options_social['BBB']['enable'] == 'yes'){
				        				?>
				        					<div class="rs-share-link">
				        						<a target="_BLANK" href="<?php echo $rs_options_social['BBB']['link']; ?>"><img src="<?php echo plugin_dir_url(__FILE__) . 'img/BBB-logo.png'; ?>"></a>
				        					</div>
				        				<?php 
				        			}
				        		?>
				        	</div>
				        </div>

					</div>
				</div>
			</div>

			<?php 
				if(isset($rs_options['bg_color']) && !empty($rs_options['bg_color'])){
					?>
						<style type="text/css">
							body{
								background-color: <?php echo $rs_options['bg_color']; ?> !important;
							}
						</style>
					<?php 
				}
				else{
					?>
						<style type="text/css">
							body{
								background-color: #91c9e8 !important;
							}
						</style>
					<?php 
				}
			?>
			

		<?php 
	return ob_get_clean();
}

add_action('wp_footer', 'rs_rating_form_scripts');
function rs_rating_form_scripts(){
	?>
		<style type="text/css"> 
			.rs-rating-form{
				margin: auto;
				margin-top: 150px;
				text-align: center;
			}
			.rs-rating-form-title img{
				max-width: 500px;
				height: auto;
				width: 100%;
			}
			.rs-rating-form-description{
				max-width: 900px;
				margin: auto;
				text-align: center;
			}
			.rs-rating-form-rates {
			  margin: 50px auto;
			  text-align: center;
			}
			.rs-rate {
			  cursor: pointer;
			  display: inline-block;
			  font-size: 50px;
			  margin: auto 20px;
			}
			.rate-star {
			  display: block;
			}
			.rate-label {
			  display: block;
			  font-size: 13px;
			  margin-top: 10px;
			  opacity: 0;
			}
			.rs-rate:hover .rate-label {
			  opacity: 1;
			}

			.rs-rate.hover, .rs-rate.active{
				color: #ffc700;
			}
			.rs-rate.active.hover{
				color: #c59b08;
			}

			.rs-share-links{
				display: block;
				width: 100%;
			}
			.rs-share-link{
				display: inline-block;
				width: 32%;
				margin: 0;
				padding: 0;
			}
			.rs-share-link a{
				padding: 10px;
				border: 1px solid #ebebeb;
				display: block;
			}
			.rs-share-link a img{
				max-width: 100%;
				height: auto;
			}
			.rs-share-links .rs-share-link:first-child, .rs-share-links .rs-share-link:nth-child(2) {
			  /*display: block;*/
			  margin: auto auto 10px;
			  width: 49.5%;
			}



			/*modal */
			.rs-modal {
			    position: fixed;
			    top: 0;
			    right: 0;
			    bottom: 0;
			    left: 0;
			    z-index: 1050;
			    display: none;
			    overflow: hidden;
			    -webkit-overflow-scrolling: touch;
			    -moz-overflow-scrolling: touch;
			    -ms-overflow-scrolling: touch;
			    -o-overflow-scrolling: touch;
			    overflow-scrolling: touch;
			    outline: 0;
			}
			.rs-model-content{
				margin: 30px auto;
				max-width: 600px;
				width: 100%;
				background-color: #fff;
				border-radius: 6px;
			    box-shadow: 0 5px 15px rgba(0,0,0,.5);
			}
			.rs-show.rs-modal {
			    overflow-x: hidden;
			    overflow-y: auto;
			    display: block;
			}
			.close-modal {
			    float: right;
			    font-size: 21px;
			    font-weight: 700;
			    line-height: 1;
			    color: #000;
			    text-shadow: 0 1px 0 #fff;
			    filter: alpha(opacity=20);
			    opacity: .2;
			}
			.rs-modal-header{
				padding: 15px;
			}
			.rs-modal-body{
				padding: 15px;
			}
			.rs-modal-backdrop {
			    position: fixed;
			    top: 0;
			    right: 0;
			    bottom: 0;
			    left: 0;
			    z-index: 1040;
			    background-color: rgba(0,0,0,0.5);
			}
			.rs-modal-open{
				overflow: hidden;
			}

			@media all and (max-width: 414px){
				.rs-rate{
					margin: auto 5px;
				}
				.rs-rating-form-title img{
					max-width: 300px;
				}
			}
		</style>
		<script type="text/javascript">
			jQuery(function($){
				$(document).on('hover', '.rs-rating-form-rates .rs-rate', function(){
					var this_star = $(this);
					var index = this_star.index();
					$(document).find('.rs-rating-form-rates .rs-rate').each(function(){
						if($(this).index() <= index){
							$(this).addClass('hover');
						}
						else{
							$(this).removeClass('hover');
						}
					});
				});
				$(document).on('mouseleave', '.rs-rating-form-rates .rs-rate', function(){
					$(document).find('.rs-rating-form-rates .rs-rate').each(function(){
						$(this).removeClass('hover');
					});
				});
				$(document).on('click', '.rs-rating-form-rates .rs-rate', function(){
					var this_star = $(this);
					var index = this_star.index();
					$(document).find('.rs-rating-form-rates .rs-rate').each(function(){
						$(this).removeClass('hover');
						if($(this).index() <= index){
							$(this).addClass('active');
						}
						else{
							$(this).removeClass('active');
						}
					});
				});

				// show modal
				// <div class="rs-modal-backdrop"></div>
				$(document).on('click', '[data-target-modal]', function(){
					var this_btn = $(this);
					var this_modal_id = this_btn.data('target-modal');

					$(this_modal_id).addClass('rs-show');
					$('body').append('<div class="rs-modal-backdrop"></div>');
					$('body').addClass('rs-modal-open');
				});

				$(document).on('click', '.close-modal, [data-dismiss-modal="modal"]', function(){
					var this_close = $(this);
					this_close.closest('.rs-modal').removeClass('rs-show');
					$(document).find('body > .rs-modal-backdrop:last-child').remove();
					$('body').removeClass('rs-modal-open');
				});
			});
		</script>
	<?php 
}