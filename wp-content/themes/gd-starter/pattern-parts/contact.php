<!-- wp:html -->
<div class="gd-contact">
	<div class="gd-contact__form">
		<h2><?php echo gd_copy( 'contact.heading' ); ?></h2>
		<p class="gd-lede"><?php echo str_replace( 'Get involved', '<a href="/get-involved/">Get involved</a>', gd_copy( 'contact.lede' ) ); ?></p>
		[gd_contact_form]
	</div>
	<aside class="gd-contact__aside">
		<div class="gd-contact__card">
			<h3><?php echo gd_copy( 'contact.card1.title' ); ?></h3>
			<ul>
				<li><?php echo gd_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( gd_get( 'email' ) ); ?>"><?php echo esc_html( gd_get( 'email' ) ); ?></a></li>
				<li><?php echo gd_icon( 'instagram' ); ?><a href="<?php echo esc_url( gd_get( 'instagram' ) ); ?>" target="_blank" rel="noopener">@<?php echo esc_html( gd_site_config()['instagram_handle'] ?? '' ); ?></a></li>
				<li><?php echo gd_icon( 'pin' ); ?><span><?php echo esc_html( gd_get( 'address' ) ); ?></span></li>
			</ul>
		</div>
		<div class="gd-contact__card">
			<h3><?php echo gd_copy( 'contact.card2.title' ); ?></h3>
			<p><?php echo gd_copy( 'contact.card2.text' ); ?></p>
			<a class="gd-link" href="/get-involved/#request-help"><?php echo gd_copy( 'contact.card2.link' ); ?></a>
		</div>
		<div class="gd-contact__card">
			<h3><?php echo gd_copy( 'contact.card3.title' ); ?></h3>
			<p><?php echo gd_copy( 'contact.card3.text' ); ?></p>
			<a class="gd-link" href="/donate/"><?php echo gd_copy( 'contact.card3.link' ); ?></a>
		</div>
	</aside>
</div>
<!-- /wp:html -->
