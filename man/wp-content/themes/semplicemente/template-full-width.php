<?php
/**
 *
 * Template Name: Full width
 *
 * The template used if you want to create a page without sidebar but full width
 *
 * @package semplicemente
 */
 
get_header(); ?>
	<div id="primary" class="content-area full-width">
		<main id="main" class="site-main">
			<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) : ?>
				<?php 
					if(have_posts()) : 
						while(have_posts()) : the_post();
							get_template_part( 'content', 'page' );
							// If comments are open or we have at least one comment, load up the comment template
							if ( comments_open() || '0' != get_comments_number() ) :
								comments_template();
							endif;
						endwhile;
					endif; ?>
			<?php endif; ?>
		</main><!-- #main -->
	</div><!-- #primary -->
<?php
get_footer();