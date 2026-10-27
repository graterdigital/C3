<?php
/**
 * SITE CONFIG — C3 · Creative Collaboration for Compassion
 * Everything here is a Customizer default; the client can override in Appearance → Customize.
 */
return [
	'name'          => 'C3 · Creative Collaboration for Compassion',
	'short_name'    => 'C3',
	'tagline'       => 'Be the change, every day.',
	'founded'       => '2026',
	'phone'         => '',
	'phone_e164'    => '',
	'whatsapp_text' => '',
	'email'         => 'C3creativecollaboration@gmail.com',
	'address'       => 'Marietta, Georgia — serving metro Atlanta',
	'facebook'      => '',
	'instagram'     => 'https://www.instagram.com/c3_creativecollaborative/',
	'instagram_handle' => 'c3_creativecollaborative',
	'tripadvisor'   => '',
	'google_url'    => '',
	'google_write_url' => '',
	'google_rating' => '',
	'google_count'  => '',

	// Giving
	'donate_url'    => 'https://gofund.me/3b486c0eb',
	'donate_label'  => 'Donate on GoFundMe',

	// Primary nav fallback (used until a menu is assigned)
	'nav'           => [ 'What we do' => '/what-we-do/', 'Events' => '/events/', 'About' => '/about/', 'Get involved' => '/get-involved/', 'Donate' => '/donate/' ],

	// Primary CTA (header button + footer band)
	'cta_label'     => 'Request help',
	'cta_url'       => '/get-involved/',
	'cta_heading'   => 'Need a repair? Have skills to share?',
	'cta_text'      => 'Tell us what’s broken, or tell us what you can do. Either way, you’re part of this.',
	'cta_secondary_label' => 'Volunteer',
	'cta_secondary_url'   => '/get-involved/#volunteer',

	// Design direction — see DIRECTIONS.md. Loads assets/css/directions/<name>.css after main.css.
	'direction'     => 'workshop',
	'fonts_url'     => 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap',
	'transparent_header' => false,

	// Show WhatsApp buttons? (needs phone_e164)
	'whatsapp'      => false,

	// schema.org type for the organization entity
	'schema_type'   => 'NGO',

	// Forms. Native forms are built in (inc/forms.php). To switch to Gravity Forms,
	// install GF and put the form IDs here — the Get Involved page swaps them in automatically.
	'gf_help_form_id'      => 0,
	'gf_volunteer_form_id' => 0,
	'form_recipient'       => '', // blank = the email above

	// Services shown on the Request Help form + home grid
	'services' => [
		'home'       => 'Home repairs & maintenance',
		'auto'       => 'Auto repairs',
		'emergency'  => 'Emergency repairs',
		'landscaping'=> 'Landscaping',
		'junk'       => 'Junk removal',
		'appliance'  => 'Appliance repair',
	],

	// Partners & affiliates (home + about). Logo files live in assets/img/; omit 'logo' for a text chip.
	'partners' => [
		[ 'name' => 'Black Lion Party, Georgia Chapter', 'logo' => '', 'url' => 'https://www.instagram.com/blpgeorgiachapter/' ],
		[ 'name' => 'Gwinnett Mutual Aid', 'logo' => 'partner-gma.png', 'url' => '' ],
		[ 'name' => 'Mutual Aid Athens', 'logo' => 'partner-maa.png', 'url' => '' ],
		[ 'name' => 'Mutual Aid Highway Connect', 'logo' => 'partner-mahc.png', 'url' => '' ],
		[ 'name' => 'North Georgia Community Action Network', 'logo' => 'partner-ngacan.png', 'url' => '' ],
		[ 'name' => 'Tha Pantherz', 'logo' => 'partner-thapantherz.png', 'url' => '' ],
		[ 'name' => 'All-African People’s Revolutionary Party', 'logo' => 'partner-aaprp.png', 'url' => '' ],
		[ 'name' => 'Above the Rain Collective', 'logo' => '', 'url' => '' ],
		[ 'name' => 'Reclaimed Karma', 'logo' => '', 'url' => '' ],
		[ 'name' => 'Joyful Noise Homeschool Group', 'logo' => '', 'url' => '' ],
	],
];
