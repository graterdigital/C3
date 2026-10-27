<?php
$cfg  = gd_site_config();
$gf   = function_exists( 'gravity_form' ) || class_exists( 'GFForms' );
$help = $gf && ! empty( $cfg['gf_help_form_id'] )      ? '[gravityform id="' . (int) $cfg['gf_help_form_id'] . '" title="false" description="false" ajax="true"]'      : '[gd_request_help_form]';
$vol  = $gf && ! empty( $cfg['gf_volunteer_form_id'] ) ? '[gravityform id="' . (int) $cfg['gf_volunteer_form_id'] . '" title="false" description="false" ajax="true"]' : '[gd_volunteer_form]';
?>
<!-- wp:html -->
<div class="gd-involved" data-gd-switch>
	<ul class="gd-switch alignwide" role="tablist">
		<li><a class="gd-switch--help" href="#request-help" role="tab" data-gd-tab="request-help"><strong><?php echo gd_copy( 'involved.help.tab' ); ?></strong><span><?php echo gd_copy( 'involved.help.tabsub' ); ?></span></a></li>
		<li><a class="gd-switch--volunteer" href="#volunteer" role="tab" data-gd-tab="volunteer"><strong><?php echo gd_copy( 'involved.volunteer.tab' ); ?></strong><span><?php echo gd_copy( 'involved.volunteer.tabsub' ); ?></span></a></li>
	</ul>

	<section class="gd-panel" id="request-help" data-gd-panel="request-help">
		<div class="gd-panel__intro">
			<h2><?php echo gd_copy( 'involved.help.heading' ); ?></h2>
			<p class="gd-lede"><?php echo gd_copy( 'involved.help.lede' ); ?></p>
		</div>
		<?php echo $help; ?>
	</section>

	<section class="gd-panel" id="volunteer" data-gd-panel="volunteer">
		<div class="gd-panel__intro">
			<h2><?php echo gd_copy( 'involved.volunteer.heading' ); ?></h2>
			<p class="gd-lede"><?php echo gd_copy( 'involved.volunteer.lede' ); ?></p>
		</div>
		<?php echo $vol; ?>
	</section>
</div>
<!-- /wp:html -->
