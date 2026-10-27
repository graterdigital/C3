<?php
/** Full home page = the section patterns in order. */
foreach ( [ 'hero', 'trust-strip', 'intro', 'services', 'steps', 'doors', 'events-section', 'partners', 'instagram-section' ] as $p ) {
	include GD_THEME_DIR . '/pattern-parts/' . $p . '.php';
	echo "\n";
}
