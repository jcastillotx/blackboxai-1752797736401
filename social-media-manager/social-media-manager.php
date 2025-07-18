<?php
/**
 * Plugin Name: Social Media Manager Custom
 * Plugin URI: https://www.kre8itech.com
 * Description: Comprehensive social media management plugin with AI-powered strategy generation and client management.
 * Version: 1.0.0
 * Author: Jeremiah Castillo
 * Author URI: https://www.kre8itech.com
 * Company: Kre8ivTech, LLC
 * License: GPL v2 or later
 * Text Domain: social-media-manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SMM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SMM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SMM_VERSION', '1.0.0');

// Main plugin class
class SocialMediaManager {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load plugin components
        $this->load_dependencies();
        $this->init_hooks();
        $this->create_user_roles();
    }
    
    private function load_dependencies() {
        // Load dependencies with error checking
        $dependencies = array(
            'class-database.php' => 'SMM_Database',
            'class-admin.php' => 'SMM_Admin',
            'class-frontend.php' => 'SMM_Frontend',
            'class-client-frontend.php' => 'SMM_Client_Frontend',
            'class-chatgpt-integration.php' => 'SMM_ChatGPT_Integration',
            'class-social-media-api.php' => 'SMM_Social_Media_API'
        );
        
        foreach ($dependencies as $file => $class) {
            $file_path = SMM_PLUGIN_PATH . 'includes/' . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
                if (!class_exists($class)) {
                    error_log('SMM Plugin Error: Class ' . $class . ' not found in ' . $file_path);
                }
            } else {
                error_log('SMM Plugin Error: File missing - ' . $file_path);
            }
        }
        
        // Load update management to disable unwanted update notifications
        $update_manager_file = SMM_PLUGIN_PATH . 'includes/class-update-manager.php';
        if (file_exists($update_manager_file)) {
            require_once $update_manager_file;
        } else {
            error_log('SMM Plugin Error: Update Manager file missing - ' . $update_manager_file);
        }
        
        // Initialize classes only if they exist
        if (class_exists('SMM_Database')) {
            new SMM_Database();
        }
        if (class_exists('SMM_Admin')) {
            new SMM_Admin();
        }
        if (class_exists('SMM_Frontend')) {
            new SMM_Frontend();
        }
        if (class_exists('SMM_Client_Frontend')) {
            new SMM_Client_Frontend();
        }
        if (class_exists('SMM_ChatGPT_Integration')) {
            new SMM_ChatGPT_Integration();
        }
        if (class_exists('SMM_Social_Media_API')) {
            new SMM_Social_Media_API();
        }
    }
    
    private function init_hooks() {
        add_action('wp_ajax_smm_client_intake', array($this, 'handle_client_intake'));
        add_action('wp_ajax_nopriv_smm_client_intake', array($this, 'handle_client_intake'));
        add_action('wp_ajax_smm_generate_strategy', array($this, 'generate_strategy'));
        add_action('wp_ajax_smm_save_timesheet', array($this, 'save_timesheet'));
        add_action('wp_ajax_smm_send_message', array($this, 'send_message'));
        add_action('wp_ajax_smm_test_api_connection', array($this, 'test_api_connection'));
        add_action('wp_ajax_smm_ask_ai', array($this, 'ask_ai'));
        add_action('wp_ajax_smm_get_timesheet_entries', array($this, 'get_timesheet_entries'));
        add_action('wp_ajax_smm_get_messages', array($this, 'get_messages'));
        add_action('wp_ajax_smm_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_smm_email_strategy', array($this, 'email_strategy'));
    }
    
    private function create_user_roles() {
        // Create custom user roles
        add_role('social_media_manager', 'Social Media Manager', array(
            'read' => true,
            'manage_social_media' => true,
            'view_analytics' => true,
            'manage_clients' => true
        ));
        
        add_role('smm_client', 'SMM Client', array(
            'read' => true,
            'view_own_reports' => true
        ));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('smm-frontend-style', SMM_PLUGIN_URL . 'assets/css/frontend.css', array(), SMM_VERSION);
        wp_enqueue_script('smm-frontend-script', SMM_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), SMM_VERSION, true);
        
        // Localize script for AJAX
        wp_localize_script('smm-frontend-script', 'smm_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('smm_nonce')
        ));
    }
    
    public function admin_enqueue_scripts() {
        wp_enqueue_style('smm-admin-style', SMM_PLUGIN_URL . 'assets/css/admin.css', array(), SMM_VERSION);
        wp_enqueue_script('smm-admin-script', SMM_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), SMM_VERSION, true);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
    }
    
    public function activate() {
        // Load database class for activation
        $database_file = SMM_PLUGIN_PATH . 'includes/class-database.php';
        if (file_exists($database_file)) {
            require_once $database_file;
            if (class_exists('SMM_Database')) {
                $database = new SMM_Database();
                $database->create_tables();
            } else {
                error_log('SMM Plugin Error: SMM_Database class not found in ' . $database_file);
            }
        } else {
            error_log('SMM Plugin Error: Database file missing during activation - ' . $database_file);
        }
        
        // Set default options
        add_option('smm_settings', array(
            'chatgpt_api_key' => '',
            'facebook_api_key' => '',
            'instagram_api_key' => '',
            'twitter_api_key' => '',
            'linkedin_api_key' => '',
            'system_prompt' => 'You are an expert social media strategist and marketing consultant with over 10 years of experience helping businesses grow their online presence. Your task is to create comprehensive, actionable social media strategies based on client intake information.'
        ));
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Clean up if needed
        flush_rewrite_rules();
    }
    
    // AJAX handlers
    public function handle_client_intake() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        $intake_data = sanitize_text_field($_POST['intake_data']);
        // Process intake form data
        
        wp_send_json_success(array('message' => 'Intake form submitted successfully'));
    }
    
    public function generate_strategy() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        $client_data = sanitize_text_field($_POST['client_data']);
        
        if (class_exists('SMM_ChatGPT_Integration')) {
            $chatgpt = new SMM_ChatGPT_Integration();
            $strategy = $chatgpt->generate_strategy($client_data);
            
            wp_send_json_success(array('strategy' => $strategy));
        } else {
            wp_send_json_error(array('message' => 'ChatGPT integration not available'));
        }
    }
    
    public function save_timesheet() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        $timesheet_data = sanitize_text_field($_POST['timesheet_data']);
        // Save timesheet data to database
        
        wp_send_json_success(array('message' => 'Timesheet saved successfully'));
    }
    
    public function send_message() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        $message_data = sanitize_text_field($_POST['message_data']);
        // Process and save message
        
        wp_send_json_success(array('message' => 'Message sent successfully'));
    }
    
    public function test_api_connection() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        $platform = sanitize_text_field($_POST['platform']);
        
        if (class_exists('SMM_Social_Media_API')) {
            $api = new SMM_Social_Media_API();
            $result = $api->connect_platform();
            
            if (isset($result['error'])) {
                wp_send_json_error(array('message' => $result['error']));
            } else {
                wp_send_json_success(array('message' => 'API connection successful'));
            }
        } else {
            wp_send_json_error(array('message' => 'Social Media API class not found'));
        }
    }
    
    public function ask_ai() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        $question = sanitize_textarea_field($_POST['question']);
        
        if (class_exists('SMM_ChatGPT_Integration')) {
            $chatgpt = new SMM_ChatGPT_Integration();
            
            // Create a simple prompt for Q&A
            $system_prompt = "You are a social media marketing expert. Answer questions about social media strategy, best practices, and marketing advice. Provide actionable, professional advice.";
            $user_prompt = $question;
            
            $response = $chatgpt->make_api_request($system_prompt, $user_prompt);
            
            if (isset($response['error'])) {
                wp_send_json_error(array('message' => $response['error']));
            } else {
                wp_send_json_success(array('answer' => $response));
            }
        } else {
            wp_send_json_error(array('message' => 'ChatGPT integration not available'));
        }
    }
    
    public function get_timesheet_entries() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        if (class_exists('SMM_Database')) {
            $database = new SMM_Database();
            global $wpdb;
            
            $table = $wpdb->prefix . 'smm_timesheets';
            $user_id = get_current_user_id();
            
            $entries = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE user_id = %d ORDER BY work_date DESC LIMIT 20",
                $user_id
            ));
            
            wp_send_json_success(array('entries' => $entries));
        } else {
            wp_send_json_error(array('message' => 'Database class not found'));
        }
    }
    
    public function get_messages() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        if (class_exists('SMM_Database')) {
            $database = new SMM_Database();
            $user_id = get_current_user_id();
            $messages = $database->get_messages($user_id);
            
            wp_send_json_success(array('messages' => $messages));
        } else {
            wp_send_json_error(array('message' => 'Database class not found'));
        }
    }
    
    public function save_settings() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        $settings = array();
        $allowed_settings = array(
            'default_post_time',
            'timezone',
            'email_notifications',
            'post_reminders'
        );
        
        foreach ($allowed_settings as $setting) {
            if (isset($_POST[$setting])) {
                $settings[$setting] = sanitize_text_field($_POST[$setting]);
            }
        }
        
        $current_settings = get_option('smm_user_settings_' . get_current_user_id(), array());
        $updated_settings = array_merge($current_settings, $settings);
        
        update_option('smm_user_settings_' . get_current_user_id(), $updated_settings);
        
        wp_send_json_success(array('message' => 'Settings saved successfully'));
    }
    
    public function email_strategy() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        
        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Invalid email address'));
            return;
        }
        
        // Get the last generated strategy for this user
        global $wpdb;
        $table = $wpdb->prefix . 'smm_clients';
        $user_id = get_current_user_id();
        
        $client = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC LIMIT 1",
            $user_id
        ));
        
        if ($client && !empty($client->strategy_generated)) {
            $subject = 'Your Social Media Strategy from ' . get_bloginfo('name');
            $message = "Hello,\n\nHere is your personalized social media strategy:\n\n";
            $message .= $client->strategy_generated;
            $message .= "\n\nBest regards,\n" . get_bloginfo('name');
            
            $sent = wp_mail($email, $subject, $message);
            
            if ($sent) {
                wp_send_json_success(array('message' => 'Strategy emailed successfully'));
            } else {
                wp_send_json_error(array('message' => 'Failed to send email'));
            }
        } else {
            wp_send_json_error(array('message' => 'No strategy found to email'));
        }
    }
}

// Initialize the plugin
new SocialMediaManager();
