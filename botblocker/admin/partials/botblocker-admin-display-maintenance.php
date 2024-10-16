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
                    <h2 class="card-title">Maintenance</h2>
                </header>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h3 class="bbcs_settings_h3">General</h3>
                            <div class="bbcs_settings_button">
                                <button type="button" id="bbcs-backup-data-settings" class="mb-1 btn btn-xs btn-default">
                                    <i class="fa-solid fa-download"></i>
                                    Export data and settings
                                </button>
                                <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-original-title="Clear Wordpress log if exist"></i>
                            </div>
                            <div class="bbcs_settings_button">
                                <button type="button" id="bbcs-import-data-settings" class="mb-1 btn btn-xs btn-default">
                                    <i class="fa-solid fa-upload"></i>
                                    Import data and settings
                                </button>
                                <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-original-title="Clear Wordpress log if exist"></i>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h3 class="bbcs_settings_h3">Database</h3>
                            <div class="bbcs_settings_button">
                                <button type="button" id="bbcs-reinstall-database" class="mb-1 btn btn-xs btn-danger">
                                    <i class="fas fa-sync"></i>
                                    Re-install Database
                                </button>
                                <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-original-title="Clear all tables of BotBlocker and install initial settings"></i>
                            </div>
                            <div class="bbcs_settings_button">
                                <button type="button" id="bbcs-clear-hits-database" class="mb-1 btn btn-xs btn-default">
                                    <i class="fa-regular fa-trash-can"></i>
                                    Clear all visitors data
                                </button>
                                <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-original-title="Clear all visitors and statistics from DB"></i>
                            </div>
                            <div class="bbcs_settings_button">
                                <button type="button" id="bbcs-reinstall-files" class="mb-1 btn btn-xs btn-default">
                                    <i class="fa-regular fa-file-code"></i>
                                    Clear rule files for EarlyBlocker and MU-plugin
                                </button>
                                <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-original-title=" Clear rule files for EarlyBlocker and MU-plugin"></i>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h3 class="bbcs_settings_h3">Features</h3>
                        </div>
                        <div class="col-md-3">
                            <h3 class="bbcs_settings_h3">Wordpress</h3>
                            <div class="bbcs_settings_button">
                                <button type="button" id="bbcs-reinstall-files" class="mb-1 btn btn-xs btn-default">
                                    <i class="fa-regular fa-file-lines"></i>
                                    Clear Wordpress log
                                </button>
                                <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-original-title="Clear Wordpress log if exist"></i>
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