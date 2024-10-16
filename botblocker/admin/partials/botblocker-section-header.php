<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}

global $BBCS;
global $BBCSA;
 
?>
<header class="header">
	<div class="logo-container">
		<a href="<?php echo $BBCSA['pages']['dashboard'];?>" class="logo">
			<?php
			echo '<img src="' . $BBCS['logo_webp'] . '" height="50" alt="' . BOTBLOCKER_SHORT_NAME . '">';
			?>
		</a>
		<div class="d-md-none toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
			<i class="fas fa-bars" aria-label="Toggle sidebar"></i>
		</div>
	</div>
 
	<div class="header-right">
		<span class="bbcs-license-<?php echo bbcs_getLicenseType(); ?>">
			<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-primary"><?php echo bbcs_getLicenseType(); ?></button>
		</span>
		<span class="separator"></span>
		<ul class="notifications">
			<li>
				<a href="#" class="dropdown-toggle notification-icon" data-bs-toggle="dropdown">
					<i class="fa fa-list"></i>
					<span class="badge"><!--3--></span>
				</a>
				<div class="dropdown-menu notification-menu large">
					<div class="notification-title">
						<span class="float-end badge badge-default"><!--3--></span>
						Tasks
					</div>
					<div class="content">
						<ul>
<!--
							<li>
								<p class="clearfix mb-1">
									<span class="message float-start">Generating Sales Report</span>
									<span class="message float-end text-dark">60%</span>
								</p>
								<div class="progress progress-xs light">
									<div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;"></div>
								</div>
							</li>
							<li>
								<p class="clearfix mb-1">
									<span class="message float-start">Importing Contacts</span>
									<span class="message float-end text-dark">98%</span>
								</p>
								<div class="progress progress-xs light">
									<div class="progress-bar" role="progressbar" aria-valuenow="98" aria-valuemin="0" aria-valuemax="100" style="width: 98%;"></div>
								</div>
							</li>
							<li>
								<p class="clearfix mb-1">
									<span class="message float-start">Uploading something big</span>
									<span class="message float-end text-dark">33%</span>
								</p>
								<div class="progress progress-xs light mb-1">
									<div class="progress-bar" role="progressbar" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100" style="width: 33%;"></div>
								</div>
							</li>
-->
						</ul>
					</div>
				</div>
			</li>
			<li>
				<a href="#" class="dropdown-toggle notification-icon" data-bs-toggle="dropdown">
					<i class="fa fa-bullhorn"></i>
					<span class="badge"><!--3--></span>
				</a>
				<div class="dropdown-menu notification-menu">
					<div class="notification-title">
						<span class="float-end badge badge-default"><!--3--></span>
						Alerts
					</div>
					<div class="content">
						<ul>
					<!--
							<li>
								<a href="#" class="clearfix">
									<div class="image">
										<i class="fas fa-thumbs-down bg-danger text-light"></i>
									</div>
									<span class="title">Server is Down!</span>
									<span class="message">Just now</span>
								</a>
							</li>
							<li>
								<a href="#" class="clearfix">
									<div class="image">
										<i class="fas fa-signal bg-success text-light"></i>
									</div>
									<span class="title">Connection Restaured</span>
									<span class="message">10/10/2023</span>
								</a>
							</li>
						-->
						</ul>
						<hr>
						<div class="text-end">
							<a href="#" class="view-more">View All</a>
						</div>
					</div>
				</div>
			</li>

			<li>
				<a href="#" class="dropdown-toggle notification-icon" data-bs-toggle="dropdown">
					<i class="fa fa-globe"></i>
				</a>
				<div class="dropdown-menu notification-menu">
					<div class="notification-title">
						Select Language
					</div>
					<div class="content">
						<ul>
							<li>
								<a href="#" class="clearfix language-option" data-lang="en">
									<div class="image">
										<span class="flag-icon flag-icon-us"></span>
									</div>
									<span class="title">English</span>
								</a>
							</li>
							<li>
								<a href="#" class="clearfix language-option" data-lang="ru">
									<div class="image">
										<span class="flag-icon flag-icon-ru"></span>
									</div>
									<span class="title">Русский</span>
								</a>
							</li>
							<!-- Добавьте дополнительные языки здесь -->
						</ul>
					</div>
				</div>
			</li>

		</ul>
		<span class="separator"></span>
		<div id="userbox" class="userbox">
			<a href="#" data-bs-toggle="dropdown">
				<figure class="profile-picture">

					<?php
					$user = wp_get_current_user();
					if (is_user_logged_in()) {
						$avatar_path = bbcs_get_avatar_path($user->ID);
						$display_name = bbcs_get_display_name($user->ID);
						$user_role = bbcs_get_user_role($user->ID);

						if($avatar_path == $BBCSA['empty']){
							$avatar_path = $BBCSA['custom_avatar'];
						}

						echo '<img src="' . $avatar_path . '" alt="' . esc_attr($display_name) . '" class="rounded-circle">';
					} else {
						$avatar_path = $BBCSA['custom_avatar'];
						echo '<img src="' . $avatar_path . '" alt="John Doe" class="rounded-circle">';
					}
					?>
				</figure>
				<div class="profile-info" data-lock-name="<?php echo esc_html($display_name); ?>" data-lock-email="johndoe@okler.com">
					<span class="name"><?php echo esc_html($display_name); ?></span>
					<?php
					if (is_user_logged_in()) {
						echo '<span class="role">' . esc_html($user_role) . '</span>';
					} else {
						echo '<span class="role">Guest</span>';
					}
					?>
				</div>
				<i class="fa custom-caret"></i>
			</a>
			<div class="dropdown-menu">
				<ul class="list-unstyled mb-2">
					<li class="divider"></li>
<!--
					<li>
						<a role="menuitem" tabindex="-1" href="pages-user-profile.html"><i class="bx bx-user-circle"></i> My Profile</a>
					</li>
					<li>
						<a role="menuitem" tabindex="-1" href="#" data-lock-screen="true"><i class="bx bx-lock"></i> Lock Screen</a>
					</li>
				-->
					<li class="divider"></li>
					<li>
						<a role="menuitem" tabindex="-1" href="https://cybersecure.top" target="_blank"><i class="bx bx-power-off"></i> CyberSecure Website</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</header>