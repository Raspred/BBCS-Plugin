<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}
include('botblocker-section-header.php');
?><section role="main" class="content-body">
	<div class="row">
		<div class="col-lg-7">
			<section class="card mb-2">
				<header class="card-header">
					<div class="card-actions">

					</div>
					<h2 class="card-title">Rules and IP lists</h2>
				</header>
				<div class="card-body">
					<ul class="nav nav-tabs">
						<li class="nav-item">
							<a class="nav-link active" data-bs-toggle="tab" href="#bbcs_rules">Rules</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-bs-toggle="tab" href="#bbcs_path">Paths</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-bs-toggle="tab" href="#bbcs_white_bots">White bots</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-bs-toggle="tab" href="#bbcs_IPv4_list">IPv4 list</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-bs-toggle="tab" href="#bbcs_IPv6_list">IPv6 list</a>
						</li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane container active" id="bbcs_rules">
							<div class="bbcs_control_panel">
								<?php include_once BOTBLOCKER_DIR . 'includes/section/botblocker-rule-controls.php'; ?>
							</div>
							<table class="table table-bordered table-striped compact mb-0" id="botblocker-rules" style="width:100%; font-size: 11px;">
								<thead>
									<tr>
										<th style="min-width: 50px;">ID</th>
										<th style="min-width: 80px;">Priority</th>
										<th style="min-width: 80px;">Type</th>
										<th style="min-width: 100px;">Data</th>
										<th style="min-width: 100px;">Expires</th>
										<th style="min-width: 80px;">Rule</th>
										<th style="min-width: 100px;">Comment</th>
										<th style="min-width: 100px;">Actions</th>
									</tr>
								</thead>
							</table>
						</div>
						<div class="tab-pane container fade" id="bbcs_path">
							<div class="bbcs_control_panel">
								<?php include_once BOTBLOCKER_DIR . 'includes/section/botblocker-path-controls.php'; ?>
								<table class="table table-bordered table-striped compact mb-0" id="botblocker-paths" style="width:100%; font-size: 11px;">
									<thead>
										<tr>
											<th style="min-width: 50px;">ID</th>
											<th style="min-width: 80px;">Priority</th>
											<th style="min-width: 100px;">Data</th>
											<th style="min-width: 80px;">Rule</th>
											<th style="min-width: 100px;">Comment</th>
											<th style="min-width: 100px;">Actions</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
						<div class="tab-pane container fade" id="bbcs_white_bots">
							<div class="bbcs_control_panel">
								<?php include_once BOTBLOCKER_DIR . 'includes/section/botblocker-white-controls.php'; ?>
								<table class="table table-bordered table-striped compact mb-0" id="botblocker-white" style="width:100%; font-size: 11px;">
								<thead>
									<tr>
										<th style="min-width: 50px;">ID</th>
										<th style="min-width: 80px;">Priority</th>
										<th style="min-width: 80px;">Search</th>
										<th style="min-width: 100px;">Data</th>
										<th style="min-width: 80px;">Rule</th>
										<th style="min-width: 100px;">Comment</th>
										<th style="min-width: 100px;">Actions</th>
									</tr>
								</thead>
							</table>
							</div>
						</div>
						<div class="tab-pane container fade" id="bbcs_IPv4_list">
							<div class="bbcs_control_panel">
								<?php include_once BOTBLOCKER_DIR . 'includes/section/botblocker-ipv4-controls.php'; ?>
							</div>

							<table class="table table-bordered table-striped compact mb-0" id="botblocker-ipv4-rules" style="width:100%; font-size: 11px;">
								<thead>
									<tr>
										<th style="min-width: 50px;">ID</th>
										<th style="min-width: 50px;">Priority</th>
										<th style="min-width: 80px;">IP</th>
										<th style="min-width: 80px;">Rule</th>
										<th style="min-width: 100px;">Expires</th>
										<th style="min-width: 100px;">Comment</th>
										<th style="min-width: 100px;">Actions</th>
									</tr>
								</thead>
							</table>

						</div>
						<div class="tab-pane container fade" id="bbcs_IPv6_list">
							<div class="bbcs_control_panel">
								<?php include_once BOTBLOCKER_DIR . 'includes/section/botblocker-ipv6-controls.php'; ?>
							</div>

							<table class="table table-bordered table-striped compact mb-0" id="botblocker-ipv6-rules" style="width:100%; font-size: 11px;">
								<thead>
									<tr>
										<th style="min-width: 50px;">ID</th>
										<th style="min-width: 50px;">Priority</th>
										<th style="min-width: 80px;">IP</th>
										<th style="min-width: 80px;">Rule</th>
										<th style="min-width: 100px;">Expires</th>
										<th style="min-width: 100px;">Comment</th>
										<th style="min-width: 100px;">Actions</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			</section>
		</div>
		<div class="col-lg-3 mb-1">
			<section class="card">
				<header class="card-header">
					<div class="card-actions">

					</div>
					<h2 class="card-title">Tools</h2>
				</header>
				<div class="card-body">
						<div class="bbcs_settings_button">
                                <button type="button" id="bbcs-reinstall-xxx" class="mb-1 btn btn-xs btn-danger">
                                    <i class="fas fa-sync"></i> 
                                    Re-install Database
                                </button>
                                <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-original-title="Clear all tables of BotBlocker and install initial settings"></i>
                        </div>
				</div>
			</section>
			<section class="card">
				<header class="card-header">
					<div class="card-actions">

					</div>
					<h2 class="card-title">Rules and IP lists statistics</h2>
				</header>
				<div class="card-body">
					<?php echo do_shortcode('[botblocker_rules_stats show_chart="yes" chart_height="120"]'); ?>
				</div>
			</section>
		</div>

		<div class="col-lg-2 mb-1">
			<?php include('botblocker-section-right-sidebar.php'); ?>
		</div>
	</div>

	<?php
	include_once BOTBLOCKER_DIR . 'includes/modal/modal-botblocker-rule-edit.php';
	include_once BOTBLOCKER_DIR . 'includes/modal/modal-botblocker-rule-add.php';
	include_once BOTBLOCKER_DIR . 'includes/modal/modal-botblocker-rule-ipv4-edit.php';
	include_once BOTBLOCKER_DIR . 'includes/modal/modal-botblocker-rule-ipv4-add.php';
	include_once BOTBLOCKER_DIR . 'includes/modal/modal-botblocker-rule-ipv6-edit.php';
	include_once BOTBLOCKER_DIR . 'includes/modal/modal-botblocker-rule-ipv6-add.php';
	include_once BOTBLOCKER_DIR . 'includes/modal/modal-botblocker-path-edit.php';
	include_once BOTBLOCKER_DIR . 'includes/modal/modal-botblocker-path-add.php';
	include_once BOTBLOCKER_DIR . 'includes/modal/modal-botblocker-white-edit.php';
	include_once BOTBLOCKER_DIR . 'includes/modal/modal-botblocker-white-add.php';
	?>