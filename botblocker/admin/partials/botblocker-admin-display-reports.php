<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}

include('botblocker-section-header.php');

$bbcs_reportTableHeader = '
<thead>
<tr>
<th style="min-width: 85px;">Date</th>
<th style="min-width: 60px;">Time</th>
<th style="min-width: 80px;">IP</th>
<th style="min-width: 100px;">PTR</th>
<th style="min-width: 100px;">AS Info</th>
<th style="min-width: 50px;"><i class="fa fa-globe"></i></th>
<th style="min-width: 60px;">Lng</th>
<th style="min-width: 200px;">User Agent</th>
<th style="min-width: 150px;">Referer</th>
<th style="min-width: 150px;">Page</th>
<th style="min-width: 150px;">JS Info</th>
<th style="min-width: 50px;"><i class="fa-solid fa-ban"></i></th>
</tr>
</thead>';

?><section role="main" class="content-body">
	<div class="row">

		<div class="col-lg-10">
			<section class="card mb-2">
				<header class="card-header">
					<div class="card-actions">
						<a href="#" class="card-action card-action-toggle" data-card-toggle=""></a>
						<a href="#" class="card-action card-action-dismiss" data-card-dismiss=""></a>
					</div>
					<h2 class="card-title">Statistics</h2>
					<!--<p class="card-subtitle">Score of your website health status.</p>-->
				</header>
				<div class="card-body">
					<ul class="nav nav-tabs">
						<li class="nav-item">
							<a class="nav-link active" data-bs-toggle="tab" href="#frontend">Site visitors</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-bs-toggle="tab" href="#admin">Admin log</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-bs-toggle="tab" href="#wordpress">WP actions</a>
						</li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane container active" id="frontend">
							<table class="table table-bordered table-striped compact mb-0" id="botblocker-hits" style="width:100%; font-size: 11px;">
								<?php echo $bbcs_reportTableHeader; ?>
							</table>
						</div>
						<div class="tab-pane container fade" id="admin">
							<table class="table table-bordered table-striped compact mb-0" id="botblocker-hits-admin" style="width:100%; font-size: 11px;">
								<?php echo $bbcs_reportTableHeader; ?>
							</table>
						</div>
						<div class="tab-pane container fade" id="wordpress">
							<table class="table table-bordered table-striped compact mb-0" id="botblocker-other-admin" style="width:100%; font-size: 11px;">
								<?php echo $bbcs_reportTableHeader; ?>
							</table>
						</div>
					</div>
				</div>
			</section>
		</div>
		<div class="col-md-2">
			<?php include('botblocker-section-right-sidebar.php'); ?>
		</div>
	</div>
</section>