<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}
?> <a href="#" id="bbcs_ipv4_add" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Add new search engine or other white bot"><i class="fa-solid fa-plus"></i></a>
<a href="#" id="bbcs_ipv4_import" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Import search engines and other white bots from JSON"><i class="fa-solid fa-upload"></i></a>
<a href="#" id="bbcs_ipv4_export" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Export search engines and other white bots to JSON"><i class="fa-solid fa-download"></i></a>
<a href="#" id="bbcs_ipv4_clear_all" class="btn btn-default btn-sm me-3" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Remove all search engines and other white bots"><i class="fa-regular fa-trash-can"></i></a>


<a href="#" id="bbcs_ipv4_to_php" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Set search engines and other white bots to Early BotBlocker Mode"><i class="fa-solid fa-bolt-lightning"></i></a>
<a href="#" id="bbcs_ipv4_to_mu" class="btn btn-default btn-sm me-3" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Set search engines and other white bots to MU-plugin BotBlocker Mode"><i class="fa-solid fa-plug-circle-bolt"></i></a>

<a href="#" id="bbcs_ipv4_import_white" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Import white list IP from text file"><i class="fa-regular fa-flag"></i></a>
<a href="#" id="bbcs_ipv4_import_black" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Import black list IP from text file"><i class="fa-solid fa-flag"></i></a>
<a href="<?php echo $BBCSA['files']['IPv4']; ?>" id="bbcs_ipv4_download_test" class="btn btn-default btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="Download test file for import lists" download><i class="fa-regular fa-file-lines"></i></a>