<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

global $bbcsa;

?><section class="card bbcs-card-border-left ">
<header class="card-header bbcs_small_header">
    <div class="card-actions bbcs_header_controls">
        <a href="<?php echo $BBCSA['pages']['settings'];?>" data-bs-toggle="tooltip" data-bs-placement="top" 
        data-bs-original-title="BotBlocker Settings"><i class="fa-solid fa-gear bbcs-h-btn-gray"></i></a>
    </div>
    <h2 class="card-title">Status</h2>
    <!--<p class="card-subtitle"></p>-->
</header>
<div class="card-body">
        <div class="bbcs_status_main">
            <i class="fa-solid fa-2x fa-shield-halved bbcs_color_green"></i> 
            <span class="bbcs_status_text">Active</span>	
        </div>

        <div class="bbcs_switch_container">
          <label class="bbcs_switch">
            <input type="checkbox" id="bbcs_switch_early_init">
            <span class="bbcs_slider"></span>
          </label>
          <span class="bbcs_switch_label">Early initialization <a href="#"><i class="fas fa-gear bbcs-gray ms-1"></i></a></span>
        </div>
        <div class="bbcs_switch_container">
          <label class="bbcs_switch">
            <input type="checkbox" id="bbcs_switch_mu_plugin">
            <span class="bbcs_slider"></span>
          </label>
          <span class="bbcs_switch_label">MU plugin functionality <a href="#"><i class="fas fa-gear bbcs-gray ms-1"></i></a></span>
        </div>
        <div class="bbcs_switch_container">
          <label class="bbcs_switch">
            <input type="checkbox" id="bbcs_switch_redis">
            <span class="bbcs_slider"></span>
          </label>
          <span class="bbcs_switch_label">Redis <a href="#"><i class="fas fa-gear bbcs-gray ms-1"></i></a> <a href="#"><i class="fas fa-info-circle bbcs-gray ms-1"></i></a></span>
        </div>
        <div class="bbcs_switch_container">
          <label class="bbcs_switch">
            <input type="checkbox" id="bbcs_switch_memcached">
            <span class="bbcs_slider"></span>
          </label>
          <span class="bbcs_switch_label">Memcached</span>
        </div>
        <div class="bbcs_switch_container">
          <label class="bbcs_switch">
            <input type="checkbox" id="bbcs_switch_apcu">
            <span class="bbcs_slider"></span>
          </label>
          <span class="bbcs_switch_label">PTR Cache</span>
        </div>
</div>
<div class="card-footer">
  <small>
	Today blocked: <b>115</b><br> 
  Total blocked: <b>2745</b>
  </small>
</div>

</section>

<section class="card bbcs-card-border-left ">
<header class="card-header bbcs_small_header">
    <div class="card-actions bbcs_header_controls">
    <a href="https://cybersecure.top/botblocker-pro" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" 
    data-bs-original-title="About BotBlocker PRO"><i class="fa-solid fa-globe bbcs-h-btn-gray"></i></a>
    </div>
    <h2 class="card-title">PRO Features</h2>
    <!--<p class="card-subtitle"></p>-->
</header>
<div class="card-body">

<ul class="bbcs-pro-features">
    <li><i class="fa-solid fa-check"></i> Advanced Bot Detection</li>
    <li><i class="fa-solid fa-check"></i> Advanced Bot Blocking</li>
    <li><i class="fa-solid fa-check"></i> Advanced Bot Analysis</li>
    <li><i class="fa-solid fa-check"></i> Advanced Bot Protection</li>
    <li><i class="fa-solid fa-check"></i> Advanced Bot Security</li>
    <li><i class="fa-solid fa-check"></i> Advanced Bot Prevention</li>
    <li><i class="fa-solid fa-check"></i> Advanced Bot Defense</li>
    <li><i class="fa-solid fa-check"></i> Advanced Bot Shielding</li>
</ul>

<a href="<?php echo $BBCSA['pages']['pro'];?>" class="mt-2 btn btn-xs btn-primary"><i class="fa-solid fa-cart-shopping"></i> Upgrade to PRO</a>

</div>
</section>	

<section class="card bbcs-card-border-left ">
<header class="card-header bbcs_small_header">
    <div class="card-actions bbcs_header_controls">
    <a href="https://cybersecure.top/news" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" 
    data-bs-original-title="BotBlocker News"><i class="fa-solid fa-globe bbcs-h-btn-gray"></i></a>
    </div>
    <h2 class="card-title">News</h2>
    <!--<p class="card-subtitle"></p>-->
</header>
<div class="card-body">
    <?php echo do_shortcode('[bbcs_cybersecure_news]'); ?>
</div>
<div class="card-footer">
  <small>
	New bots discovered today <b>+256</b>
  <br>
  GLOBUS.studio new projects 
  </small>
</div>
</section>

<section class="card bbcs-card-border-left ">
<header class="card-header bbcs_small_header">
    <div class="card-actions bbcs_header_controls">
        <a href="<?php echo $BBCSA['pages']['settings'];?>" data-bs-toggle="tooltip" data-bs-placement="top" 
        data-bs-original-title="BotBlocker Settings"><i class="fa-solid fa-gear bbcs-h-btn-gray"></i></a>
    </div>
    <h2 class="card-title">System Status</h2>
    <!--<p class="card-subtitle"></p>-->
</header>
<div class="card-body">
    <?php echo do_shortcode('[bbcs_system_status]'); ?>
</div>
</section>				