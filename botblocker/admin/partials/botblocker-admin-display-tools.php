<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

include('botblocker-section-header.php');

?><section role="main" class="content-body">
<div class="row">
	<div class="col-md-10">
		<section class="card">
			<header class="card-header">
				<div class="card-actions">
					<button type="submit" name="save_settings" value="Save Settings" class="bbcs-icon-button">
						<i class="bbcs-card-action fa-regular fa-xl fa-floppy-disk"></i>
					</button>
				</div>
				<h2 class="card-title">Tools</h2>
			</header>
			<div class="card-body">
				<div class="row">
					<div class="col-md-3">
					</div>
					<div class="col-md-3">
					</div>
					<div class="col-md-3">
					</div>
					<div class="col-md-3">
					</div>
				</div>
		</section>
	</div>
	<div class="col-md-2">
		<?php include('botblocker-section-right-sidebar.php'); ?>
	</div>
</div>
</section>