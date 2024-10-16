<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}
?> <a href="#" id="bbcs_path_add" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Add new path"><i class="fa-solid fa-plus"></i></a>
<a href="#" id="bbcs_path_import" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Import paths from JSON"><i class="fa-solid fa-upload"></i></a>
<a href="#" id="bbcs_path_export" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Export paths to JSON"><i class="fa-solid fa-download"></i></a>
<a href="#" id="bbcs_path_clear_all" class="btn btn-default btn-sm me-3" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Remove all paths"><i class="fa-regular fa-trash-can"></i></a>

    <a href="#" id="bbcs_path_to_php" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Set paths to Early BotBlocker Mode"><i class="fa-solid fa-bolt-lightning"></i></a>
<a href="#" id="bbcs_path_to_mu" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Set paths to MU-plugin BotBlocker Mode"><i class="fa-solid fa-plug-circle-bolt"></i></a>