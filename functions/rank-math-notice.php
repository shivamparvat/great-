<?php

/**
 * Display admin notice for Rank Math SEO
 */
function rank_math_admin_notice() {
	global $current_user;
	$user_id        = $current_user->ID;
	$current_screen = get_current_screen();
	/* Check that the user hasn't already clicked to ignore the message */
	if ( ! is_admin() || ( 'dashboard' !== $current_screen->base ) || ! current_user_can( 'administrator' ) || get_option( 'rank_math_modules' ) || get_option( 'rm_autoinstall' ) ) {
		return;
	}

	$ignored = (int) get_user_meta( $user_id, 'rank_math_ignore_notice', true );
	if ( $ignored > 1 ) {
		return;
	} elseif ( 1 === $ignored ) {
		$ignore_date = (int) get_user_meta( $user_id, 'rank_math_ignore_notice_date', true );
		if ( ( $ignore_date + ( 30 * DAY_IN_SECONDS ) ) > time() ) {
			// Don't show for the next 30 days.
			return;
		}
	}

	$action = $url = $classes = '';

	$active_plugins = get_plugins();
	$rm_installed   = array_key_exists( 'seo-by-rank-math/rank-math.php', $active_plugins );
	if ( ! $rm_installed ) {
			$action  = __( 'Install for FREE', 'mythemeshop' );
			$url     = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=seo-by-rank-math' ), 'install-plugin_seo-by-rank-math' );
			$classes = 'install-rm install-now';
	} else {
			$action  = __( 'Activate it now', 'mythemeshop' );
			$url     = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=seo-by-rank-math/rank-math.php&from=try-rankmath' ), 'activate-plugin_seo-by-rank-math/rank-math.php' );
			$classes = 'activate-rm activate-now';
	}

	?>
	<style>
		.rm-notice {
			margin-right: 20px;
			margin-top: 50px;
		}
		#wrap .rm-notice {
			margin-right: 0;
			margin-top: 0;
		}
		.rm-notice ul {
			line-height: 1.3;
			overflow: hidden;
			display: flex;
			flex-wrap: wrap;
		}
		.rm-notice li {
			width: 48%;
		}
		.rm-notice li:nth-child(odd) {
			float: right;
		}
		.rm-notice .button.updating-message:before {
			margin-top: 0;
		}
	</style>
	<div class="notice rm-notice welcome-panel plugin-card-seo-by-rank-math" id="try-rankmath-panel" style="clear:both;">
		<div class="welcome-panel-content" style="overflow:hidden;">
			<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/rank-math-banner.jpg" alt="Rank Math SEO" style="float: left;max-width: 343px;margin-right: 20px;margin-bottom: -60px;border: 5px solid #ededed;" />
			<div style="overflow: hidden; position: relative;">
				<h2><?php esc_html_e( 'The Future of WordPress SEO is Here!', 'mythemeshop' ); ?></h2>
				<p class="about-description"><?php echo esc_attr( 'The new Rank Math SEO plugin will help you rank better in the search results.', 'mythemeshop' ); ?></p>
				<ul>
					<li>
						<i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Google Search Console Integration', 'mythemeshop' ); ?>
					</li>
					<li>
						<i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Google Keyword Ranking', 'mythemeshop' ); ?>
					</li>
					<li>
						<i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Google Keyword Suggession', 'mythemeshop' ); ?>
					</li>
					<li>
						<i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Google mythemeshop Rich Snippets', 'mythemeshop' ); ?>
					</li>
					<li>
						<i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Advanced SEO Analysis Tool', 'mythemeshop' ); ?>
					</li>
					<li>
						<i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Automated Image SEO', 'mythemeshop' ); ?>
					</li>
					<li>
						<i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'XML Sitemap', 'mythemeshop' ); ?>
					</li>
					<li>
						<i class="dashicons dashicons-yes"></i> <?php esc_html_e( '404 Monitor', 'mythemeshop' ); ?>
					</li>
					<li>
						<i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Redirection Manager', 'mythemeshop' ); ?>
					</li>
					<li>
						<i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Local SEO Optimization', 'mythemeshop' ); ?>
					</li>
					<li>
						<i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Lightweight', 'mythemeshop' ); ?>
					</li>
					<li>
						<i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'and much more...', 'mythemeshop' ); ?>
					</li>
				</ul>
				<a class="button button-primary button-hero <?php echo esc_attr( $classes ); ?>" href="<?php echo esc_url( $url ); ?>" style="margin-top: 0; margin-bottom: 20px;" data-slug="seo-by-rank-math" data-name="Rank Math"><?php echo $action; ?></a>
				<div style="opacity:0.4;font-size:12px;position:absolute;bottom:15px;right:0;">
						<?php esc_html_e( 'A MyThemeShop Product.', 'mythemeshop' ); ?>
				</div>
			</div>
			<a class="welcome-panel-close" id="wp_try_rankmath_panel-hide" href="<?php echo add_query_arg( 'rank_math_admin_notice_ignore', '1' ); ?>" aria-label="<?php esc_attr_e( 'Dismiss the Rank Math panel', 'mythemeshop' ); ?>"><?php esc_html_e( 'Dismiss', 'mythemeshop' ); ?></a>
			<?php wp_nonce_field( 'try-rankmath-panel-nonce', 'trygutenbergpanelnonce', false ); ?>
		</div>
		<script type="text/javascript">
			jQuery(document).ready( function($) {
				var tryRankmathPanel = $( '#try-rankmath-panel' ),
						updateTryRankmathPanel, installRankmathSuccess;

				updateTryRankmathPanel = function( visible ) {
					$.post( ajaxurl, {
						action: 'update-try-rankmath-panel',
						visible: visible,
						tryrankmathpanelnonce: $( '#tryrankmathpanelnonce' ).val()
					});
				};

				installRankmathSuccess = function( response ) {
					response.activateUrl += '&from=try-rankmath';
					response.activateLabel = wp.updates.l10n.activatePluginLabel.replace( '%s', response.pluginName );
					tryRankmathPanel.find('.install-now').text('<?php echo esc_js( __( 'Activating...', 'mythemeshop' ) ); ?>');
                    window.location.href = response.activateUrl;
				};

				tryRankmathPanel.on( 'click', '.install-now', function( e ) {
					e.preventDefault();
					var args = {
							slug: $( e.target ).data( 'slug' ),
							success: installRankmathSuccess
					};
					wp.updates.installPlugin( args );
				} );
			});
		</script>
	</div>
	<?php
}
add_action( 'admin_notices', 'rank_math_admin_notice' );

/**
 * Rank Math Notice ignore function
 */
function rank_math_admin_notice_ignore() {
	global $current_user;
	$user_id = $current_user->ID;
	if ( isset( $_GET['rank_math_admin_notice_ignore'] ) && $_GET['rank_math_admin_notice_ignore'] ) {
		$shown = (int) get_user_meta( $user_id, 'rank_math_ignore_notice', true );
		if ( $shown > 1  ) {
			return;
		}
		$last      = (int) get_user_meta( $user_id, 'rank_math_ignore_notice_date', true );
		$new_shown = 1;
		$new_date  = $last ? $last : time();
		if ( 1 === $shown && ( $last + ( 30 * DAY_IN_SECONDS ) ) < time() ) {
				$new_shown = 2;
				$new_date  = time();
		}
		update_user_meta( $user_id, 'rank_math_ignore_notice', $new_shown );
		update_user_meta( $user_id, 'rank_math_ignore_notice_date', $new_date );
	}
}
add_action( 'admin_init', 'rank_math_admin_notice_ignore' );
