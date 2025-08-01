<?php
/**
 * Semplicemente Admin Class.
 *
 * @author  CrestaProject
 * @package Semplicemente
 * @since   1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Semplicemente_Admin' ) ) :

/**
 * Semplicemente_Admin Class.
 */
class Semplicemente_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'wp_loaded', array( __CLASS__, 'hide_notices' ) );
		add_action( 'load-themes.php', array( $this, 'admin_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add admin menu.
	 */
	public function admin_menu() {
		$theme = wp_get_theme( get_template() );
		global $semplicemente_adminpage;
		$semplicemente_adminpage = add_theme_page( esc_html__( 'About', 'semplicemente' ) . ' ' . $theme->display( 'Name' ), esc_html__( 'About', 'semplicemente' ) . ' ' . $theme->display( 'Name' ), 'activate_plugins', 'semplicemente-welcome', array( $this, 'welcome_screen' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public function enqueue_admin_scripts() {
		global $semplicemente_adminpage;
		$screen = get_current_screen();
		if ( $screen->id != $semplicemente_adminpage ) {
			return;
		}
		wp_enqueue_style( 'semplicemente-welcome', get_template_directory_uri() . '/inc/admin/welcome.css', array(), '1.0' );
	}

	/**
	 * Add admin notice.
	 */
	public function admin_notice() {
		global $pagenow;

		wp_enqueue_style( 'semplicemente-message', get_template_directory_uri() . '/inc/admin/message.css', array(), '1.0' );

		// Let's bail on theme activation.
		if ( 'themes.php' == $pagenow && isset( $_GET['activated'] ) ) {
			add_action( 'admin_notices', array( $this, 'welcome_notice' ) );
			update_option( 'semplicemente_admin_notice_welcome', 1 );

		// No option? Let run the notice wizard again..
		} elseif( ! get_option( 'semplicemente_admin_notice_welcome' ) ) {
			add_action( 'admin_notices', array( $this, 'welcome_notice' ) );
		}
	}

	/**
	 * Hide a notice if the GET variable is set.
	 */
	public static function hide_notices() {
		if ( isset( $_GET['semplicemente-hide-notice'] ) && isset( $_GET['_semplicemente_notice_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key($_GET['_semplicemente_notice_nonce'] ), 'semplicemente_hide_notices_nonce' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'semplicemente' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Cheatin&#8217; huh?', 'semplicemente' ) );
			}

			$hide_notice = sanitize_text_field( wp_unslash($_GET['semplicemente-hide-notice'] ));
			update_option( 'semplicemente_admin_notice_' . $hide_notice, 1 );
		}
	}

	/**
	 * Show welcome notice.
	 */
	public function welcome_notice() {
		?>
		<div id="message" class="updated cresta-message">
			<a class="cresta-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( remove_query_arg( array( 'activated' ), add_query_arg( 'semplicemente-hide-notice', 'welcome' ) ), 'semplicemente_hide_notices_nonce', '_semplicemente_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'semplicemente' ); ?></a>
			<p>
			<?php
			/* translators: 1: start option panel link, 2: end option panel link */
			printf( esc_html__( 'Welcome! Thank you for choosing Semplicemente! To fully take advantage of the best our theme can offer please make sure you visit our %1$swelcome page%2$s.', 'semplicemente' ), '<a href="' . esc_url( admin_url( 'themes.php?page=semplicemente-welcome' ) ) . '">', '</a>' );
			?>
			</p>
			<p class="submit">
				<a class="button-secondary" href="<?php echo esc_url( admin_url( 'themes.php?page=semplicemente-welcome' ) ); ?>"><?php esc_html_e( 'Get started with Semplicemente', 'semplicemente' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Intro text/links shown to all about pages.
	 *
	 * @access private
	 */
	private function intro() {
		$theme = wp_get_theme( get_template() );
		?>
		<div class="cresta-theme-info">
				<h1>
					<?php esc_html_e('About', 'semplicemente'); ?>
					<?php echo esc_html($theme->get( 'Name' )) ." ". esc_html($theme->get( 'Version' )); ?>
				</h1>

			<div class="welcome-description-wrap">
				<div class="about-text"><?php echo esc_html($theme->display( 'Description' )); ?>
				<p class="cresta-actions">
					<a href="<?php echo esc_url( apply_filters( 'semplicemente_pro_theme_url', 'https://crestaproject.com/downloads/semplicemente/' ) ); ?>" class="button button-secondary" target="_blank"><?php esc_html_e( 'Theme Info', 'semplicemente' ); ?></a>

					<a href="<?php echo esc_url( apply_filters( 'semplicemente_pro_theme_url', 'https://crestaproject.com/demo/semplicemente/' ) ); ?>" class="button button-secondary docs" target="_blank"><?php esc_html_e( 'View Demo', 'semplicemente' ); ?></a>

					<a href="<?php echo esc_url( apply_filters( 'semplicemente_pro_theme_url', 'https://crestaproject.com/demo/semplicemente-pro/' ) ); ?>" class="button button-primary docs" target="_blank"><?php esc_html_e( 'View PRO version Demo', 'semplicemente' ); ?></a>

					<a href="<?php echo esc_url( apply_filters( 'semplicemente_pro_theme_url', 'https://wordpress.org/support/theme/semplicemente/reviews/' ) ); ?>" class="button button-secondary docs" target="_blank"><?php esc_html_e( 'Rate this theme', 'semplicemente' ); ?></a>
				</p>
				</div>

				<div class="cresta-screenshot">
					<img src="<?php echo esc_url( get_template_directory_uri() ) . '/screenshot.png'; ?>" />
				</div>
			</div>
		</div>

		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php if ( empty( $_GET['tab'] ) && isset( $_GET['page'] ) && $_GET['page'] == 'semplicemente-welcome' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'semplicemente-welcome' ), 'themes.php' ) ) ); ?>">
				<?php echo esc_html($theme->display( 'Name' )); ?>
			</a>
			<a class="nav-tab <?php if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'free_vs_pro' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'semplicemente-welcome', 'tab' => 'free_vs_pro' ), 'themes.php' ) ) ); ?>">
				<?php esc_html_e( 'Free Vs PRO', 'semplicemente' ); ?>
			</a>
			<a class="nav-tab <?php if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'changelog' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'semplicemente-welcome', 'tab' => 'changelog' ), 'themes.php' ) ) ); ?>">
				<?php esc_html_e( 'Changelog', 'semplicemente' ); ?>
			</a>
		</h2>
		<?php
	}

	/**
	 * Welcome screen page.
	 */
	public function welcome_screen() {
		$current_tab = empty( $_GET['tab'] ) ? 'about' : sanitize_title( wp_unslash($_GET['tab']) );

		// Look for a {$current_tab}_screen method.
		if ( is_callable( array( $this, $current_tab . '_screen' ) ) ) {
			return $this->{ $current_tab . '_screen' }();
		}

		// Fallback to about screen.
		return $this->about_screen();
	}

	/**
	 * Output the about screen.
	 */
	public function about_screen() {
		$theme = wp_get_theme( get_template() );
		?>
		<div class="wrap about-wrap">

			<?php $this->intro(); ?>

			<div class="changelog point-releases">
				<div class="under-the-hood two-col">
					<div class="col">
						<h3><?php esc_html_e( 'Theme Customizer', 'semplicemente' ); ?></h3>
						<p><?php esc_html_e( 'All Theme Options are available via Customize screen.', 'semplicemente' ) ?></p>
						<p><a href="<?php echo esc_url(admin_url( 'customize.php' )); ?>" class="button button-secondary"><?php esc_html_e( 'Customize', 'semplicemente' ); ?></a></p>
					</div>

					<div class="col">
						<h3><?php esc_html_e( 'Got theme support question?', 'semplicemente' ); ?></h3>
						<p><?php esc_html_e( 'Please put it in our support forum.', 'semplicemente' ) ?></p>
						<p><a target="_blank" href="<?php echo esc_url( 'https://wordpress.org/support/theme/semplicemente/' ); ?>" class="button button-secondary"><?php esc_html_e( 'Support', 'semplicemente' ); ?></a></p>
					</div>

					<div class="col">
						<h3><?php esc_html_e( 'Need more features?', 'semplicemente' ); ?></h3>
						<p><?php esc_html_e( 'Upgrade to PRO version for more exciting features.', 'semplicemente' ) ?></p>
						<p><a target="_blank" href="<?php echo esc_url( 'https://crestaproject.com/downloads/semplicemente/' ); ?>" class="button button-secondary"><?php esc_html_e( 'Info about PRO version', 'semplicemente' ); ?></a></p>
					</div>

					<div class="col">
						<h3>
							<?php
							esc_html_e( 'Translate', 'semplicemente' );
							echo ' ' . esc_html($theme->display( 'Name' ));
							?>
						</h3>
						<p><?php esc_html_e( 'Click below to translate this theme into your own language.', 'semplicemente' ) ?></p>
						<p>
							<a target="_blank" href="<?php echo esc_url( 'http://translate.wordpress.org/projects/wp-themes/semplicemente/' ); ?>" class="button button-secondary">
								<?php
								esc_html_e( 'Translate', 'semplicemente' );
								echo ' ' . esc_html($theme->display( 'Name' ));
								?>
							</a>
						</p>
					</div>
				</div>
			</div>

			<div class="return-to-dashboard cresta">
				<?php if ( current_user_can( 'update_core' ) && isset( $_GET['updated'] ) ) : ?>
					<a href="<?php echo esc_url( self_admin_url( 'update-core.php' ) ); ?>">
						<?php is_multisite() ? esc_html_e( 'Return to Updates', 'semplicemente' ) : esc_html_e( 'Return to Dashboard &rarr; Updates', 'semplicemente' ); ?>
					</a> |
				<?php endif; ?>
				<a href="<?php echo esc_url( self_admin_url() ); ?>"><?php is_blog_admin() ? esc_html_e( 'Go to Dashboard &rarr; Home', 'semplicemente' ) : esc_html_e( 'Go to Dashboard', 'semplicemente' ); ?></a>
			</div>
		</div>
		<?php
	}

		/**
	 * Output the changelog screen.
	 */
	public function changelog_screen() {
		?>
		<div class="wrap about-wrap">

			<?php $this->intro(); ?>

			<p class="about-description"><?php esc_html_e( 'View changelog below:', 'semplicemente' ); ?></p>

			<?php
				$changelog_file = apply_filters( 'semplicemente_changelog_file', get_template_directory() . '/readme.txt' );

				// Check if the changelog file exists and is readable.
				if ( $changelog_file && is_readable( $changelog_file ) ) {
					$changelog = file_get_contents( $changelog_file );
					$changelog_list = $this->parse_changelog( $changelog );

					echo wp_kses_post( $changelog_list );
				}
			?>
		</div>
		<?php
	}

	/**
	 * Parse changelog from readme file.
	 * @param  string $content
	 * @return string
	 */
	private function parse_changelog( $content ) {
		$matches   = null;
		$regexp    = '~==\s*Changelog\s*==(.*)($)~Uis';
		$changelog = '';

		if ( preg_match( $regexp, $content, $matches ) ) {
			$changes = explode( '\r\n', trim( $matches[1] ) );

			$changelog .= '<pre class="changelog">';

			foreach ( $changes as $index => $line ) {
				$changelog .= wp_kses_post( preg_replace( '~(=\s*Version\s*(\d+(?:\.\d+)+)\s*=|$)~Uis', '<span class="title">${1}</span>', $line ) );
			}

			$changelog .= '</pre>';
		}

		return wp_kses_post( $changelog );
	}

	/**
	 * Output the free vs pro screen.
	 */
	public function free_vs_pro_screen() {
		?>
		<div class="wrap about-wrap">

			<?php $this->intro(); ?>

			<p class="about-description"><?php esc_html_e( 'Upgrade to PRO version for more exciting features.', 'semplicemente' ); ?></p>

			<table>
				<thead>
					<tr>
						<th class="table-feature-title"><h3><?php esc_html_e('Features', 'semplicemente'); ?></h3></th>
						<th><h3><?php esc_html_e('Semplicemente', 'semplicemente'); ?></h3></th>
						<th><h3><?php esc_html_e('Semplicemente PRO', 'semplicemente'); ?></h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><h3><?php esc_html_e('Responsive Design', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Change Background', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Unlimited Text Color', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Choose Social Incons', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('RTL Support', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Powerful theme options', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Loading Page', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Font switcher', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Breadcrumb', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Post views counter', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('7 Shortcodes', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('10 Exclusive Widgets', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Related Posts Box', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Information About Author Box', 'semplicemente'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td class="btn-wrapper">
							<a href="<?php echo esc_url( apply_filters( 'semplicemente_pro_theme_url', 'https://crestaproject.com/demo/semplicemente-pro/' ) ); ?>" class="button button-secondary" target="_blank"><?php esc_html_e( 'View PRO version demo', 'semplicemente' ); ?></a>
							<a href="<?php echo esc_url( apply_filters( 'semplicemente_pro_theme_url', 'https://crestaproject.com/downloads/semplicemente/' ) ); ?>" class="button button-secondary" target="_blank"><?php esc_html_e( 'More Information', 'semplicemente' ); ?></a>
						</td>
					</tr>
				</tbody>
			</table>

		</div>
		<?php
	}
}

endif;

return new Semplicemente_Admin();
