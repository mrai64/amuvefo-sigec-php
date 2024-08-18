<?php
/**
 * semplicemente functions and dynamic template
 *
 * @package semplicemente
 */

 /**
 * Register Custom Settings
 */
function semplicemente_custom_settings_register( $wp_customize ) {
	/*
	Theme Colors
	=====================================================
	*/
	$colors = array();
	
	$colors[] = array(
	'slug'=>'color_primary', 
	'default' => '#888888',
	'label' => __('Primary Color ', 'semplicemente')
	);
	
	$colors[] = array(
	'slug'=>'color_link', 
	'default' => '#404040',
	'label' => __('Link Color ', 'semplicemente')
	);
	
	$colors[] = array(
	'slug'=>'color_secondary', 
	'default' => '#36c1c8',
	'label' => __('Secondary Color ', 'semplicemente')
	);
	
	foreach( $colors as $semplicemente_theme_options ) {
		// SETTINGS
			$wp_customize->add_setting(
				'semplicemente_theme_options[' . $semplicemente_theme_options['slug'] . ']', array(
				'default' => $semplicemente_theme_options['default'],
				'type' => 'option', 
				'sanitize_callback' => 'sanitize_hex_color',
				'capability' => 'edit_theme_options'
			)
		);
		// CONTROLS
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$semplicemente_theme_options['slug'], 
				array('label' => $semplicemente_theme_options['label'], 
				'section' => 'colors',
				'settings' =>'semplicemente_theme_options[' . $semplicemente_theme_options['slug'] . ']',
				)
			)
		);
	}
	
	/*
	Start Annina Options
	=====================================================
	*/
	$wp_customize->add_section( 'cresta_semplicemente_options', array(
	     'title'    => esc_html__( 'Semplicemente Theme Options', 'semplicemente' ),
	     'priority' => 50,
	) );
	
	$socialmedia = array();
	
	$socialmedia[] = array(
	'slug'=>'facebookurl', 
	'default' => '#',
	'label' => __('Facebook URL', 'semplicemente')
	);
	$socialmedia[] = array(
	'slug'=>'twitterurl', 
	'default' => '#',
	'label' => __('Twitter URL', 'semplicemente')
	);
	$socialmedia[] = array(
	'slug'=>'googleplusurl', 
	'default' => '#',
	'label' => __('Google Plus URL', 'semplicemente')
	);
	$socialmedia[] = array(
	'slug'=>'linkedinurl', 
	'default' => '#',
	'label' => __('Linkedin URL', 'semplicemente')
	);
	$socialmedia[] = array(
	'slug'=>'instagramurl', 
	'default' => '#',
	'label' => __('Instagram URL', 'semplicemente')
	);
	$socialmedia[] = array(
	'slug'=>'youtubeurl', 
	'default' => '#',
	'label' => __('YouTube URL', 'semplicemente')
	);
	$socialmedia[] = array(
	'slug'=>'pinteresturl', 
	'default' => '#',
	'label' => __('Pinterest URL', 'semplicemente')
	);
	$socialmedia[] = array(
	'slug'=>'tumblrurl', 
	'default' => '#',
	'label' => __('Tumblr URL', 'semplicemente')
	);
	$socialmedia[] = array(
	'slug'=>'vkurl', 
	'default' => '#',
	'label' => __('VK URL', 'semplicemente')
	);
	$socialmedia[] = array(
	'slug'=>'xingurl', 
	'default' => '',
	'label' => __('Xing URL', 'semplicemente')
	);
	$socialmedia[] = array(
	'slug'=>'twitchurl', 
	'default' => '',
	'label' => __('Twitch URL', 'semplicemente')
	);
	$socialmedia[] = array(
	'slug'=>'spotifyurl', 
	'default' => '',
	'label' => __('Spotify URL', 'semplicemente')
	);
	$socialmedia[] = array(
	'slug'=>'whatsappurl', 
	'default' => '',
	'label' => __('WhatsApp URL', 'semplicemente')
	);
	
	foreach( $socialmedia as $semplicemente_theme_options ) {
		// SETTINGS
			$wp_customize->add_setting(
				'semplicemente_theme_options_' . $semplicemente_theme_options['slug'], array(
				'default' => $semplicemente_theme_options['default'],
				'capability'     => 'edit_theme_options',
				'sanitize_callback' => 'esc_url_raw',
				'type'     => 'theme_mod',
			)
		);
		// CONTROLS
		$wp_customize->add_control(
			$semplicemente_theme_options['slug'], 
			array('label' => $semplicemente_theme_options['label'], 
			'section'    => 'cresta_semplicemente_options',
			'settings' =>'semplicemente_theme_options_' . $semplicemente_theme_options['slug'],
			)
		);
	}
	
	/*
	Email Button
	=====================================================
	*/
	$wp_customize->add_setting('semplicemente_theme_options_emailurl', array(
        'default'    => '',
        'type'       => 'theme_mod',
        'capability' => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_email'
    ) );
	
	$wp_customize->add_control('semplicemente_theme_options_emailurl', array(
        'label'      => __( 'Your Email', 'semplicemente' ),
        'section'    => 'cresta_semplicemente_options',
        'settings'   => 'semplicemente_theme_options_emailurl',
    ) );
	
	/*
	Search Button
	=====================================================
	*/
	$wp_customize->add_setting('semplicemente_theme_options_hidesearch', array(
        'default'    => '1',
        'type'       => 'theme_mod',
        'capability' => 'edit_theme_options',
		'sanitize_callback' => 'semplicemente_sanitize_checkbox'
    ) );
	
	$wp_customize->add_control('hidesearch', array(
        'label'      => __( 'Show Search Button', 'semplicemente' ),
        'section'    => 'cresta_semplicemente_options',
        'settings'   => 'semplicemente_theme_options_hidesearch',
        'type'       => 'checkbox',
    ) );

    /*
	Host Google Fonts locally
	=====================================================
	*/
	$wp_customize->add_setting('semplicemente_theme_options_googlefontslocal', array(
        'default'    => '',
        'type'       => 'theme_mod',
        'capability' => 'edit_theme_options',
		'sanitize_callback' => 'semplicemente_sanitize_checkbox'
    ) );
	
	$wp_customize->add_control('semplicemente_theme_options_googlefontslocal', array(
        'label'      => __( 'Host Google Fonts locally', 'semplicemente' ),
        'section'    => 'cresta_semplicemente_options',
        'settings'   => 'semplicemente_theme_options_googlefontslocal',
        'type'       => 'checkbox',
    ) );
	
	/*
	Custom Mobile Menu text
	=====================================================
	*/
	$wp_customize->add_setting('semplicemente_theme_options_mobilemenu_text', array(
		'sanitize_callback' => 'semplicemente_sanitize_text',
		'default'    => __( 'Menu', 'semplicemente' ),
		'type'       => 'theme_mod',
		'capability' => 'edit_theme_options',
	) );
	$wp_customize->add_control('semplicemente_theme_options_mobilemenu_text', array(
		'label'      => __( 'Mobile menu text', 'semplicemente' ),
		'section'    => 'cresta_semplicemente_options',
		'settings'   => 'semplicemente_theme_options_mobilemenu_text',
		'type'       => 'text',
	) );
	
	/*
	Read More text
	=====================================================
	*/
	$wp_customize->add_setting('semplicemente_theme_options_readmoretext', array(
        'default'    => __( 'Read More', 'semplicemente' ),
        'type'       => 'theme_mod',
        'capability' => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field'
    ) );
	
	$wp_customize->add_control('semplicemente_theme_options_readmoretext', array(
        'label'      => __( 'Custom text for read more button', 'semplicemente' ),
        'section'    => 'cresta_semplicemente_options',
        'settings'   => 'semplicemente_theme_options_readmoretext',
		'type'       => 'text',
    ) );
	
	/*
	Copyright text
	=====================================================
	*/
	$wp_customize->add_setting('semplicemente_theme_options_copyrighttext', array(
		'sanitize_callback' => 'semplicemente_sanitize_text',
		'default'    => '&copy; '.date('Y').' '. get_bloginfo('name'),
		'type'       => 'theme_mod',
		'capability' => 'edit_theme_options',
	) );
	$wp_customize->add_control('semplicemente_theme_options_copyrighttext', array(
		'label'      => __( 'Copyright Text', 'semplicemente' ),
		'description' => __( 'Get the PRO version to remove CrestaProject Credits', 'semplicemente'),
		'section'    => 'cresta_semplicemente_options',
		'settings'   => 'semplicemente_theme_options_copyrighttext',
		'type'       => 'text',
	) );
	
	/*
	Upgrade to PRO
	=====================================================
	*/
    class Semplicemente_Customize_Upgrade_Control extends WP_Customize_Control {
        public function render_content() {  ?>
        	<p class="semplicemente-upgrade-title">
        		<span class="customize-control-title">
					<h3 style="text-align:center;"><div class="dashicons dashicons-megaphone"></div> <?php esc_html_e('Get Semplicemente PRO WP Theme for only', 'semplicemente'); ?> 19,90&euro;</h3>
        		</span>
        	</p>
			<p style="text-align:center;" class="semplicemente-upgrade-button">
				<a style="margin: 10px;" target="_blank" href="https://crestaproject.com/demo/semplicemente-pro/" class="button button-secondary">
					<?php esc_html_e('Watch the demo', 'semplicemente'); ?>
				</a>
				<a style="margin: 10px;" target="_blank" href="https://crestaproject.com/downloads/semplicemente/" class="button button-secondary">
					<?php esc_html_e('Get Semplicemente PRO Theme', 'semplicemente'); ?>
				</a>
			</p>
			<ul>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php esc_html_e('Advanced Theme Options', 'semplicemente'); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php esc_html_e('Logo Upload', 'semplicemente'); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php esc_html_e('Choose sidebar position', 'semplicemente'); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php esc_html_e('Font switcher', 'semplicemente'); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php esc_html_e('Loading Page', 'semplicemente'); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php esc_html_e('Unlimited Colors and Skin', 'semplicemente'); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php esc_html_e('Post views counter', 'semplicemente'); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php esc_html_e('Post format', 'semplicemente'); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php esc_html_e('7 Shortcodes', 'semplicemente'); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php esc_html_e('12 Exclusive Widgets', 'semplicemente'); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php esc_html_e('Related Posts Box', 'semplicemente'); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php esc_html_e('Information About Author Box', 'semplicemente'); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php esc_html_e('And much more...', 'semplicemente'); ?></b></li>
			<ul><?php
        }
    }
	
	$wp_customize->add_section( 'cresta_upgrade_pro', array(
	     'title'    => esc_html__( 'More features? Upgrade to PRO', 'semplicemente' ),
	     'priority' => 999,
	));
	
	$wp_customize->add_setting('semplicemente_section_upgrade_pro', array(
		'default' => '',
		'type' => 'option',
		'sanitize_callback' => 'esc_attr'
	));
	
	$wp_customize->add_control(new Semplicemente_Customize_Upgrade_Control($wp_customize, 'semplicemente_section_upgrade_pro', array(
		'section' => 'cresta_upgrade_pro',
		'settings' => 'semplicemente_section_upgrade_pro',
	)));
	
}
add_action( 'customize_register', 'semplicemente_custom_settings_register' );

