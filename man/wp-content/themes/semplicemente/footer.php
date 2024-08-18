<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package semplicemente
 */
?>

	</div><!-- #content -->
	<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) : ?>
		<footer id="colophon" class="site-footer">
			<div class="site-info">
				<?php
				$copyrightText = get_theme_mod('semplicemente_theme_options_copyrighttext', '&copy; '.date('Y').' '. get_bloginfo('name'));
				if ($copyrightText || is_customize_preview()): ?>
					<span class="custom"><?php echo do_shortcode(wp_kses_post($copyrightText)); ?></span>
				<?php endif; ?>
				<span class="sep"> | </span>
				<?php
				/* translators: 1: theme name, 2: theme developer */
				printf( esc_html__( 'WordPress Theme: %1$s by %2$s.', 'semplicemente' ), '<a title="Semplicemente Theme" href="https://crestaproject.com/downloads/semplicemente/" rel="noopener noreferrer" target="_blank">Semplicemente</a>', 'CrestaProject' );
				?>
			</div><!-- .site-info -->
		</footer><!-- #colophon -->
	<?php endif; ?>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
