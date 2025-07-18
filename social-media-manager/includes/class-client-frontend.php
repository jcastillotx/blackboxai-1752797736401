<?php
/**
 * Client Frontend management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class SMM_Client_Frontend {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_shortcode('smm_client_intake', array($this, 'render_intake_form'));
        add_shortcode('smm_client_dashboard', array($this, 'render_client_dashboard'));
        add_action('wp_ajax_smm_submit_intake', array($this, 'handle_intake_submission'));
        add_action('wp_ajax_nopriv_smm_submit_intake', array($this, 'handle_intake_submission'));

        // New AJAX handlers for client dashboard features
        add_action('wp_ajax_smm_get_post_calendar', array($this, 'get_post_calendar'));
        add_action('wp_ajax_smm_get_pending_posts', array($this, 'get_pending_posts'));
        add_action('wp_ajax_smm_get_unread_messages', array($this, 'get_unread_messages'));
        add_action('wp_ajax_smm_get_private_messages', array($this, 'get_private_messages'));
        add_action('wp_ajax_smm_get_client_settings', array($this, 'get_client_settings'));
        add_action('wp_ajax_smm_save_client_settings', array($this, 'save_client_settings'));

        // Register AJAX handler for sending private messages
        add_action('wp_ajax_smm_send_private_message', array($this, 'send_private_message'));
    }
    
    // AJAX handler to get post content calendar data
    public function get_post_calendar() {
        check_ajax_referer('smm_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        // Fetch scheduled posts for current user
        if (!class_exists('SMM_Database')) {
            wp_send_json_error(array('message' => 'Database class not available'));
            return;
        }
        $database = new SMM_Database();
        $user_id = get_current_user_id();
        $posts = $database->get_scheduled_posts($user_id);
        wp_send_json_success(array('posts' => $posts));
    }
    
    // AJAX handler to get pending posts for approval
    public function get_pending_posts() {
        check_ajax_referer('smm_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        if (!class_exists('SMM_Database')) {
            wp_send_json_error(array('message' => 'Database class not available'));
            return;
        }
        $database = new SMM_Database();
        $user_id = get_current_user_id();
        $pending_posts = $database->get_pending_posts($user_id);
        wp_send_json_success(array('pending_posts' => $pending_posts));
    }
    
    // AJAX handler to get unread private messages
    public function get_unread_messages() {
        check_ajax_referer('smm_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        if (!class_exists('SMM_Database')) {
            wp_send_json_error(array('message' => 'Database class not available'));
            return;
        }
        $database = new SMM_Database();
        $user_id = get_current_user_id();

        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;

        if (in_array('social_media_manager', $user_roles)) {
            // Social media managers can see unread messages with any client
            $unread_messages = $database->get_unread_messages_for_manager($user_id);
        } elseif (in_array('social_media_manager_client', $user_roles) || in_array('smm_client', $user_roles)) {
            // Clients can see unread messages only with their assigned social media manager
            $client_id = $database->get_client_id_by_user_id($user_id);
            if (!$client_id) {
                wp_send_json_error(array('message' => 'Client record not found'));
                return;
            }
            $assigned_manager_id = $database->get_assigned_manager_id($client_id);
            $unread_messages = $database->get_unread_messages_between_users($user_id, $assigned_manager_id);
        } elseif (in_array('administrator', $user_roles)) {
            // Admins can see all unread messages
            $unread_messages = $database->get_all_unread_messages();
        } else {
            wp_send_json_error(array('message' => 'You do not have permission to view messages'));
            return;
        }

        wp_send_json_success(array('unread_messages' => $unread_messages));
    }
    
    // AJAX handler to get private messages
    public function get_private_messages() {
        check_ajax_referer('smm_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        if (!class_exists('SMM_Database')) {
            wp_send_json_error(array('message' => 'Database class not available'));
            return;
        }
        $database = new SMM_Database();
        $user_id = get_current_user_id();

        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;

        if (in_array('social_media_manager', $user_roles)) {
            // Social media managers can see messages with any client
            $messages = $database->get_messages_for_manager($user_id);
        } elseif (in_array('social_media_manager_client', $user_roles) || in_array('smm_client', $user_roles)) {
            // Clients can see messages only with their assigned social media manager
            $client_id = $database->get_client_id_by_user_id($user_id);
            if (!$client_id) {
                wp_send_json_error(array('message' => 'Client record not found'));
                return;
            }
            $assigned_manager_id = $database->get_assigned_manager_id($client_id);
            $messages = $database->get_messages_between_users($user_id, $assigned_manager_id);
        } elseif (in_array('administrator', $user_roles)) {
            // Admins can see all messages
            $messages = $database->get_all_messages();
        } else {
            wp_send_json_error(array('message' => 'You do not have permission to view messages'));
            return;
        }

        wp_send_json_success(array('messages' => $messages));
    }
    
    // AJAX handler to get client settings
    public function get_client_settings() {
        check_ajax_referer('smm_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        $settings = get_option('smm_user_settings_' . get_current_user_id(), array());
        wp_send_json_success(array('settings' => $settings));
    }
    
    // AJAX handler to save client settings
    public function save_client_settings() {
        check_ajax_referer('smm_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        $settings = array();
        $allowed_settings = array(
            'email', 'password'
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

    // AJAX handler to send private message
    public function send_private_message() {
        check_ajax_referer('smm_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        if (!class_exists('SMM_Database')) {
            wp_send_json_error(array('message' => 'Database class not available'));
            return;
        }
        $sender_id = get_current_user_id();
        $recipient_id = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : 0;
        $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
        $message_content = isset($_POST['message_content']) ? sanitize_textarea_field($_POST['message_content']) : '';

        if ($recipient_id <= 0 || empty($message_content)) {
            wp_send_json_error(array('message' => 'Invalid message data'));
            return;
        }

        $database = new SMM_Database();

        // Get current user role
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;

        // Check messaging permissions
        if (in_array('social_media_manager', $user_roles)) {
            // Social media managers can message any client - no restriction
        } elseif (in_array('social_media_manager_client', $user_roles) || in_array('smm_client', $user_roles)) {
            // Clients can only message their assigned social media manager
            $client_id = $database->get_client_id_by_user_id($sender_id);
            if (!$client_id) {
                wp_send_json_error(array('message' => 'Client record not found'));
                return;
            }
            $assigned_manager_id = $database->get_assigned_manager_id($client_id);
            if ($recipient_id != $assigned_manager_id) {
                wp_send_json_error(array('message' => 'You can only message your assigned social media manager'));
                return;
            }
        } elseif (in_array('administrator', $user_roles)) {
            // Admins can message anyone
        } else {
            wp_send_json_error(array('message' => 'You do not have permission to send messages'));
            return;
        }

        $data = array(
            'sender_id' => $sender_id,
            'recipient_id' => $recipient_id,
            'subject' => $subject,
            'message_content' => $message_content,
            'is_read' => 0,
            'created_at' => current_time('mysql')
        );

        $result = $database->insert_message($data);

        if ($result) {
            wp_send_json_success(array('message' => 'Message sent successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send message'));
        }
    }
    
    public function init() {
        // Initialize client frontend functionality
    }
    
    public function render_intake_form($atts) {
        $atts = shortcode_atts(array(
            'title' => 'Social Media Strategy Intake Form'
        ), $atts);
        
        ob_start();
        ?>
        <div class="smm-client-intake-form">
            <div class="smm-intake-header">
                <h1><?php echo esc_html($atts['title']); ?></h1>
                <p>Help us create a comprehensive social media strategy tailored to your business needs. Please provide detailed information about your company, goals, and preferences.</p>
            </div>
            
            <form id="smm-intake-form" class="smm-intake-form">
                <?php wp_nonce_field('smm_intake_nonce', 'smm_intake_nonce'); ?>
                
                <!-- Company Information Section -->
                <div class="smm-form-section">
                    <h2>Company Information</h2>
                    
                    <div class="smm-form-row">
                        <div class="smm-form-group smm-form-half">
                            <label for="company_name">Company Name *</label>
                            <input type="text" id="company_name" name="company_name" required>
                        </div>
                        <div class="smm-form-group smm-form-half">
                            <label for="industry">Industry *</label>
                            <select id="industry" name="industry" required>
                                <option value="">Select Industry</option>
                                <option value="technology">Technology</option>
                                <option value="healthcare">Healthcare</option>
                                <option value="finance">Finance</option>
                                <option value="retail">Retail</option>
                                <option value="food_beverage">Food & Beverage</option>
                                <option value="real_estate">Real Estate</option>
                                <option value="education">Education</option>
                                <option value="entertainment">Entertainment</option>
                                <option value="automotive">Automotive</option>
                                <option value="fashion">Fashion</option>
                                <option value="travel">Travel & Tourism</option>
                                <option value="fitness">Fitness & Wellness</option>
                                <option value="nonprofit">Non-Profit</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="company_description">Company Description *</label>
                        <textarea id="company_description" name="company_description" rows="4" placeholder="Describe your company, products/services, and what makes you unique..." required></textarea>
                    </div>
                    
                    <div class="smm-form-row">
                        <div class="smm-form-group smm-form-half">
                            <label for="company_size">Company Size</label>
                            <select id="company_size" name="company_size">
                                <option value="">Select Size</option>
                                <option value="1-10">1-10 employees</option>
                                <option value="11-50">11-50 employees</option>
                                <option value="51-200">51-200 employees</option>
                                <option value="201-500">201-500 employees</option>
                                <option value="500+">500+ employees</option>
                            </select>
                        </div>
                        <div class="smm-form-group smm-form-half">
                            <label for="website_url">Website URL</label>
                            <input type="url" id="website_url" name="website_url" placeholder="https://yourwebsite.com">
                        </div>
                    </div>
                </div>
                
                <!-- Target Audience Section -->
                <div class="smm-form-section">
                    <h2>Target Audience</h2>
                    
                    <div class="smm-form-row">
                        <div class="smm-form-group smm-form-half">
                            <label for="target_age_range">Primary Age Range *</label>
                            <select id="target_age_range" name="target_age_range" required>
                                <option value="">Select Age Range</option>
                                <option value="18-24">18-24</option>
                                <option value="25-34">25-34</option>
                                <option value="35-44">35-44</option>
                                <option value="45-54">45-54</option>
                                <option value="55-64">55-64</option>
                                <option value="65+">65+</option>
                            </select>
                        </div>
                        <div class="smm-form-group smm-form-half">
                            <label for="target_gender">Target Gender</label>
                            <select id="target_gender" name="target_gender">
                                <option value="">All Genders</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="non-binary">Non-Binary</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="target_location">Target Geographic Location *</label>
                        <input type="text" id="target_location" name="target_location" placeholder="e.g., United States, California, San Francisco" required>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="target_interests">Target Interests & Behaviors *</label>
                        <textarea id="target_interests" name="target_interests" rows="3" placeholder="Describe your ideal customer's interests, hobbies, behaviors, and pain points..." required></textarea>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="customer_personas">Customer Personas</label>
                        <textarea id="customer_personas" name="customer_personas" rows="4" placeholder="Describe your main customer personas (e.g., busy professionals, young parents, tech enthusiasts...)"></textarea>
                    </div>
                </div>
                
                <!-- Business Goals Section -->
                <div class="smm-form-section">
                    <h2>Business Goals & Objectives</h2>
                    
                    <div class="smm-form-group">
                        <label>Primary Social Media Goals * (Select all that apply)</label>
                        <div class="smm-checkbox-group">
                            <label><input type="checkbox" name="primary_goals[]" value="brand_awareness"> Increase Brand Awareness</label>
                            <label><input type="checkbox" name="primary_goals[]" value="lead_generation"> Generate Leads</label>
                            <label><input type="checkbox" name="primary_goals[]" value="sales"> Drive Sales</label>
                            <label><input type="checkbox" name="primary_goals[]" value="engagement"> Boost Engagement</label>
                            <label><input type="checkbox" name="primary_goals[]" value="website_traffic"> Increase Website Traffic</label>
                            <label><input type="checkbox" name="primary_goals[]" value="customer_service"> Customer Service & Support</label>
                            <label><input type="checkbox" name="primary_goals[]" value="community_building"> Build Community</label>
                            <label><input type="checkbox" name="primary_goals[]" value="thought_leadership"> Establish Thought Leadership</label>
                        </div>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="success_metrics">How do you measure success? *</label>
                        <textarea id="success_metrics" name="success_metrics" rows="3" placeholder="Describe your key performance indicators (KPIs) and success metrics..." required></textarea>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="current_challenges">Current Marketing Challenges</label>
                        <textarea id="current_challenges" name="current_challenges" rows="3" placeholder="What challenges are you facing with your current marketing efforts?"></textarea>
                    </div>
                </div>
                
                <!-- Current Social Media Presence -->
                <div class="smm-form-section">
                    <h2>Current Social Media Presence</h2>
                    
                    <div class="smm-form-group">
                        <label>Which platforms are you currently using? (Check all that apply)</label>
                        <div class="smm-checkbox-group">
                            <label><input type="checkbox" name="current_platforms[]" value="facebook"> Facebook</label>
                            <label><input type="checkbox" name="current_platforms[]" value="instagram"> Instagram</label>
                            <label><input type="checkbox" name="current_platforms[]" value="twitter"> Twitter/X</label>
                            <label><input type="checkbox" name="current_platforms[]" value="linkedin"> LinkedIn</label>
                            <label><input type="checkbox" name="current_platforms[]" value="youtube"> YouTube</label>
                            <label><input type="checkbox" name="current_platforms[]" value="tiktok"> TikTok</label>
                            <label><input type="checkbox" name="current_platforms[]" value="pinterest"> Pinterest</label>
                            <label><input type="checkbox" name="current_platforms[]" value="snapchat"> Snapchat</label>
                            <label><input type="checkbox" name="current_platforms[]" value="none"> None - Starting from scratch</label>
                        </div>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="current_performance">Current Social Media Performance</label>
                        <textarea id="current_performance" name="current_performance" rows="3" placeholder="Describe your current follower count, engagement rates, and performance across platforms..."></textarea>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="posting_frequency">Current Posting Frequency</label>
                        <select id="posting_frequency" name="posting_frequency">
                            <option value="">Select Frequency</option>
                            <option value="daily">Daily</option>
                            <option value="few_times_week">Few times per week</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="irregular">Irregular</option>
                            <option value="none">Not currently posting</option>
                        </select>
                    </div>
                </div>
                
                <!-- Content Preferences -->
                <div class="smm-form-section">
                    <h2>Content Preferences</h2>
                    
                    <div class="smm-form-group">
                        <label>Preferred Content Types * (Select all that apply)</label>
                        <div class="smm-checkbox-group">
                            <label><input type="checkbox" name="content_types[]" value="educational"> Educational/How-to Content</label>
                            <label><input type="checkbox" name="content_types[]" value="behind_scenes"> Behind-the-Scenes</label>
                            <label><input type="checkbox" name="content_types[]" value="product_showcase"> Product/Service Showcase</label>
                            <label><input type="checkbox" name="content_types[]" value="user_generated"> User-Generated Content</label>
                            <label><input type="checkbox" name="content_types[]" value="industry_news"> Industry News & Trends</label>
                            <label><input type="checkbox" name="content_types[]" value="testimonials"> Customer Testimonials</label>
                            <label><input type="checkbox" name="content_types[]" value="entertainment"> Entertainment/Fun Content</label>
                            <label><input type="checkbox" name="content_types[]" value="promotional"> Promotional Content</label>
                        </div>
                    </div>
                    
                    <div class="smm-form-row">
                        <div class="smm-form-group smm-form-half">
                            <label for="brand_voice">Brand Voice & Tone *</label>
                            <select id="brand_voice" name="brand_voice" required>
                                <option value="">Select Brand Voice</option>
                                <option value="professional">Professional</option>
                                <option value="friendly">Friendly & Casual</option>
                                <option value="authoritative">Authoritative</option>
                                <option value="playful">Playful & Fun</option>
                                <option value="inspirational">Inspirational</option>
                                <option value="conversational">Conversational</option>
                                <option value="luxury">Luxury & Premium</option>
                            </select>
                        </div>
                        <div class="smm-form-group smm-form-half">
                            <label for="content_language">Primary Content Language</label>
                            <select id="content_language" name="content_language">
                                <option value="english">English</option>
                                <option value="spanish">Spanish</option>
                                <option value="french">French</option>
                                <option value="german">German</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="content_themes">Content Themes & Topics</label>
                        <textarea id="content_themes" name="content_themes" rows="3" placeholder="What topics, themes, or subjects should we focus on in your content?"></textarea>
                    </div>
                </div>
                
                <!-- Competition & Market Analysis -->
                <div class="smm-form-section">
                    <h2>Competition & Market Analysis</h2>
                    
                    <div class="smm-form-group">
                        <label for="main_competitors">Main Competitors *</label>
                        <textarea id="main_competitors" name="main_competitors" rows="3" placeholder="List your main competitors and their social media handles if known..." required></textarea>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="competitor_analysis">What do you like/dislike about competitors' social media?</label>
                        <textarea id="competitor_analysis" name="competitor_analysis" rows="3" placeholder="Describe what works well for your competitors and what you'd like to do differently..."></textarea>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="unique_value_proposition">Your Unique Value Proposition *</label>
                        <textarea id="unique_value_proposition" name="unique_value_proposition" rows="3" placeholder="What makes your business unique? How do you differentiate from competitors?" required></textarea>
                    </div>
                </div>
                
                <!-- Budget & Resources -->
                <div class="smm-form-section">
                    <h2>Budget & Resources</h2>
                    
                    <div class="smm-form-row">
                        <div class="smm-form-group smm-form-half">
                            <label for="monthly_budget">Monthly Social Media Budget *</label>
                            <select id="monthly_budget" name="monthly_budget" required>
                                <option value="">Select Budget Range</option>
                                <option value="under_500">Under $500</option>
                                <option value="500_1000">$500 - $1,000</option>
                                <option value="1000_2500">$1,000 - $2,500</option>
                                <option value="2500_5000">$2,500 - $5,000</option>
                                <option value="5000_10000">$5,000 - $10,000</option>
                                <option value="over_10000">Over $10,000</option>
                            </select>
                        </div>
                        <div class="smm-form-group smm-form-half">
                            <label for="ad_budget">Paid Advertising Budget</label>
                            <select id="ad_budget" name="ad_budget">
                                <option value="">Select Ad Budget</option>
                                <option value="no_ads">No paid advertising</option>
                                <option value="under_200">Under $200/month</option>
                                <option value="200_500">$200 - $500/month</option>
                                <option value="500_1000">$500 - $1,000/month</option>
                                <option value="1000_2500">$1,000 - $2,500/month</option>
                                <option value="over_2500">Over $2,500/month</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="internal_resources">Internal Resources Available</label>
                        <textarea id="internal_resources" name="internal_resources" rows="3" placeholder="Describe your internal team's capacity for social media (content creation, photography, video, etc.)"></textarea>
                    </div>
                </div>
                
                <!-- Timeline & Expectations -->
                <div class="smm-form-section">
                    <h2>Timeline & Expectations</h2>
                    
                    <div class="smm-form-row">
                        <div class="smm-form-group smm-form-half">
                            <label for="start_timeline">When would you like to start? *</label>
                            <select id="start_timeline" name="start_timeline" required>
                                <option value="">Select Timeline</option>
                                <option value="immediately">Immediately</option>
                                <option value="within_week">Within a week</option>
                                <option value="within_month">Within a month</option>
                                <option value="within_quarter">Within 3 months</option>
                            </select>
                        </div>
                        <div class="smm-form-group smm-form-half">
                            <label for="campaign_duration">Expected Campaign Duration</label>
                            <select id="campaign_duration" name="campaign_duration">
                                <option value="">Select Duration</option>
                                <option value="3_months">3 months</option>
                                <option value="6_months">6 months</option>
                                <option value="12_months">12 months</option>
                                <option value="ongoing">Ongoing partnership</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="success_timeline">When do you expect to see results? *</label>
                        <select id="success_timeline" name="success_timeline" required>
                            <option value="">Select Timeline</option>
                            <option value="1_month">Within 1 month</option>
                            <option value="3_months">Within 3 months</option>
                            <option value="6_months">Within 6 months</option>
                            <option value="12_months">Within 12 months</option>
                        </select>
                    </div>
                </div>
                
                <!-- Additional Information -->
                <div class="smm-form-section">
                    <h2>Additional Information</h2>
                    
                    <div class="smm-form-group">
                        <label for="brand_guidelines">Existing Brand Guidelines</label>
                        <textarea id="brand_guidelines" name="brand_guidelines" rows="3" placeholder="Describe your brand colors, fonts, logo usage, and any existing brand guidelines..."></textarea>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="content_restrictions">Content Restrictions or Sensitivities</label>
                        <textarea id="content_restrictions" name="content_restrictions" rows="3" placeholder="Are there any topics, images, or content types we should avoid?"></textarea>
                    </div>
                    
                    <div class="smm-form-group">
                        <label for="additional_notes">Additional Notes or Special Requests</label>
                        <textarea id="additional_notes" name="additional_notes" rows="4" placeholder="Any additional information, special requests, or questions you'd like to share..."></textarea>
                    </div>
                </div>
                
                <div class="smm-form-actions">
                    <button type="submit" class="smm-btn smm-btn-primary smm-btn-large">
                        Generate My Social Media Strategy
                    </button>
                </div>
            </form>
        </div>
        
        <div id="smm-strategy-result" class="smm-strategy-result" style="display: none;">
            <div class="smm-strategy-header">
                <h2>Your Personalized Social Media Strategy</h2>
                <p>Generated using AI based on your responses</p>
            </div>
            <div class="smm-strategy-content">
                <!-- Strategy content will be loaded here -->
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_client_dashboard($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please log in to access your dashboard.</p>';
        }
        
        ob_start();
        ?>
        <div class="smm-client-dashboard">
            <div class="smm-client-header">
                <h1>Client Dashboard</h1>
                <p>Track your social media performance and strategy progress</p>
            </div>
            
            <div class="smm-client-overview">
                <div class="smm-overview-card">
                    <h3>Strategy Status</h3>
                    <div class="smm-status-indicator active">Active</div>
                </div>
                <div class="smm-overview-card">
                    <h3>This Month's Posts</h3>
                    <div class="smm-metric-large">24</div>
                </div>
                <div class="smm-overview-card">
                    <h3>Total Engagement</h3>
                    <div class="smm-metric-large">1,234</div>
                </div>
                <div class="smm-overview-card">
                    <h3>New Followers</h3>
                    <div class="smm-metric-large">+89</div>
                </div>
            </div>
            
            <div class="smm-client-content">
                <div class="smm-content-section">
                    <h2>Post Content Calendar</h2>
                    <div id="smm-post-calendar">
                        <!-- Post content calendar will be rendered here -->
                        <p>Loading calendar...</p>
                    </div>
                </div>

                <div class="smm-content-section">
                    <h2>Pending Posts for Approval</h2>
                    <div id="smm-pending-posts-table">
                        <!-- Pending posts table will be rendered here -->
                        <p>Loading pending posts...</p>
                    </div>
                </div>

                <div class="smm-content-section">
                    <h2>Unread Private Messages</h2>
                    <div id="smm-unread-messages-table">
                        <!-- Unread messages table will be rendered here -->
                        <p>Loading unread messages...</p>
                    </div>
                </div>

                <div class="smm-content-section">
                    <h2>Private Messages</h2>
                    <div id="smm-private-messages-section">
                        <!-- Private message section will be rendered here -->
                        <p>Loading messages...</p>
                    </div>
                </div>

                <div class="smm-content-section">
                    <h2>Analytics</h2>
                    <div class="smm-analytics-widget">
                        <canvas id="clientAnalyticsChart"></canvas>
                    </div>
                </div>

                <div class="smm-content-section">
                    <h2>Settings</h2>
                    <div id="smm-client-settings-section">
                        <!-- Settings form will be rendered here -->
                        <p>Loading settings...</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function handle_intake_submission() {
        check_ajax_referer('smm_intake_nonce', 'nonce');
        
        // Collect and sanitize form data
        $intake_data = array();
        $fields = array(
            'company_name', 'industry', 'company_description', 'company_size', 'website_url',
            'target_age_range', 'target_gender', 'target_location', 'target_interests', 'customer_personas',
            'success_metrics', 'current_challenges', 'current_performance', 'posting_frequency',
            'brand_voice', 'content_language', 'content_themes', 'main_competitors', 'competitor_analysis',
            'unique_value_proposition', 'monthly_budget', 'ad_budget', 'internal_resources',
            'start_timeline', 'campaign_duration', 'success_timeline', 'brand_guidelines',
            'content_restrictions', 'additional_notes'
        );
        
        foreach ($fields as $field) {
            $intake_data[$field] = sanitize_textarea_field($_POST[$field] ?? '');
        }
        
        // Handle checkbox arrays
        $checkbox_fields = array('primary_goals', 'current_platforms', 'content_types');
        foreach ($checkbox_fields as $field) {
            $intake_data[$field] = isset($_POST[$field]) ? array_map('sanitize_text_field', $_POST[$field]) : array();
        }
        
        // Save to database
        if (!class_exists('SMM_Database')) {
            wp_send_json_error(array(
                'message' => 'Database class not available. Please try again.'
            ));
            return;
        }
        
        $database = new SMM_Database();
        $client_data = array(
            'user_id' => get_current_user_id(),
            'company_name' => $intake_data['company_name'],
            'industry' => $intake_data['industry'],
            'target_audience' => json_encode(array(
                'age_range' => $intake_data['target_age_range'],
                'gender' => $intake_data['target_gender'],
                'location' => $intake_data['target_location'],
                'interests' => $intake_data['target_interests'],
                'personas' => $intake_data['customer_personas']
            )),
            'business_goals' => json_encode(array(
                'primary_goals' => $intake_data['primary_goals'],
                'success_metrics' => $intake_data['success_metrics'],
                'challenges' => $intake_data['current_challenges']
            )),
            'current_social_presence' => json_encode(array(
                'platforms' => $intake_data['current_platforms'],
                'performance' => $intake_data['current_performance'],
                'frequency' => $intake_data['posting_frequency']
            )),
            'budget_range' => $intake_data['monthly_budget'],
            'preferred_platforms' => json_encode($intake_data['current_platforms']),
            'content_preferences' => json_encode(array(
                'types' => $intake_data['content_types'],
                'voice' => $intake_data['brand_voice'],
                'language' => $intake_data['content_language'],
                'themes' => $intake_data['content_themes']
            )),
            'brand_voice' => $intake_data['brand_voice'],
            'competitors' => $intake_data['main_competitors'],
            'intake_data' => json_encode($intake_data)
        );
        
        $result = $database->save_client_data($client_data);
        
        if ($result) {
            // Generate strategy using ChatGPT
            if (class_exists('SMM_ChatGPT_Integration')) {
                $chatgpt = new SMM_ChatGPT_Integration();
                $strategy = $chatgpt->generate_strategy($intake_data);
                
                wp_send_json_success(array(
                    'message' => 'Intake form submitted successfully!',
                    'strategy' => $strategy
                ));
            } else {
                wp_send_json_success(array(
                    'message' => 'Intake form submitted successfully! Strategy generation is currently unavailable.',
                    'strategy' => 'Strategy generation service is temporarily unavailable. Please contact support.'
                ));
            }
        } else {
            wp_send_json_error(array(
                'message' => 'Failed to save intake data. Please try again.'
            ));
        }
    }
}
