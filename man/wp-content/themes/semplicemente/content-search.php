<?php
/**
 * The template part for displaying results in search pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package semplicemente
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
		if ( '' != get_the_post_thumbnail() ) {
			echo '<div class="entry-featuredImg"><a href="' .esc_url(get_permalink()). '" title="' .the_title_attribute('echo=0'). '"><span class="overlay-img"></span>';
			the_post_thumbnail('normal-post', array('class' => 'semplicemente-loop-featured-image'));
			echo '</a></div>';
		}
	?>
	<header class="entry-header">
		<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

		<?php if ( 'post' == get_post_type() ) : ?>
		<div class="entry-meta">
			<?php semplicemente_posted_on(); ?>
			<?php if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
				<span class="comments-link"><i class="fa fa-comment-o spaceRight" aria-hidden="true"></i><?php comments_popup_link( esc_html__( 'Leave a comment', 'semplicemente' ), esc_html__( '1 Comment', 'semplicemente' ), esc_html__( '% Comments', 'semplicemente' ) ); ?></span>
			<?php endif; ?>
		</div><!-- .entry-meta -->
		<?php endif; ?>
	</header><!-- .entry-header -->

	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div><!-- .entry-summary -->

	<footer class="entry-footer">
		<?php edit_post_link( esc_html__( 'Edit', 'semplicemente' ), '<span class="edit-link"><i class="fa fa-pencil-square-o spaceRight" aria-hidden="true"></i>', '</span>' ); ?>
		<div class="readMoreLink">
			<a href="<?php echo esc_url(get_permalink()); ?>"><?php esc_html_e('Read More', 'semplicemente') ?><i class="fa spaceLeft fa-angle-double-right" aria-hidden="true"></i></a>
		</div>
	</footer><!-- .entry-footer -->
</article><!-- #post-## -->