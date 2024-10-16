<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}
?><a href="#" id="bbcs_rules_add" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Add new rule"><i class="fa-solid fa-plus"></i></a>
<a href="#" id="bbcs_rules_import" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Import rules from JSON"><i class="fa-solid fa-upload"></i></a>
<a href="#" id="bbcs_rules_export" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Export rules to JSON"><i class="fa-solid fa-download"></i></a>
<a href="#" id="bbcs_rules_clear_all" class="btn btn-default btn-sm me-3" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Remove all rules"><i class="fa-regular fa-trash-can"></i></a>

<a href="#" id="bbcs_rules_to_php" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Set rules to Early BotBlocker Mode"><i class="fa-solid fa-bolt-lightning"></i></a>
<a href="#" id="bbcs_rules_to_mu" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Set rules to MU-plugin BotBlocker Mode"><i class="fa-solid fa-plug-circle-bolt"></i></a>