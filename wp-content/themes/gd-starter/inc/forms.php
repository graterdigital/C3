<?php
defined( 'ABSPATH' ) || exit;

/**
 * Get Involved forms: [gd_request_help_form] and [gd_volunteer_form].
 *
 * Native, no plugin. Each submission:
 *   - is stored as a private "Form entry" post (Dashboard → Form entries) with all fields + photo attachments
 *   - is emailed to config/site.php 'form_recipient' (or 'email')
 * Honeypot + timing check + nonce; photos go through wp_handle_upload (images only, 8 MB each, max 5).
 *
 * Gravity Forms: set 'gf_help_form_id' / 'gf_volunteer_form_id' in config/site.php and the
 * Get Involved pattern renders [gravityform] instead of these — no other change needed.
 */

/* ---------------------------------------------------------------------- */
/* Entries CPT                                                              */
/* ---------------------------------------------------------------------- */
add_action( 'init', function () {
	register_post_type( 'gd_entry', [
		'labels' => [
			'name'          => __( 'Form entries', 'gd-starter' ),
			'singular_name' => __( 'Form entry', 'gd-starter' ),
			'all_items'     => __( 'All entries', 'gd-starter' ),
			'edit_item'     => __( 'Entry', 'gd-starter' ),
			'search_items'  => __( 'Search entries', 'gd-starter' ),
			'not_found'     => __( 'No entries yet', 'gd-starter' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-email-alt',
		'menu_position'       => 26,
		'supports'            => [ 'title' ],
		'capability_type'     => 'post',
		'capabilities'        => [ 'create_posts' => 'do_not_allow' ],
		'map_meta_cap'        => true,
		'exclude_from_search' => true,
	] );
	register_taxonomy( 'gd_entry_type', 'gd_entry', [
		'labels'            => [ 'name' => __( 'Type', 'gd-starter' ), 'singular_name' => __( 'Type', 'gd-starter' ) ],
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'hierarchical'      => true,
	] );
} );

/** Admin: show the submission in a read-only box, hide the editor. */
add_action( 'add_meta_boxes_gd_entry', function () {
	remove_meta_box( 'submitdiv', 'gd_entry', 'side' );
	add_meta_box( 'gd-entry', __( 'Submission', 'gd-starter' ), function ( WP_Post $post ) {
		$data = (array) get_post_meta( $post->ID, '_gd_entry', true );
		echo '<table class="widefat striped" style="max-width:900px"><tbody>';
		foreach ( $data as $label => $value ) {
			if ( $value === '' || $value === [] ) {
				continue;
			}
			echo '<tr><th style="width:260px;vertical-align:top">' . esc_html( $label ) . '</th><td>';
			if ( is_array( $value ) ) {
				foreach ( $value as $v ) {
					if ( is_int( $v ) && wp_attachment_is_image( $v ) ) {
						echo '<a href="' . esc_url( wp_get_attachment_url( $v ) ) . '" target="_blank" style="display:inline-block;margin:0 8px 8px 0">' . wp_get_attachment_image( $v, 'thumbnail' ) . '</a>';
					} else {
						echo esc_html( (string) $v ) . '<br>';
					}
				}
			} else {
				echo nl2br( esc_html( (string) $value ) );
			}
			echo '</td></tr>';
		}
		echo '</tbody></table>';
		echo '<p class="description">' . sprintf( esc_html__( 'Received %s from %s', 'gd-starter' ), esc_html( get_the_date( 'M j, Y g:ia', $post ) ), esc_html( get_post_meta( $post->ID, '_gd_entry_ip', true ) ) ) . '</p>';
	}, 'gd_entry', 'normal', 'high' );
} );
add_filter( 'manage_gd_entry_posts_columns', fn( $c ) => [ 'cb' => $c['cb'], 'title' => __( 'From', 'gd-starter' ), 'taxonomy-gd_entry_type' => __( 'Type', 'gd-starter' ), 'gd_area' => __( 'Area', 'gd-starter' ), 'date' => __( 'Received', 'gd-starter' ) ] );
add_action( 'manage_gd_entry_posts_custom_column', function ( $col, $id ) {
	if ( $col === 'gd_area' ) {
		$d = (array) get_post_meta( $id, '_gd_entry', true );
		echo esc_html( $d['Area / neighborhood'] ?? $d['Area you can serve'] ?? '' );
	}
}, 10, 2 );

/* ---------------------------------------------------------------------- */
/* Shared bits                                                              */
/* ---------------------------------------------------------------------- */
function gd_form_state(): array {
	return [
		'sent'  => isset( $_GET['sent'] ) ? sanitize_key( wp_unslash( $_GET['sent'] ) ) : '',
		'error' => isset( $_GET['form_error'] ) ? sanitize_text_field( wp_unslash( $_GET['form_error'] ) ) : '',
		'form'  => isset( $_GET['form'] ) ? sanitize_key( wp_unslash( $_GET['form'] ) ) : '',
	];
}

function gd_form_head( string $action, string $anchor ): void {
	wp_nonce_field( 'gd_form_' . $action, 'gd_form_nonce' );
	echo '<input type="hidden" name="action" value="' . esc_attr( $action ) . '">';
	echo '<input type="hidden" name="gd_ts" value="' . esc_attr( (string) time() ) . '">';
	echo '<input type="hidden" name="gd_return" value="' . esc_url( get_permalink() ) . '">';
	echo '<input type="hidden" name="gd_anchor" value="' . esc_attr( $anchor ) . '">';
	echo '<p class="gd-hp" aria-hidden="true"><label>Leave this empty <input type="text" name="gd_website" tabindex="-1" autocomplete="off"></label></p>';
}

function gd_form_success( string $title, string $text ): string {
	return '<div class="gd-form-success">' . gd_icon( 'check' ) . '<div><strong>' . esc_html( $title ) . '</strong><br>' . esc_html( $text ) . '</div></div>';
}

/* ---------------------------------------------------------------------- */
/* Request help                                                             */
/* ---------------------------------------------------------------------- */
add_shortcode( 'gd_request_help_form', function () {
	$st       = gd_form_state();
	$services = gd_site_config()['services'] ?? [];
	ob_start();
	if ( $st['sent'] === 'help' ) {
		echo gd_form_success( gd_copy_data()['involved.help.thanks'] ?? 'Thank you.', gd_copy_data()['involved.help.thankstext'] ?? '' );
		return ob_get_clean();
	}
	if ( $st['error'] && $st['form'] === 'help' ) {
		echo '<p class="gd-form-error">' . esc_html( $st['error'] ) . '</p>';
	}
	?>
	<form class="gd-form gd-form--help" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php gd_form_head( 'gd_request_help', 'request-help' ); ?>

		<fieldset>
			<legend>About you</legend>
			<div class="gd-form__row">
				<label><span>Your name</span><input type="text" name="name" required autocomplete="name"></label>
				<label><span>Email</span><input type="email" name="email" required autocomplete="email"></label>
			</div>
			<div class="gd-form__row">
				<label><span>Phone <small class="gd-field-help">optional — some volunteers prefer to text</small></span><input type="tel" name="phone" autocomplete="tel"></label>
				<label><span>Preferred contact</span>
					<select name="contact_pref"><option value="Email">Email</option><option value="Text">Text</option><option value="Phone call">Phone call</option></select>
				</label>
			</div>
			<label><span>Area / neighborhood <small class="gd-field-help">City or neighborhood is enough — we’ll ask for an exact address only when we’re ready to move forward. We sometimes help several neighbors in one area over a weekend.</small></span><input type="text" name="area" required placeholder="e.g. Marietta, near Kennesaw State"></label>
		</fieldset>

		<fieldset>
			<legend>The repair</legend>
			<label><span>What kind of help do you need?</span>
				<div class="gd-choices gd-choices--cols">
					<?php foreach ( $services as $key => $label ) : ?>
						<label class="gd-choice"><input type="checkbox" name="service[]" value="<?php echo esc_attr( $label ); ?>"> <?php echo esc_html( $label ); ?></label>
					<?php endforeach; ?>
					<label class="gd-choice"><input type="checkbox" name="service[]" value="Something else"> Something else</label>
				</div>
			</label>
			<label><span>Tell us about the repair <small class="gd-field-help">What’s broken, roughly how long it’s been that way, anything we should know before we show up.</small></span><textarea name="repair" rows="5" required></textarea></label>
			<label><span>How has this affected your quality of life?</span><textarea name="impact" rows="4"></textarea></label>
			<label><span>Photos <small class="gd-field-help">Up to 5 images, 8 MB each. A couple of photos help us bring the right tools and materials.</small></span><input type="file" name="photos[]" accept="image/*" multiple></label>
		</fieldset>

		<fieldset>
			<legend>Funding</legend>
			<label><span>Which describes your situation best?</span>
				<div class="gd-choices">
					<label class="gd-choice"><input type="radio" name="funding" value="I need 100% financial help with this project" required> I need 100% financial help with this project</label>
					<label class="gd-choice"><input type="radio" name="funding" value="I can contribute part of the cost (discount + funding project for a neighbor)"> I can contribute part of the cost — and I’d like my project to help fund a repair for another community member</label>
					<label class="gd-choice"><input type="radio" name="funding" value="I can pay full price (hire C3, fund a free repair)"> I can pay full price — hire C3 and fund a free repair for someone else</label>
				</div>
			</label>
			<label><span>Tell us a little about your struggle <small class="gd-field-help">It doesn’t need to be a life story. No income verification — we trust you.</small></span><textarea name="situation" rows="4"></textarea></label>
		</fieldset>

		<fieldset>
			<legend>A couple more things</legend>
			<label><span>Do you know anyone in greater need than you? <small class="gd-field-help">A name and how to reach them, or just a description. We’ll reach out gently.</small></span><textarea name="referral" rows="3"></textarea></label>
			<label><span>Any interest in volunteering? What’s your skill set?</span><textarea name="volunteer_interest" rows="3"></textarea></label>
			<label class="gd-choice gd-form__consent"><input type="checkbox" name="consent" value="yes" required> I understand C3 is a volunteer-run initiative, that repairs are offered as capacity allows, and that my information will only be used to coordinate help.</label>
		</fieldset>

		<button type="submit" class="gd-btn gd-btn--primary gd-btn--hero">Send my request</button>
		<p class="gd-form__note">Prefer email? Write to <a href="mailto:<?php echo esc_attr( gd_get( 'email' ) ); ?>"><?php echo esc_html( gd_get( 'email' ) ); ?></a>.</p>
	</form>
	<?php
	return ob_get_clean();
} );

/* ---------------------------------------------------------------------- */
/* Volunteer                                                                */
/* ---------------------------------------------------------------------- */
add_shortcode( 'gd_volunteer_form', function () {
	$st       = gd_form_state();
	$services = gd_site_config()['services'] ?? [];
	ob_start();
	if ( $st['sent'] === 'volunteer' ) {
		echo gd_form_success( gd_copy_data()['involved.volunteer.thanks'] ?? 'Thank you.', gd_copy_data()['involved.volunteer.thankstext'] ?? '' );
		return ob_get_clean();
	}
	if ( $st['error'] && $st['form'] === 'volunteer' ) {
		echo '<p class="gd-form-error">' . esc_html( $st['error'] ) . '</p>';
	}
	?>
	<form class="gd-form gd-form--volunteer" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php gd_form_head( 'gd_volunteer', 'volunteer' ); ?>

		<fieldset>
			<legend>About you</legend>
			<div class="gd-form__row">
				<label><span>Your name</span><input type="text" name="name" required autocomplete="name"></label>
				<label><span>Email</span><input type="email" name="email" required autocomplete="email"></label>
			</div>
			<div class="gd-form__row">
				<label><span>Phone <small class="gd-field-help">optional</small></span><input type="tel" name="phone" autocomplete="tel"></label>
				<label><span>Preferred contact</span>
					<select name="contact_pref"><option value="Email">Email</option><option value="Text">Text</option><option value="Phone call">Phone call</option><option value="Signal">Signal</option></select>
				</label>
			</div>
			<div class="gd-form__row">
				<label><span>Area you can serve</span><input type="text" name="area" required placeholder="e.g. Cobb County, north metro"></label>
				<label><span>Business or organization <small class="gd-field-help">optional</small></span><input type="text" name="org" autocomplete="organization"></label>
			</div>
		</fieldset>

		<fieldset>
			<legend>How you’d like to help</legend>
			<label><span>I can help with</span>
				<div class="gd-choices gd-choices--cols">
					<?php foreach ( $services as $key => $label ) : ?>
						<label class="gd-choice"><input type="checkbox" name="help_with[]" value="<?php echo esc_attr( $label ); ?>"> <?php echo esc_html( $label ); ?></label>
					<?php endforeach; ?>
					<label class="gd-choice"><input type="checkbox" name="help_with[]" value="Hauling / truck or trailer"> Hauling — I have a truck or trailer</label>
					<label class="gd-choice"><input type="checkbox" name="help_with[]" value="Events, outreach, art & crafts"> Events, outreach, art &amp; crafts</label>
					<label class="gd-choice"><input type="checkbox" name="help_with[]" value="Materials, tools or funding"> Materials, tools or funding</label>
					<label class="gd-choice"><input type="checkbox" name="help_with[]" value="I want to learn a skill"> I want to learn a skill</label>
				</div>
			</label>
			<label><span>Skills, interests, what you’d like to learn</span><textarea name="skills" rows="4" required placeholder="Licensed electrician, 10 years. / Handy with basic carpentry, happy to be an extra pair of hands. / No skills yet but eager to learn."></textarea></label>
			<label><span>Availability</span>
				<div class="gd-choices gd-choices--cols">
					<label class="gd-choice"><input type="checkbox" name="availability[]" value="Weekends"> Weekends</label>
					<label class="gd-choice"><input type="checkbox" name="availability[]" value="Weekday evenings"> Weekday evenings</label>
					<label class="gd-choice"><input type="checkbox" name="availability[]" value="Weekdays"> Weekdays</label>
					<label class="gd-choice"><input type="checkbox" name="availability[]" value="On call for emergencies"> On call for emergencies</label>
				</div>
			</label>
			<label><span>Business: can you offer pro-bono or low-cost services?</span>
				<select name="business_offer"><option value="">Not applicable</option><option value="Pro-bono">Yes — pro-bono</option><option value="Low-cost">Yes — low-cost</option><option value="Materials / in-kind">Materials or in-kind</option><option value="Match funds">Match funds on a project</option></select>
			</label>
			<label><span>Anything else?</span><textarea name="notes" rows="3"></textarea></label>
			<label class="gd-choice gd-form__consent"><input type="checkbox" name="chat" value="yes"> Add me to the volunteer group chat (Signal/Discord) when it’s set up.</label>
		</fieldset>

		<button type="submit" class="gd-btn gd-btn--primary gd-btn--hero">Sign me up</button>
	</form>
	<?php
	return ob_get_clean();
} );

/* ---------------------------------------------------------------------- */
/* Handlers                                                                 */
/* ---------------------------------------------------------------------- */
function gd_forms_handle( string $which ): void {
	$back   = isset( $_POST['gd_return'] ) ? esc_url_raw( wp_unslash( $_POST['gd_return'] ) ) : home_url( '/get-involved/' );
	$anchor = isset( $_POST['gd_anchor'] ) ? sanitize_key( wp_unslash( $_POST['gd_anchor'] ) ) : $which;
	$fail   = function ( string $msg ) use ( $back, $anchor, $which ): void {
		wp_safe_redirect( add_query_arg( [ 'form' => $which, 'form_error' => rawurlencode( $msg ) ], $back ) . '#' . $anchor );
		exit;
	};
	if ( ! isset( $_POST['gd_form_nonce'] ) || ! wp_verify_nonce( $_POST['gd_form_nonce'], 'gd_form_gd_' . ( $which === 'help' ? 'request_help' : 'volunteer' ) ) ) {
		$fail( 'Your session expired — please try again.' );
	}
	if ( ! empty( $_POST['gd_website'] ) || ( time() - (int) ( $_POST['gd_ts'] ?? 0 ) ) < 4 ) {
		$fail( 'Something went wrong. Please try again.' );
	}
	$txt  = fn( $k ) => sanitize_text_field( wp_unslash( $_POST[ $k ] ?? '' ) );
	$area = fn( $k ) => sanitize_textarea_field( wp_unslash( $_POST[ $k ] ?? '' ) );
	$list = fn( $k ) => array_values( array_filter( array_map( 'sanitize_text_field', (array) wp_unslash( $_POST[ $k ] ?? [] ) ) ) );

	$name  = $txt( 'name' );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	if ( ! $name || ! is_email( $email ) ) {
		$fail( 'Please add your name and a valid email.' );
	}

	if ( $which === 'help' ) {
		if ( empty( $_POST['consent'] ) ) {
			$fail( 'Please tick the box at the bottom so we know you understand how C3 works.' );
		}
		$data = [
			'Name'                        => $name,
			'Email'                       => $email,
			'Phone'                       => $txt( 'phone' ),
			'Preferred contact'           => $txt( 'contact_pref' ),
			'Area / neighborhood'         => $txt( 'area' ),
			'Help needed'                 => $list( 'service' ),
			'About the repair'            => $area( 'repair' ),
			'Impact on quality of life'   => $area( 'impact' ),
			'Funding'                     => $txt( 'funding' ),
			'Situation'                   => $area( 'situation' ),
			'Knows someone in greater need' => $area( 'referral' ),
			'Volunteer interest / skills' => $area( 'volunteer_interest' ),
		];
		// Photos.
		$ids = [];
		if ( ! empty( $_FILES['photos']['name'][0] ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$files = $_FILES['photos'];
			$n     = min( 5, count( $files['name'] ) );
			for ( $i = 0; $i < $n; $i++ ) {
				if ( $files['error'][ $i ] !== UPLOAD_ERR_OK || $files['size'][ $i ] > 8 * MB_IN_BYTES ) {
					continue;
				}
				$check = wp_check_filetype_and_ext( $files['tmp_name'][ $i ], $files['name'][ $i ] );
				if ( ! str_starts_with( (string) $check['type'], 'image/' ) ) {
					continue;
				}
				$_FILES['gd_photo'] = [ 'name' => $files['name'][ $i ], 'type' => $files['type'][ $i ], 'tmp_name' => $files['tmp_name'][ $i ], 'error' => 0, 'size' => $files['size'][ $i ] ];
				$id = media_handle_upload( 'gd_photo', 0, [ 'post_title' => 'Request photo — ' . $name ] );
				if ( ! is_wp_error( $id ) ) {
					$ids[] = (int) $id;
				}
			}
		}
		$data['Photos'] = $ids;
		$type    = 'Request help';
		$subject = sprintf( '[C3] Repair request — %s (%s)', $name, $data['Area / neighborhood'] ?: 'area not given' );
	} else {
		$data = [
			'Name'                 => $name,
			'Email'                => $email,
			'Phone'                => $txt( 'phone' ),
			'Preferred contact'    => $txt( 'contact_pref' ),
			'Area you can serve'   => $txt( 'area' ),
			'Business / org'       => $txt( 'org' ),
			'Can help with'        => $list( 'help_with' ),
			'Skills / interests'   => $area( 'skills' ),
			'Availability'         => $list( 'availability' ),
			'Business offer'       => $txt( 'business_offer' ),
			'Notes'                => $area( 'notes' ),
			'Join group chat'      => ! empty( $_POST['chat'] ) ? 'Yes' : 'No',
		];
		$type    = 'Volunteer';
		$subject = sprintf( '[C3] New volunteer — %s (%s)', $name, $data['Area you can serve'] ?: 'area not given' );
	}

	// Store.
	$post_id = wp_insert_post( [
		'post_type'   => 'gd_entry',
		'post_status' => 'private',
		'post_title'  => $name . ' — ' . $type,
	], true );
	if ( ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_gd_entry', $data );
		update_post_meta( $post_id, '_gd_entry_ip', sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		wp_set_object_terms( $post_id, $type, 'gd_entry_type' );
		foreach ( $data['Photos'] ?? [] as $aid ) {
			wp_update_post( [ 'ID' => $aid, 'post_parent' => $post_id ] );
		}
	}

	// Email.
	$lines = [ $type . ' submission from the website', '' ];
	foreach ( $data as $label => $value ) {
		if ( is_array( $value ) ) {
			$value = $label === 'Photos' ? implode( "\n  ", array_map( 'wp_get_attachment_url', $value ) ) : implode( ', ', $value );
		}
		if ( $value !== '' ) {
			$lines[] = $label . ': ' . $value;
		}
	}
	if ( ! is_wp_error( $post_id ) ) {
		$lines[] = '';
		$lines[] = 'View in WordPress: ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' );
	}
	$to = gd_site_config()['form_recipient'] ?: ( gd_get( 'email' ) ?: get_option( 'admin_email' ) );
	$ok = wp_mail( apply_filters( 'gd_form_recipient', $to, $which ), $subject, implode( "\n", $lines ), [ 'Reply-To: ' . $name . ' <' . $email . '>' ] );
	do_action( 'gd_form_submitted', $which, $data, $post_id, $ok );

	// Even if mail fails, the entry is stored — don't make the person retry.
	wp_safe_redirect( add_query_arg( 'sent', $which, $back ) . '#' . $anchor );
	exit;
}
add_action( 'admin_post_gd_request_help', fn() => gd_forms_handle( 'help' ) );
add_action( 'admin_post_nopriv_gd_request_help', fn() => gd_forms_handle( 'help' ) );
add_action( 'admin_post_gd_volunteer', fn() => gd_forms_handle( 'volunteer' ) );
add_action( 'admin_post_nopriv_gd_volunteer', fn() => gd_forms_handle( 'volunteer' ) );

/** Mark the entries menu with the count of entries from the last 7 days. */
add_action( 'admin_menu', function () {
	global $menu;
	$n = (int) ( new WP_Query( [ 'post_type' => 'gd_entry', 'post_status' => 'private', 'date_query' => [ [ 'after' => '7 days ago' ] ], 'fields' => 'ids', 'posts_per_page' => -1 ] ) )->found_posts;
	if ( ! $n ) {
		return;
	}
	foreach ( $menu as &$m ) {
		if ( $m[2] === 'edit.php?post_type=gd_entry' ) {
			$m[0] .= ' <span class="awaiting-mod">' . $n . '</span>';
		}
	}
}, 99 );