function semplicemente_sanitize_text( $input ) {
	return wp_kses($input, semplicemente_allowed_html());
}

if( ! function_exists('semplicemente_allowed_html')){
	function semplicemente_allowed_html() {
		$allowed_tags = array(
			'a' => array(
				'class' => array(),
				'id'    => array(),
				'href'  => array(),
				'rel'   => array(),
				'title' => array(),
				'target' => array(),
			),
			'abbr' => array(
				'title' => array(),
			),
			'b' => array(),
			'blockquote' => array(
				'cite'  => array(),
			),
			'cite' => array(
				'title' => array(),
			),
			'code' => array(),
			'del' => array(
				'datetime' => array(),
				'title' => array(),
			),
			'dd' => array(),
			'div' => array(
				'class' => array(),
				'title' => array(),
				'style' => array(),
			),
			'dl' => array(),
			'dt' => array(),
			'em' => array(),
			'h1' => array(
				'class' => array(),
			),
			'h2' => array(
				'class' => array(),
			),
			'h3' => array(
				'class' => array(),
			),
			'h4' => array(
				'class' => array(),
			),
			'h5' => array(
				'class' => array(),
			),
			'h6' => array(
				'class' => array(),
			),
			'i' => array(
				'class' => array(),
			),
			'br' => array(),
			'img' => array(
				'alt'    => array(),
				'class'  => array(),
				'height' => array(),
				'src'    => array(),
				'width'  => array(),
			),
			'li' => array(
				'class' => array(),
			),
			'ol' => array(
				'class' => array(),
			),
			'p' => array(
				'class' => array(),
			),
			'q' => array(
				'cite' => array(),
				'title' => array(),
			),
			'span' => array(
				'class' => array(),
				'title' => array(),
				'style' => array(),
			),
			'strike' => array(),
			'strong' => array(),
			'ul' => array(
				'class' => array(),
			),
			'iframe' => array(
				'width' => array(),
				'height' => array(),
				'src' => array(),
				'frameborder' => array(),
				'allow' => array(),
				'style' => array(),
				'name' => array(),
				'id' => array(),
				'class' => array(),
			),
		);
		return $allowed_tags;
	}
}

