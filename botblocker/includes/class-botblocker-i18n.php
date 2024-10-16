<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://globus.studio
 * @since      1.0.0
 *
 * @package    Botblocker
 * @subpackage Botblocker/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Botblocker
 * @subpackage Botblocker/includes
 * @author     GLOBUS.studio <sales@globus.studio>
 */
class Botblocker_i18n {
    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        $locale = $this->get_preferred_language();
        load_plugin_textdomain(
            'botblocker',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/' . $locale
        );
    }

    /**
     * Get the preferred language from cookies.
     *
     * @since    1.0.0
     * @return   string   The locale.
     */
    private function get_preferred_language() {
        if (isset($_COOKIE['preferred_language'])) {
            return sanitize_text_field($_COOKIE['preferred_language']);
        }
        return get_locale(); // default locale
    }
}
