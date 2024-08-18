<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package semplicemente
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php 
if ( function_exists( 'wp_body_open' ) ) {
    wp_body_open();
} else {
    do_action( 'wp_body_open' );
}
?>
<div id="page" class="hfeed site">
	<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) : ?>
		<header id="masthead" class="site-header">
			<div class="site-branding">
				<?php
				if ( is_front_page() && is_home() ) : ?>
					<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				<?php else : ?>
					<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
				<?php
				endif;
				$semplicemente_description = get_bloginfo( 'description', 'display' );
				if ( $semplicemente_description || is_customize_preview() ) : ?>
					<p class="site-description"><?php echo $semplicemente_description; /* // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?></p>
				<?php
				endif; ?>
			</div>
			<?php 
				$hideSearch = get_theme_mod('semplicemente_theme_options_hidesearch', '1');
				$facebookURL = get_theme_mod('semplicemente_theme_options_facebookurl', '#');
				$twitterURL = get_theme_mod('semplicemente_theme_options_twitterurl', '#');
				$googleplusURL = get_theme_mod('semplicemente_theme_options_googleplusurl', '#');
				$linkedinURL = get_theme_mod('semplicemente_theme_options_linkedinurl', '#');
				$instagramURL = get_theme_mod('semplicemente_theme_options_instagramurl', '#');
				$youtubeURL = get_theme_mod('semplicemente_theme_options_youtubeurl', '#');
				$pinterestURL = get_theme_mod('semplicemente_theme_options_pinteresturl', '#');
				$tumblrURL = get_theme_mod('semplicemente_theme_options_tumblrurl', '#');
				$vkURL = get_theme_mod('semplicemente_theme_options_vkurl', '#');
				$xingURL = get_theme_mod('semplicemente_theme_options_xingurl', '');
				$twitchURL = get_theme_mod('semplicemente_theme_options_twitchurl', '');
				$spotifyURL = get_theme_mod('semplicemente_theme_options_spotifyurl', '');
				$whatsappURL = get_theme_mod('semplicemente_theme_options_whatsappurl', '');
				$emailURL = get_theme_mod('semplicemente_theme_options_emailurl', '');
			?>

			<div class="site-social">
				<div class="socialLine">
				
					<?php if (!empty($facebookURL)) : ?>
						<a href="<?php echo esc_url($facebookURL); ?>" title="<?php esc_attr_e( 'Facebook', 'semplicemente' ); ?>"><i class="fa fa-facebook spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'Facebook', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
							
					<?php if (!empty($twitterURL)) : ?>
						<a href="<?php echo esc_url($twitterURL); ?>" title="<?php esc_attr_e( 'Twitter', 'semplicemente' ); ?>"><i class="fa fa-twitter spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'Twitter', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
							
					<?php if (!empty($googleplusURL)) : ?>
						<a href="<?php echo esc_url($googleplusURL); ?>" title="<?php esc_attr_e( 'Google Plus', 'semplicemente' ); ?>"><i class="fa fa-google-plus spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'Google Plus', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
							
					<?php if (!empty($linkedinURL)) : ?>
						<a href="<?php echo esc_url($linkedinURL); ?>" title="<?php esc_attr_e( 'Linkedin', 'semplicemente' ); ?>"><i class="fa fa-linkedin spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'Linkedin', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
							
					<?php if (!empty($instagramURL)) : ?>
						<a href="<?php echo esc_url($instagramURL); ?>" title="<?php esc_attr_e( 'Instagram', 'semplicemente' ); ?>"><i class="fa fa-instagram spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'Instagram', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
							
					<?php if (!empty($youtubeURL)) : ?>
						<a href="<?php echo esc_url($youtubeURL); ?>" title="<?php esc_attr_e( 'YouTube', 'semplicemente' ); ?>"><i class="fa fa-youtube spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'YouTube', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
							
					<?php if (!empty($pinterestURL)) : ?>
						<a href="<?php echo esc_url($pinterestURL); ?>" title="<?php esc_attr_e( 'Pinterest', 'semplicemente' ); ?>"><i class="fa fa-pinterest spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'Pinterest', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
					
					<?php if (!empty($tumblrURL)) : ?>
						<a href="<?php echo esc_url($tumblrURL); ?>" title="<?php esc_attr_e( 'Tumblr', 'semplicemente' ); ?>"><i class="fa fa-tumblr spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'Tumblr', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
							
					<?php if (!empty($vkURL)) : ?>
						<a href="<?php echo esc_url($vkURL); ?>" title="<?php esc_attr_e( 'VK', 'semplicemente' ); ?>"><i class="fa fa-vk spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'VK', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
					
					<?php if (!empty($xingURL )) : ?>
						<a href="<?php echo esc_url($xingURL ); ?>" title="<?php esc_attr_e( 'Xing', 'semplicemente' ); ?>"><i class="fa fa-xing spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'Xing', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
					
					<?php if (!empty($twitchURL )) : ?>
						<a href="<?php echo esc_url($twitchURL ); ?>" title="<?php esc_attr_e( 'Twitch', 'semplicemente' ); ?>"><i class="fa fa-twitch spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'Twitch', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
					
					<?php if (!empty($spotifyURL )) : ?>
						<a href="<?php echo esc_url($spotifyURL ); ?>" title="<?php esc_attr_e( 'Spotify', 'semplicemente' ); ?>"><i class="fa fa-spotify spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'Spotify', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
					
					<?php if (!empty($whatsappURL )) : ?>
						<a href="<?php echo esc_url($whatsappURL ); ?>" title="<?php esc_attr_e( 'WhatsApp', 'semplicemente' ); ?>"><i class="fa fa-whatsapp spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'WhatsApp', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
					
					<?php if (!empty($emailURL)) : ?>
						<a href="mailto:<?php echo esc_attr(antispambot($emailURL)); ?>" title="<?php esc_attr_e( 'Email', 'semplicemente' ); ?>"><i class="fa fa-envelope spaceLeftDouble"><span class="screen-reader-text"><?php esc_html_e( 'Email', 'semplicemente' ); ?></span></i></a>
					<?php endif; ?>
					
					<?php if ( $hideSearch == 1 ) : ?>
						<a href="#" aria-hidden="true" class="top-search"><i class="fa spaceLeftDouble fa-search"></i></a>
					<?php endif; ?>
					
				</div>
					<?php if ( $hideSearch == 1 ) : ?>
					<div class="topSearchForm">
						<?php get_search_form(); ?>
					</div>
					<?php endif; ?>
			</div>
			
			<nav id="site-navigation" class="main-navigation">
				<?php $mobileMenuText = get_theme_mod('semplicemente_theme_options_mobilemenu_text', __( 'Menu', 'semplicemente' )); ?>
				<button class="menu-toggle" aria-label="<?php echo esc_attr($mobileMenuText); ?>"><?php echo esc_html($mobileMenuText); ?><i class="fa fa-align-justify"></i></button>
				<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>
			</nav><!-- #site-navigation -->
		</header><!-- #masthead -->
	<?php endif; ?>
	<div id="content" class="site-content">