/**
 * Add Custom CSS to Header 
 */
function semplicemente_custom_css_styles() { 
	global $semplicemente_theme_options;
	$color_options = get_option( 'semplicemente_theme_options', $semplicemente_theme_options );	
	if( isset( $color_options[ 'color_primary' ] ) ) {
		$color_primary = $color_options['color_primary'];
	}
	if( isset( $color_options[ 'color_link' ] ) ) {
		$color_link = $color_options['color_link'];
	}
	if( isset( $color_options[ 'color_secondary' ] ) ) {
		$color_secondary = $color_options['color_secondary'];
	}
	
?>

<style id="semplicemente-custom-css">
	<?php if (!empty($color_primary)) : ?>
	body,
	button,
	input,
	select,
	textarea {
		color: <?php echo esc_html($color_primary); ?>;
	}
	<?php endif; ?>
	
	<?php if (!empty($color_link)) : ?>
	h1, h2, h3, h4, h5, h6, a, .entry-date {
		color: <?php echo esc_html($color_link); ?>;
	}
	<?php endif; ?>
	
	<?php if (!empty($color_secondary)) : ?>
	a:hover, a:focus, a:active, .entry-meta i, .top-search.active {
		color: <?php echo esc_html($color_secondary); ?>;
	}
	.widget-title h3 {
		border-bottom: 1px solid <?php echo esc_html($color_secondary); ?>;
	}
	.sticky {
		border: 3px solid <?php echo esc_html($color_secondary); ?>;
	}
	
	blockquote {
		border-left: 5px solid <?php echo esc_html($color_secondary); ?>;
		border-right: 2px solid <?php echo esc_html($color_secondary); ?>;
	}
	
	button,
	input[type="button"],
	input[type="reset"],
	input[type="submit"],
	.dataBottom a, 
	.readMoreLink {
		border: 1px solid <?php echo esc_html($color_secondary); ?>;
	}
	
	button:hover,
	input[type="button"]:hover,
	input[type="reset"]:hover,
	input[type="submit"]:hover,
	.readMoreLink:hover, 
	.dataBottom a:hover, 
	.sticky:before,
	button:focus,
	input[type="button"]:focus,
	input[type="reset"]:focus,
	input[type="submit"]:focus,
	button:active,
	input[type="button"]:active,
	input[type="reset"]:active,
	input[type="submit"]:active,
	.menu-toggle,
	.main-navigation.toggled .nav-menu,
	.main-navigation.toggled .nav-menu ul	{
		background: <?php echo esc_html($color_secondary); ?>;
	}
	<?php endif; ?>
	
</style>
    <?php
}
add_action('wp_head', 'semplicemente_custom_css_styles');

function semplicemente_sanitize_checkbox( $input ) {
    if ( $input == 1 ) {
        return 1;
    } else {
        return '';
    }
}
