<?php
defined( 'ABSPATH' ) || exit;

/**
 * Lightweight native enquiry form: [gd_contact_form]
 * Honeypot + timing check + nonce. Sends via wp_mail (set up SMTP on Kinsta — see DEPLOY.md).
 * Replace with Fluent Forms / WPForms later if the client wants a builder; the trek prefill works the same.
 */
add_shortcode( 'gd_contact_form', function () {
	$trek    = isset( $_GET['trip'] ) ? sanitize_text_field( wp_unslash( $_GET['trip'] ) ) : '';
	$sent    = isset( $_GET['sent'] );
	$error   = isset( $_GET['form_error'] ) ? sanitize_text_field( wp_unslash( $_GET['form_error'] ) ) : '';
	$options = gd_site_config()['enquiry_options'] ?? []; // e.g. [ 'Service A', 'Service B' ]
	ob_start();
	?>
	<div class="gd-form-wrap" id="enquire">
		<?php if ( $sent ) : ?>
			<div class="gd-form-success"><?php echo gd_icon( 'check' ); ?><div><strong>Thanks — we got your message.</strong><br>We’re volunteers, so give us a few days to reply.</div></div>
		<?php else : ?>
			<?php if ( $error ) : ?><p class="gd-form-error"><?php echo esc_html( $error ); ?></p><?php endif; ?>
			<form class="gd-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'gd_contact', 'gd_contact_nonce' ); ?>
				<input type="hidden" name="action" value="gd_contact">
				<input type="hidden" name="gd_ts" value="<?php echo esc_attr( (string) time() ); ?>">
				<input type="hidden" name="gd_return" value="<?php echo esc_url( get_permalink() ); ?>">
				<p class="gd-hp" aria-hidden="true"><label>Leave this empty <input type="text" name="gd_website" tabindex="-1" autocomplete="off"></label></p>

				<div class="gd-form__row">
					<label><span>Name</span><input type="text" name="gd_name" required autocomplete="name"></label>
					<label><span>Email</span><input type="email" name="gd_email" required autocomplete="email"></label>
				</div>
				<div class="gd-form__row">
					<?php if ( $options ) : ?>
					<label><span>This is about</span>
						<select name="gd_trek">
							<option value="">Not sure yet</option>
							<?php foreach ( $options as $opt ) : ?><option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $trek, $opt ); ?>><?php echo esc_html( $opt ); ?></option><?php endforeach; ?>
						</select>
					</label>
					<?php endif; ?>
					<label><span>Phone (optional)</span><input type="tel" name="gd_dates" autocomplete="tel"></label>
				</div>
				<div class="gd-form__row">
					<label><span>Organization (optional)</span><input type="text" name="gd_group" autocomplete="organization"></label>
					<label><span>How did you hear about us?</span><input type="text" name="gd_country"></label>
				</div>
				<label><span>How can we help?</span><textarea name="gd_message" rows="5"></textarea></label>
				<button type="submit" class="gd-btn gd-btn--primary">Send message <?php echo gd_icon( 'arrow' ); ?></button>
				<?php if ( gd_get( 'phone' ) ) : ?><p class="gd-form__note">Or call <a href="tel:+<?php echo esc_attr( gd_get( 'phone_e164' ) ); ?>"><?php echo esc_html( gd_get( 'phone' ) ); ?></a></p><?php endif; ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
} );

function gd_contact_handle(): void {
	$back = isset( $_POST['gd_return'] ) ? esc_url_raw( wp_unslash( $_POST['gd_return'] ) ) : home_url( '/contact/' );
	$fail = function ( string $msg ) use ( $back ): void {
		wp_safe_redirect( add_query_arg( 'form_error', rawurlencode( $msg ), $back ) . '#enquire' );
		exit;
	};

	if ( ! isset( $_POST['gd_contact_nonce'] ) || ! wp_verify_nonce( $_POST['gd_contact_nonce'], 'gd_contact' ) ) {
		$fail( 'Your session expired — please try again.' );
	}
	if ( ! empty( $_POST['gd_website'] ) || ( time() - (int) ( $_POST['gd_ts'] ?? 0 ) ) < 3 ) {
		$fail( 'Something went wrong. Please try again.' );
	}
	$f = fn( $k ) => sanitize_text_field( wp_unslash( $_POST[ $k ] ?? '' ) );
	$name = $f( 'gd_name' );
	$email = sanitize_email( wp_unslash( $_POST['gd_email'] ?? '' ) );
	$msg   = sanitize_textarea_field( wp_unslash( $_POST['gd_message'] ?? '' ) );
	if ( ! $name || ! is_email( $email ) ) {
		$fail( 'Please add your name and a valid email.' );
	}

	$body = sprintf(
		"New message from the website\n\nName: %s\nEmail: %s\nInterest: %s\nPhone: %s\nCompany: %s\nSource: %s\n\nMessage:\n%s\n\n—\nSent from %s",
		$name, $email, $f( 'gd_trek' ) ?: '-', $f( 'gd_dates' ) ?: '-', $f( 'gd_group' ) ?: '-', $f( 'gd_country' ) ?: '-', $msg ?: '-', home_url( '/' )
	);
	$to      = apply_filters( 'gd_contact_recipient', gd_get( 'email' ) ?: get_option( 'admin_email' ) );
	$subject = sprintf( '[C3] Message from %s%s', $name, $f( 'gd_trek' ) ? ' — ' . $f( 'gd_trek' ) : '' );
	$headers = [ 'Reply-To: ' . $name . ' <' . $email . '>' ];
	$ok      = wp_mail( $to, $subject, $body, $headers );

	do_action( 'gd_contact_submitted', compact( 'name', 'email', 'msg' ), $ok );
	if ( ! $ok ) {
		$fail( 'We could not send your message right now. Please email ' . gd_get( 'email' ) . '.' );
	}
	wp_safe_redirect( add_query_arg( 'sent', '1', $back ) . '#enquire' );
	exit;
}
add_action( 'admin_post_gd_contact', 'gd_contact_handle' );
add_action( 'admin_post_nopriv_gd_contact', 'gd_contact_handle' );
