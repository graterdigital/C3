<?php
defined( 'ABSPATH' ) || exit;

function gd_ig_get( string $key, $default = '' ) {
	$o = get_option( 'gd_instagram_options', [] );
	return $o[ $key ] ?? $default;
}
function gd_ig_set( array $pairs ): void {
	$o = get_option( 'gd_instagram_options', [] );
	update_option( 'gd_instagram_options', array_merge( $o, $pairs ), false );
}

function gd_ig_redirect_uri(): string {
	return admin_url( 'admin-post.php?action=gd_instagram_callback' );
}

add_action( 'admin_menu', function () {
	add_options_page( __( 'Instagram Feed', 'gd-instagram' ), __( 'Instagram Feed', 'gd-instagram' ), 'manage_options', 'gd-instagram', 'gd_ig_settings_page' );
} );

add_action( 'admin_init', function () {
	register_setting( 'gd_instagram', 'gd_instagram_options', [
		'type'              => 'array',
		'sanitize_callback' => function ( $in ) {
			$cur = get_option( 'gd_instagram_options', [] );
			$cur['app_id']       = sanitize_text_field( $in['app_id'] ?? '' );
			$cur['app_secret']   = sanitize_text_field( $in['app_secret'] ?? '' );
			$cur['cache_hours']  = max( 1, (int) ( $in['cache_hours'] ?? 6 ) );
			$cur['handle']       = ltrim( sanitize_text_field( $in['handle'] ?? '' ), '@' );
			$cur['source']       = in_array( $in['source'] ?? 'apify', [ 'apify', 'meta' ], true ) ? $in['source'] : 'apify';
			$cur['apify_token']  = sanitize_text_field( $in['apify_token'] ?? '' );
			$cur['apify_limit']  = max( 1, min( 50, (int) ( $in['apify_limit'] ?? 12 ) ) );
			$cur['apify_refresh_days'] = max( 1, (int) ( $in['apify_refresh_days'] ?? 7 ) );
			if ( ( $cur['handle'] ?? '' ) !== ( get_option( 'gd_instagram_options' )['handle'] ?? '' ) ) {
				delete_option( 'gd_instagram_last_refresh' ); // handle changed → refetch on next render/cron
			}
			if ( ! empty( $in['manual_token'] ) ) {
				$cur['access_token'] = sanitize_text_field( $in['manual_token'] );
				$cur['expires_at']   = time() + 60 * DAY_IN_SECONDS;
				$cur['user_id']      = '';
				delete_transient( 'gd_instagram_feed' );
			}
			return $cur;
		},
	] );
} );

