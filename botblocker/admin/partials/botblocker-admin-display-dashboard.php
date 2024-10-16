<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}

include('botblocker-section-header.php');
bbcs_get_statistics($BBCS['admin_report_period']);

?><section role="main" class="content-body">
	<div class="row">

		<div class="col-lg-5">
			<section class="card mb-2">
				<header class="card-header">

					<div class="card-actions">
						<a href="<?php echo $BBCSA['pages']['pro'];?>" class="bbcs-icon-button" data-bs-toggle="tooltip" data-bs-placement="top" 
						data-bs-original-title="Activate PRO for excelent security protection">
							<i class="bbcs-card-action fa-solid fa-crown"></i>
						</a>
						<a href="<?php echo $BBCSA['pages']['settings'];?>" class="bbcs-icon-button" data-bs-toggle="tooltip" data-bs-placement="top" 
						data-bs-original-title="Settings">
							<i class="bbcs-card-action fa-solid fa-gear"></i>
						</a>
					</div>

					<h2 class="card-title">Health status</h2>
					<p class="card-subtitle">Score of your website health status.</p>
				</header>
				<div class="card-body">
					<div class="row">
						<div class="col-lg-6">
							<?php echo do_shortcode('[bbcs_health_gauge id="health_gauge" value="' . calculateSiteHealth() . '" max="100" label="Health meter"]'); ?>
						</div>
						<div class="col-lg-6">
							<?php echo generateSiteHealthList(); ?>
						</div>
					</div>
				</div>
			</section>
			<section class="card">
				<header class="card-header">
					<div class="card-actions">
						<a href="#" class="card-action card-action-toggle" data-card-toggle=""></a>
						<a href="#" class="card-action card-action-dismiss" data-card-dismiss=""></a>
					</div>

					<h2 class="card-title">Visitors log</h2>
				</header>
				<div class="card-body">

				</div>
			</section>
		</div>

		<div class="col-lg-5 mb-1">

			<section class="card">
				<header class="card-header">
					<div class="card-actions">
						<a href="#" class="card-action card-action-dismiss" data-card-dismiss=""></a>
					</div>
					<h2 class="card-title">Today statistics</h2>
				</header>
				<div class="card-body">
					<div class="row">
						<div class="col-lg-8">
							<div class="bbcs-statistics-chart-title-div"><span class="bbcs-statistics-chart-title">Hourly hits chart</span></div>
							<?php echo do_shortcode('[bbcs_daily_hits_chart width="100%" height="200px"]'); ?>
						</div>
						<div class="col-lg-4">

						</div>
					</div>
					<div class="row">
						<div class="col-lg-3">
							<?php echo do_shortcode('[bbcs_statistics_chart type="donut" period="today" data="ip_hits_hosts" height="90px"]'); ?>
						</div>
						<div class="col-lg-3">
							<?php echo do_shortcode('[bbcs_statistics_chart type="donut" period="today" data="device_types" height="90px"]'); ?>
						</div>
						<div class="col-lg-3">
							<?php echo do_shortcode('[bbcs_statistics_chart type="donut" period="today" data="browsers" height="90px"]'); ?>
						</div>
						<div class="col-lg-3">
							<?php echo do_shortcode('[bbcs_statistics_chart type="donut" period="today" data="operating_systems" height="90px"]'); ?>
						</div>
					</div>
				</div>
			</section>

			<section class="card">
				<header class="card-header">
					<div class="card-actions">
						<a href="#" class="card-action card-action-toggle" data-card-toggle=""></a>
						<a href="#" class="card-action card-action-dismiss" data-card-dismiss=""></a>
					</div>
					<h2 class="card-title">Website Traffic</h2>
					<p class="card-subtitle">Real-time website visitor statistics. View period - <?php echo $BBCS['admin_report_period']; ?> days (<a href="<?php echo $BBCSA['pages']['settings']; ?>">Change</a>).</p>
				</header>
				<div class="card-body">
					<div class="row">
						<div class="col-lg-8">
							<?php echo do_shortcode('[bbcs_hits_and_uniques_chart width="100%" height="230px" days="' . $BBCS['admin_report_period'] . '"]'); ?>
						</div>
						<div class="col-lg-4">

						</div>
					</div>
				</div>
			</section>

			<section class="card">
				<header class="card-header">
					<div class="card-actions">
						<a href="#" class="card-action card-action-toggle" data-card-toggle=""></a>
						<a href="#" class="card-action card-action-dismiss" data-card-dismiss=""></a>
					</div>
					<h2 class="card-title">Traffic geo data</h2>
					<p class="card-subtitle">Real-time website visitor geo statistics. View period - <?php echo $BBCS['admin_report_period']; ?> days (<a href="<?php echo $BBCSA['pages']['settings']; ?>">Change</a>).</p>
				</header>
				<div class="card-body">
					<div class="row">
						<div class="col-lg-4 bbcs-border-right">

						</div>
						<div class="col-lg-8">
							<?php echo do_shortcode('[bbcs_visitors_jsvectormap days="' . $BBCS['admin_report_period'] . '" height="350px"]'); ?>
						</div>
					</div>

				</div>
			</section>

		</div>

		<div class="col-lg-2 mb-1">
			<?php include('botblocker-section-right-sidebar.php'); ?>
		</div>
	</div>

</section>
<?php include_once BOTBLOCKER_DIR . 'includes/modal/modal-botblocker-rule-countries-list.php'; ?>