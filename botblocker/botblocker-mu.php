<?php
/**
 * BotBlocker MU (Must Use) Plugin
 *
 * This file contains the BotBlockerMu class which handles the core functionality
 * of the BotBlocker plugin when used as a Must Use plugin.
 *
 * @package BotBlocker
 * @subpackage MustUse
 */

// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC')) {
    exit;
}

/**
 * Class BotBlockerMu
 *
 * Handles the core functionality of the BotBlocker plugin when used as a Must Use plugin.
 */
class BotBlockerMu
{
    /**
     * BotBlockerMu constructor.
     *
     * Initializes the BotBlockerMu class.
     */
    public function __construct()
    {
        // Constructor logic can be added here if needed.
    }

    /**
     * Run the BotBlocker MU plugin.
     *
     * Sets various headers to prevent caching and allow service workers.
     *
     * @return void
     */
    public function run()
    {
        if (!headers_sent()) {
            header('Pragma: no-cache');
            header('Expires: Thu, 10 Aug 2000 06:00:00 GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Service-Worker-Allowed: /');
        }
    }
}