function gd_ig_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$token   = gd_ig_get( 'access_token' );
	$expires = (int) gd_ig_get( 'expires_at' );
	$notice  = isset( $_GET['gd_ig'] ) ? sanitize_text_field( wp_unslash( $_GET['gd_ig'] ) ) : '';
	?>
	<div class="wrap">
		<h1>Instagram Feed</h1>
		<?php if ( $notice ) : ?><div class="notice notice-info is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div><?php endif; ?>

		<?php $src = gd_ig_get( 'source', 'apify' ); $last = get_option( 'gd_instagram_last_refresh', [] ); ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'gd_instagram' ); ?>
			<h2>Source</h2>
			<table class="form-table">
				<tr><th>Instagram handle</th><td>@<input class="regular-text" name="gd_instagram_options[handle]" value="<?php echo esc_attr( gd_ig_get( 'handle', '' ) ); ?>"></td></tr>
				<tr><th>Fetch via</th><td>
					<label><input type="radio" name="gd_instagram_options[source]" value="apify" <?php checked( $src, 'apify' ); ?>> <strong>Apify</strong> — scrapes the public profile. No Meta developer app, nothing for the client to approve. Images are copied into the Media Library.</label><br>
					<label><input type="radio" name="gd_instagram_options[source]" value="meta" <?php checked( $src, 'meta' ); ?>> <strong>Instagram API</strong> — official, needs a Meta app + the account owner to log in once (settings below).</label>
				</td></tr>
			</table>

			<h2>Apify</h2>
			<table class="form-table">
				<tr><th>Apify API token</th><td><input class="regular-text" type="password" name="gd_instagram_options[apify_token]" value="<?php echo esc_attr( gd_ig_get( 'apify_token' ) ); ?>" autocomplete="off"> <span class="description">same token as the Reviews plugin works</span></td></tr>
				<tr><th>Posts to keep</th><td><input type="number" min="1" max="50" style="width:80px" name="gd_instagram_options[apify_limit]" value="<?php echo esc_attr( gd_ig_get( 'apify_limit', 12 ) ); ?>"></td></tr>
				<tr><th>Refresh every</th><td><input type="number" min="1" style="width:80px" name="gd_instagram_options[apify_refresh_days]" value="<?php echo esc_attr( gd_ig_get( 'apify_refresh_days', 7 ) ); ?>"> days <span class="description">(~3¢ per refresh)</span></td></tr>
				<tr><th>Status</th><td><?php echo $last ? esc_html( ( $last['time'] ?? '' ) . ' — ' . ( $last['summary'] ?? '' ) ) : 'Not fetched yet.'; ?></td></tr>
			</table>
			<?php submit_button( 'Save settings' ); ?>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'gd_ig_apify_refresh' ); ?><input type="hidden" name="action" value="gd_instagram_apify_refresh">
			<?php submit_button( 'Refresh from Instagram now', 'secondary', 'submit', false ); ?>
		</form>

		<hr>
		<details <?php echo $src === 'meta' ? 'open' : ''; ?>>
		<summary style="cursor:pointer;font-size:1.2em;font-weight:600;margin:8px 0">Instagram API (official) — advanced</summary>
		<h3>Status</h3>
		<?php if ( $token ) : ?>
			<p>✅ Connected<?php echo gd_ig_get( 'username' ) ? ' as <strong>@' . esc_html( gd_ig_get( 'username' ) ) . '</strong>' : ''; ?>.
			Token expires <?php echo esc_html( $expires ? human_time_diff( time(), $expires ) : 'unknown' ); ?> from now (auto-refreshed daily).</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
				<?php wp_nonce_field( 'gd_ig_refresh' ); ?><input type="hidden" name="action" value="gd_instagram_refresh_now">
				<?php submit_button( 'Refresh token now', 'secondary', 'submit', false ); ?>
			</form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;margin-left:8px">
				<?php wp_nonce_field( 'gd_ig_clear' ); ?><input type="hidden" name="action" value="gd_instagram_clear_cache">
				<?php submit_button( 'Clear feed cache', 'secondary', 'submit', false ); ?>
			</form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;margin-left:8px">
				<?php wp_nonce_field( 'gd_ig_disconnect' ); ?><input type="hidden" name="action" value="gd_instagram_disconnect">
				<?php submit_button( 'Disconnect', 'delete', 'submit', false ); ?>
			</form>
		<?php else : ?>
			<p>Not connected.</p>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'gd_instagram' ); ?>
			<input type="hidden" name="gd_instagram_options[handle]" value="<?php echo esc_attr( gd_ig_get( 'handle' ) ); ?>">
			<input type="hidden" name="gd_instagram_options[source]" value="<?php echo esc_attr( $src ); ?>">
			<input type="hidden" name="gd_instagram_options[apify_token]" value="<?php echo esc_attr( gd_ig_get( 'apify_token' ) ); ?>">
			<input type="hidden" name="gd_instagram_options[apify_limit]" value="<?php echo esc_attr( gd_ig_get( 'apify_limit', 12 ) ); ?>">
			<input type="hidden" name="gd_instagram_options[apify_refresh_days]" value="<?php echo esc_attr( gd_ig_get( 'apify_refresh_days', 7 ) ); ?>">
			<p class="description">Meta app → Instagram → API setup with Instagram business login → Business login settings. Redirect URI:<br><code><?php echo esc_html( gd_ig_redirect_uri() ); ?></code></p>
			<table class="form-table">
				<tr><th>Instagram App ID</th><td><input class="regular-text" name="gd_instagram_options[app_id]" value="<?php echo esc_attr( gd_ig_get( 'app_id' ) ); ?>"></td></tr>
				<tr><th>Instagram App Secret</th><td><input class="regular-text" type="password" name="gd_instagram_options[app_secret]" value="<?php echo esc_attr( gd_ig_get( 'app_secret' ) ); ?>" autocomplete="off"></td></tr>
				<tr><th>Cache feed for</th><td><input type="number" min="1" name="gd_instagram_options[cache_hours]" value="<?php echo esc_attr( gd_ig_get( 'cache_hours', 6 ) ); ?>" style="width:80px"> hours</td></tr>
				<tr><th>Manual long-lived token</th><td><input class="large-text" name="gd_instagram_options[manual_token]" value="" autocomplete="off" placeholder="Optional — paste a token generated in the Meta dashboard instead of using Connect"></td></tr>
			</table>
			<?php submit_button( 'Save API settings' ); ?>
		</form>
		<?php if ( gd_ig_get( 'app_id' ) && gd_ig_get( 'app_secret' ) ) : ?>
			<a class="button button-primary" href="<?php echo esc_url( gd_ig_auth_url() ); ?>"><?php echo $token ? 'Reconnect Instagram' : 'Connect Instagram'; ?></a>
		<?php endif; ?>
		</details>

		<hr>
		<h2>Usage</h2>
		<p>Block: <strong>Instagram Feed</strong>. Shortcode: <code>[gd_instagram limit="8" columns="4" captions="0" header="1"]</code></p>
	</div>
	<?php
}
