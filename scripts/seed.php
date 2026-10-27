<?php
/**
 * SEED SKELETON — builds pages, menus, logo and sample content from content/site.json.
 *
 *   wp eval-file scripts/seed.php                 (imports images from theme assets/img + content/images.txt)
 *   LA_SKIP_IMAGES=1 wp eval-file scripts/seed.php
 *
 * Idempotent: pages/posts are matched by slug and updated in place.
 *
 * content/site.json shape:
 * {
 *   "site": { "title": "...", "tagline": "...", "timezone": "America/New_York" },
 *   "pages": [
 *     { "slug": "home", "title": "...", "pattern": "home", "canvas": true, "front": true },
 *     { "slug": "about", "title": "...", "excerpt": "...", "hero": "about.jpg",
 *       "blocks": [ { "h2": "..." }, { "p": "..." }, { "list": ["a","b"] }, { "image": "file.jpg", "alt": "..." }, { "pattern": "faq" }, { "shortcode": "[gd_contact_form]" } ] },
 *     ...
 *   ],
 *   "menus": { "primary": [ ["Label", "slug-or-url", "ParentLabel|null"], ... ], "footer": [ ... ] },
 *   "logo": "logo.svg"
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run via: wp eval-file scripts/seed.php\n" );
}

$ROOT        = dirname( __DIR__ );
$site        = json_decode( file_get_contents( $ROOT . '/content/site.json' ), true );
$skip_images = (bool) getenv( 'LA_SKIP_IMAGES' );

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/* ---------------------------------------------------------------------- */
/* Images: theme assets/img first, then remote URLs listed in images.txt    */
/* ---------------------------------------------------------------------- */
$image_map = get_option( 'gd_seed_image_map', [] );
$image_src = [];
$list      = $ROOT . '/content/images.txt';
if ( file_exists( $list ) ) {
	foreach ( file( $list, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) as $line ) {
		if ( $line[0] !== '#' ) {
			$image_src[ basename( $line ) ] = $line;
		}
	}
}

function gd_seed_image( string $file, string $alt = '' ): int {
	global $image_map, $image_src, $skip_images;
	$local = get_template_directory() . '/assets/img/' . $file;
	$hash  = file_exists( $local ) ? md5_file( $local ) : '';
	$hashes = (array) get_option( 'gd_seed_image_hash', [] );
	if ( isset( $image_map[ $file ] ) && get_post( $image_map[ $file ] ) && ( ! $hash || ( $hashes[ $file ] ?? '' ) === $hash ) ) {
		return (int) $image_map[ $file ];
	}
	if ( isset( $image_map[ $file ] ) && $hash && ( $hashes[ $file ] ?? '' ) !== $hash ) {
		WP_CLI::log( "  image $file changed → re-importing" );
	}
	if ( $skip_images ) {
		return 0;
	}
	$title = $alt ?: ucwords( str_replace( [ '-', '_' ], ' ', pathinfo( $file, PATHINFO_FILENAME ) ) );
	if ( file_exists( $local ) ) {
		$tmp = wp_tempnam( $file );
		copy( $local, $tmp );
		$id = media_handle_sideload( [ 'name' => $file, 'tmp_name' => $tmp ], 0, $alt, [ 'post_title' => $title ] );
	} elseif ( ! empty( $image_src[ $file ] ) ) {
		$id = media_sideload_image( $image_src[ $file ], 0, $alt, 'id' );
	} else {
		return 0;
	}
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( "Image $file: " . $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	$image_map[ $file ] = (int) $id;
	update_option( 'gd_seed_image_map', $image_map, false );
	if ( $hash ) {
		$hashes[ $file ] = $hash;
		update_option( 'gd_seed_image_hash', $hashes, false );
	}
	WP_CLI::log( "  image $file → #$id" );
	return (int) $id;
}

/* ---------------------------------------------------------------------- */
/* Block helpers                                                            */
/* ---------------------------------------------------------------------- */
function gd_p( string $text ): string {
	$out = '';
	foreach ( preg_split( '/\n\s*\n/', trim( $text ) ) as $para ) {
		if ( trim( $para ) !== '' ) {
			$out .= "<!-- wp:paragraph -->\n<p>" . nl2br( esc_html( trim( $para ) ) ) . "</p>\n<!-- /wp:paragraph -->\n\n";
		}
	}
	return $out;
}
function gd_h( string $text, int $level = 2 ): string {
	return "<!-- wp:heading {\"level\":$level} -->\n<h$level class=\"wp-block-heading\">" . esc_html( $text ) . "</h$level>\n<!-- /wp:heading -->\n\n";
}
function gd_list( array $items ): string {
	return "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . implode( '', array_map( fn( $i ) => '<li>' . esc_html( $i ) . '</li>', $items ) ) . "</ul>\n<!-- /wp:list -->\n\n";
}
function gd_image_block( string $file, string $alt = '' ): string {
	$id = gd_seed_image( $file, $alt );
	if ( ! $id ) {
		return '';
	}
	$url = wp_get_attachment_image_url( $id, 'large' );
	return "<!-- wp:image {\"id\":$id,\"sizeSlug\":\"large\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"" . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . "\" class=\"wp-image-$id\"/></figure>\n<!-- /wp:image -->\n\n";
}
function gd_pattern( string $slug ): string {
	$p = WP_Block_Patterns_Registry::get_instance()->get_registered( 'gd-starter/' . $slug );
	return $p ? $p['content'] . "\n" : '';
}
function gd_blocks( array $blocks ): string {
	$out = '';
	foreach ( $blocks as $b ) {
		$out .= match ( true ) {
			isset( $b['h2'] )        => gd_h( $b['h2'] ),
			isset( $b['h3'] )        => gd_h( $b['h3'], 3 ),
			isset( $b['p'] )         => gd_p( $b['p'] ),
			isset( $b['list'] )      => gd_list( $b['list'] ),
			isset( $b['image'] )     => gd_image_block( $b['image'], $b['alt'] ?? '' ),
			isset( $b['pattern'] )   => gd_pattern( $b['pattern'] ),
			isset( $b['shortcode'] ) => "<!-- wp:shortcode -->\n" . $b['shortcode'] . "\n<!-- /wp:shortcode -->\n\n",
			isset( $b['html'] )      => "<!-- wp:html -->\n" . $b['html'] . "\n<!-- /wp:html -->\n\n",
			default                  => '',
		};
	}
	return $out;
}

function gd_upsert( array $args, array $meta = [] ): int {
	$existing = get_page_by_path( $args['post_name'], OBJECT, $args['post_type'] );
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		$id         = wp_update_post( $args, true );
	} else {
		$id = wp_insert_post( $args, true );
	}
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}
	WP_CLI::log( sprintf( '  %s %s #%d (%s)', $existing ? 'updated' : 'created', $args['post_type'], $id, $args['post_name'] ) );
	return (int) $id;
}

