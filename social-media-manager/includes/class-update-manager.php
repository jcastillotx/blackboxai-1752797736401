<?php
/**
 * Update Manager Class
 * Prevents WordPress from showing update notifications for this plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SMM_Update_Manager {

    public function __construct() {
        add_filter('site_transient_update_plugins', array($this, 'disable_update_notifications'));
    }
    
    /**
     * Disable update notifications for this plugin.
     *
     * @param object $transient The update transient object.
     * @return object Modified update transient object.
     */
    public function disable_update_notifications($transient) {
        // Ensure $transient->response is set and is an array.
        if (isset($transient->response) && is_array($transient->response)) {
            // Get the plugin basename of the main plugin file.
            $plugin_file = plugin_basename(SMM_PLUGIN_PATH . 'social-media-manager.php');
            
            // Check if our plugin exists in the update list.
            if (isset($transient->response[$plugin_file])) {
                unset($transient->response[$plugin_file]);
            }
        }
        return $transient;
    }
}

// Initialize the update manager.
new SMM_Update_Manager();
