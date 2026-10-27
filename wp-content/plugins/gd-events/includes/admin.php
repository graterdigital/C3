<?php
defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes_event', function () {
	add_meta_box( 'gd-event-details', __( 'Event details', 'gd-events' ), 'gd_events_render_meta_box', 'event', 'normal', 'high' );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( in_array( $hook, [ 'post.php', 'post-new.php' ], true ) && get_current_screen()?->post_type === 'event' ) {
		wp_add_inline_style( 'wp-admin', '.gd-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px 20px}.gd-meta-field{display:grid;gap:4px}.gd-meta-field--wide{grid-column:1/-1}.gd-meta-field label{font-weight:600}.gd-meta-field input[type=text],.gd-meta-field input[type=url],.gd-meta-field input[type=datetime-local]{width:100%}.gd-meta-field .description{margin:0}' );
	}
} );

function gd_events_render_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'gd_events_save', 'gd_events_nonce' );
	echo '<div class="gd-meta-grid">';
	foreach ( gd_events_fields() as $key => $f ) {
		$id    = 'gd_' . $key;
		$value = (string) get_post_meta( $post->ID, '_gd_' . $key, true );
		$wide  = in_array( $key, [ 'address', 'link' ], true ) ? ' gd-meta-field--wide' : '';
		echo '<div class="gd-meta-field' . $wide . '">';
		switch ( $f['type'] ) {
			case 'datetime':
				printf( '<label for="%1$s">%2$s%3$s</label><input type="datetime-local" id="%1$s" name="%1$s" value="%4$s"%5$s>', esc_attr( $id ), esc_html( $f['label'] ), ! empty( $f['required'] ) ? ' *' : '', esc_attr( $value ? str_replace( ' ', 'T', $value ) : '' ), ! empty( $f['required'] ) ? ' required' : '' );
				break;
			case 'checkbox':
				printf( '<label><input type="checkbox" id="%1$s" name="%1$s" value="1"%3$s> %2$s</label>', esc_attr( $id ), esc_html( $f['label'] ), checked( $value, '1', false ) );
				break;
			case 'url':
				printf( '<label for="%1$s">%2$s</label><input type="url" id="%1$s" name="%1$s" value="%3$s" placeholder="https://">', esc_attr( $id ), esc_html( $f['label'] ), esc_attr( $value ) );
				break;
			default:
				printf( '<label for="%1$s">%2$s</label><input type="text" id="%1$s" name="%1$s" value="%3$s" placeholder="%4$s">', esc_attr( $id ), esc_html( $f['label'] ), esc_attr( $value ), esc_attr( $f['placeholder'] ?? '' ) );
		}
		if ( ! empty( $f['help'] ) ) {
			echo '<p class="description">' . esc_html( $f['help'] ) . '</p>';
		}
		echo '</div>';
	}
	echo '</div>';
	echo '<p class="description" style="margin-top:12px">' . esc_html__( 'The featured image is the event poster — set it in the sidebar. Past events stay published and move to the “Past events” list automatically.', 'gd-events' ) . '</p>';
}

add_action( 'save_post_event', function ( int $post_id ) {
	if ( ! isset( $_POST['gd_events_nonce'] ) || ! wp_verify_nonce( $_POST['gd_events_nonce'], 'gd_events_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( gd_events_fields() as $key => $f ) {
		$raw = $_POST[ 'gd_' . $key ] ?? '';
		$val = gd_events_sanitize_field( $key, is_string( $raw ) ? wp_unslash( $raw ) : $raw );
		if ( $val === '' ) {
			delete_post_meta( $post_id, '_gd_' . $key );
		} else {
			update_post_meta( $post_id, '_gd_' . $key, $val );
		}
	}
} );