/* ---------------------------------------------------------------------- */
/* Pattern copy → theme (survives rm -rf content on the host)              */
/* ---------------------------------------------------------------------- */
if ( file_exists( $ROOT . '/content/copy.json' ) ) {
	@mkdir( get_template_directory() . '/content' );
	copy( $ROOT . '/content/copy.json', get_template_directory() . '/content/copy.json' );
	WP_CLI::log( '  copy.json → theme/content/' );
}

/* ---------------------------------------------------------------------- */
/* Pages                                                                    */
/* ---------------------------------------------------------------------- */
WP_CLI::log( "\n== Pages ==" );
$ids = [];
foreach ( $site['pages'] as $pg ) {
	$content = isset( $pg['pattern'] ) ? gd_pattern( $pg['pattern'] ) : gd_blocks( $pg['blocks'] ?? [] );
	$id      = gd_upsert( [
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $pg['title'],
		'post_name'    => $pg['slug'],
		'post_excerpt' => $pg['excerpt'] ?? '',
		'post_content' => $content,
	], [ '_gd_canvas' => ! empty( $pg['canvas'] ) ? '1' : '' ] );
	$ids[ $pg['slug'] ] = $id;
	if ( ! empty( $pg['hero'] ) && ( $t = gd_seed_image( $pg['hero'], $pg['title'] ) ) ) {
		set_post_thumbnail( $id, $t );
	}
	if ( ! empty( $pg['front'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $id );
	}
	if ( ! empty( $pg['posts_page'] ) ) {
		update_option( 'page_for_posts', $id );
	}
}

/* ---------------------------------------------------------------------- */
/* Events (gd-events) + posts                                               */
/* ---------------------------------------------------------------------- */
if ( ! empty( $site['events'] ) && post_type_exists( 'event' ) ) {
	WP_CLI::log( "\n== Events ==" );
	foreach ( $site['events'] as $ev ) {
		$id = gd_upsert( [
			'post_type'    => 'event',
			'post_status'  => $ev['status'] ?? 'publish',
			'post_title'   => $ev['title'],
			'post_name'    => $ev['slug'],
			'post_excerpt' => $ev['excerpt'] ?? '',
			'post_content' => gd_p( $ev['content'] ?? '' ),
		] );
		foreach ( [ 'start', 'end', 'all_day', 'venue', 'address', 'cost', 'organizer', 'link', 'link_label' ] as $k ) {
			if ( isset( $ev[ $k ] ) && $ev[ $k ] !== '' ) {
				update_post_meta( $id, '_gd_' . $k, gd_events_sanitize_field( $k, $ev[ $k ] ) );
			} else {
				delete_post_meta( $id, '_gd_' . $k );
			}
		}
		if ( ! empty( $ev['image'] ) && ( $t = gd_seed_image( $ev['image'], $ev['title'] ) ) ) {
			set_post_thumbnail( $id, $t );
		}
	}
}
if ( ! empty( $site['posts'] ) ) {
	WP_CLI::log( "\n== Posts ==" );
	foreach ( $site['posts'] as $po ) {
		$id = gd_upsert( [
			'post_type'    => 'post',
			'post_status'  => $po['status'] ?? 'publish',
			'post_title'   => $po['title'],
			'post_name'    => $po['slug'],
			'post_excerpt' => $po['excerpt'] ?? '',
			'post_content' => gd_p( $po['content'] ?? '' ),
			'post_date'    => isset( $po['date'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $po['date'] ) ) : current_time( 'mysql' ),
		] );
		if ( ! empty( $po['image'] ) && ( $t = gd_seed_image( $po['image'], $po['title'] ) ) ) {
			set_post_thumbnail( $id, $t );
		}
	}
	// Retire the stock "Hello world!" post if it's still there.
	if ( ( $hello = get_page_by_path( 'hello-world', OBJECT, 'post' ) ) ) {
		wp_trash_post( $hello->ID );
		WP_CLI::log( '  trashed hello-world' );
	}
	if ( ( $sample = get_page_by_path( 'sample-page', OBJECT, 'page' ) ) ) {
		wp_trash_post( $sample->ID );
		WP_CLI::log( '  trashed sample-page' );
	}
}

/* ---------------------------------------------------------------------- */
/* Menus                                                                    */
/* ---------------------------------------------------------------------- */
WP_CLI::log( "\n== Menus ==" );
function gd_menu( string $name, string $location, array $items, array $ids ): void {
	$menu = wp_get_nav_menu_object( $name );
	$id   = $menu ? $menu->term_id : wp_create_nav_menu( $name );
	foreach ( wp_get_nav_menu_items( $id ) ?: [] as $it ) {
		wp_delete_post( $it->ID, true );
	}
	$parents = [];
	foreach ( $items as $i => [ $title, $target, $parent ] ) {
		$args = [ 'menu-item-title' => $title, 'menu-item-status' => 'publish', 'menu-item-position' => $i + 1 ];
		if ( isset( $ids[ $target ] ) ) {
			$args += [ 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $ids[ $target ] ];
		} else {
			$args += [ 'menu-item-type' => 'custom', 'menu-item-url' => str_starts_with( $target, 'http' ) ? $target : home_url( $target ) ];
		}
		if ( $parent && isset( $parents[ $parent ] ) ) {
			$args['menu-item-parent-id'] = $parents[ $parent ];
		}
		$parents[ $title ] = wp_update_nav_menu_item( $id, 0, $args );
	}
	$locs              = get_theme_mod( 'nav_menu_locations', [] );
	$locs[ $location ] = $id;
	set_theme_mod( 'nav_menu_locations', $locs );
	WP_CLI::log( "  menu '$name' → $location" );
}
foreach ( $site['menus'] ?? [] as $loc => $items ) {
	gd_menu( ucfirst( $loc ), $loc, $items, $ids );
}

/* ---------------------------------------------------------------------- */
/* Import every theme photo so gd_img() resolves to the Media Library       */
/* ---------------------------------------------------------------------- */
WP_CLI::log( "\n== Theme images ==" );
foreach ( glob( get_template_directory() . '/assets/img/*.{jpg,jpeg,png,webp}', GLOB_BRACE ) ?: [] as $f ) {
	gd_seed_image( basename( $f ), ucwords( str_replace( [ '-', '_' ], ' ', pathinfo( $f, PATHINFO_FILENAME ) ) ) );
}

/* ---------------------------------------------------------------------- */
/* Site options + logo                                                      */
/* ---------------------------------------------------------------------- */
update_option( 'blogname', $site['site']['title'] );
update_option( 'blogdescription', $site['site']['tagline'] ?? '' );
if ( ! empty( $site['site']['timezone'] ) ) {
	update_option( 'timezone_string', $site['site']['timezone'] );
}
if ( ! empty( $site['logo'] ) && ( $logo = gd_seed_image( $site['logo'], $site['site']['title'] ) ) ) {
	set_theme_mod( 'custom_logo', $logo );
}
flush_rewrite_rules();
WP_CLI::success( 'Seed complete. Front page: ' . home_url( '/' ) );
