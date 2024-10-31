<?php 

/*
	Global template for reputation saver shortcode
*/
?>
<!DOCTYPE HTML>
<html lang="en-US" prefix="og: http://ogp.me/ns#">
<head>
	<!-- Meta Tags -->
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <!-- Mobile Device Meta -->
    <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui' /> 
	<?php wp_head(); ?>	
</head>
<body>
	<?php 
		global $post;
		// the_post();
		// the_content();

		echo do_shortcode($post->post_content);

		?>
			<style type="text/css">
				body{
					display: block;
					position: relative;
					left: 0;
					right: 0;
					top: 0;
					bottom: 0;
					min-height: 100%;
					height: 100%;
				}
				.rs-footer {
					margin: auto;
					display: block;
					width: 100%;
					text-align: center;
					color: #fff;
					background-color: transparent !important;
					padding: 10px;
					position: fixed !important;
					bottom: 0 !important;
					left: 0;
					right: 0;
				}
				.rs-footer .rs-powered-by {
					margin: auto;
					text-align: center;
					color: #fff;
					text-shadow: 0 0 5px #444;
				}
				@media all and (max-width: 414px){
					.rs-footer {
						position: relative !important;
						margin-top: 50px !important;
					}
				}
			</style>
			<div claas="rs-footer" style="position:fixed; bottom:0; text-align:center; padding:10px; width: 100%; left:0; right:0;">
				<div class="rs-footer-inner">
					<?php 
						$rs_options = get_option('rs_options');
						$color = '#fff';
						if(isset($rs_options['txt_color']) && !empty($rs_options['txt_color'])){
							$color = $rs_options['txt_color'];
						}
					?>
					<p class="rs-powered-by" style="margin:auto; text-align: center; color:<?php echo $color; ?>; text-shadow: 0 0 10px #000;">Reputation Saver | Powered by <a target="_blank" href="http://www.reputationsaver.io/">Reputation Saver</a></p>
				</div>
			</div>
		<?php 

		wp_footer();
	?>
</body>
</html>

