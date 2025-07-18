<?php
/**
 * Frontend management class for Social Media Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class SMM_Frontend {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_shortcode('smm_dashboard', array($this, 'render_dashboard'));
        add_shortcode('smm_profile', array($this, 'render_profile'));
        add_shortcode('smm_timesheet', array($this, 'render_timesheet'));
        add_shortcode('smm_messaging', array($this, 'render_messaging'));
        add_shortcode('smm_reports', array($this, 'render_reports'));
        add_shortcode('smm_settings', array($this, 'render_settings'));
    }
    
    public function init() {
        // Initialize frontend functionality
        if (!is_admin()) {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        }
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_style('smm-frontend-style', SMM_PLUGIN_URL . 'assets/css/frontend.css', array(), SMM_VERSION);
        wp_enqueue_script('smm-frontend-script', SMM_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), SMM_VERSION, true);
        
        wp_localize_script('smm-frontend-script', 'smm_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('smm_nonce')
        ));
    }
    
    public function render_dashboard($atts) {
        $atts = shortcode_atts(array(
            'user_id' => get_current_user_id(),
            'client_id' => null
        ), $atts);
        
        if (!is_user_logged_in()) {
            return '<p>Please log in to access the dashboard.</p>';
        }
        
        ob_start();
        ?>
        <div class="smm-frontend-dashboard">
            <div class="smm-dashboard-header">
                <div class="smm-logo">
                    <h1>Social Media Manager</h1>
                    <p>Dashboard</p>
                </div>
                <div class="smm-user-info">
                    <span>Welcome, <?php echo wp_get_current_user()->display_name; ?></span>
                </div>
            </div>
            
            <div class="smm-dashboard-nav">
                <ul class="smm-nav-tabs">
                    <li class="active"><a href="#dashboard" data-tab="dashboard">Dashboard</a></li>
                    <li><a href="#analytics" data-tab="analytics">Analytics</a></li>
                    <li><a href="#campaigns" data-tab="campaigns">Campaigns</a></li>
                    <li><a href="#content" data-tab="content">Content</a></li>
                </ul>
            </div>
            
            <div class="smm-dashboard-content">
                <div id="dashboard" class="smm-tab-content active">
                    <?php $this->render_dashboard_overview(); ?>
                </div>
                <div id="analytics" class="smm-tab-content">
                    <?php $this->render_analytics_section(); ?>
                </div>
                <div id="campaigns" class="smm-tab-content">
                    <?php $this->render_campaigns_section(); ?>
                </div>
                <div id="content" class="smm-tab-content">
                    <?php $this->render_content_section(); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function render_dashboard_overview() {
        ?>
        <div class="smm-overview-grid">
            <div class="smm-widget-row">
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-header">
                        <h3>Total Posts</h3>
                    </div>
                    <div class="smm-widget-content">
                        <div class="smm-metric-value">156</div>
                        <div class="smm-metric-change positive">+12%</div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-header">
                        <h3>Engagement Rate</h3>
                    </div>
                    <div class="smm-widget-content">
                        <div class="smm-metric-value">4.2%</div>
                        <div class="smm-metric-change positive">+0.8%</div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-header">
                        <h3>Followers</h3>
                    </div>
                    <div class="smm-widget-content">
                        <div class="smm-metric-value">12,543</div>
                        <div class="smm-metric-change positive">+234</div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-header">
                        <h3>Reach</h3>
                    </div>
                    <div class="smm-widget-content">
                        <div class="smm-metric-value">45,678</div>
                        <div class="smm-metric-change negative">-2.1%</div>
                    </div>
                </div>
            </div>
            
            <div class="smm-widget-row">
                <div class="smm-widget smm-widget-half">
                    <div class="smm-widget-header">
                        <h3>Recent Posts Performance</h3>
                    </div>
                    <div class="smm-widget-content">
                        <canvas id="postsChart"></canvas>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-half">
                    <div class="smm-widget-header">
                        <h3>Platform Distribution</h3>
                    </div>
                    <div class="smm-widget-content">
                        <canvas id="platformChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_analytics_section() {
        echo '<div class="smm-analytics-section">';
        echo '<h2>Analytics & Insights</h2>';
        echo '<p>Detailed analytics will be displayed here.</p>';
        echo '</div>';
    }
    
    private function render_campaigns_section() {
        echo '<div class="smm-campaigns-section">';
        echo '<h2>Campaign Management</h2>';
        echo '<p>Campaign management interface will be displayed here.</p>';
        echo '</div>';
    }
    
    private function render_content_section() {
        echo '<div class="smm-content-section">';
        echo '<h2>Content Management</h2>';
        echo '<p>Content creation and scheduling interface will be displayed here.</p>';
        echo '</div>';
    }
    
    public function render_profile($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please log in to access your profile.</p>';
        }
        
        ob_start();
        ?>
        <div class="smm-profile-section">
            <h2>Profile Management</h2>
            <form class="smm-profile-form">
                <div class="smm-form-group">
                    <label>Display Name</label>
                    <input type="text" name="display_name" value="<?php echo esc_attr(wp_get_current_user()->display_name); ?>">
                </div>
                <div class="smm-form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>">
                </div>
                <div class="smm-form-group">
                    <label>Bio</label>
                    <textarea name="bio" rows="4"></textarea>
                </div>
                <button type="submit" class="smm-btn smm-btn-primary">Update Profile</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_timesheet($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please log in to access timesheet.</p>';
        }
        
        ob_start();
        ?>
        <div class="smm-timesheet-section">
            <div class="smm-timesheet-header">
                <h2>Timesheet</h2>
                <button class="smm-btn smm-btn-primary" id="add-time-entry">Add Entry</button>
            </div>
            
            <div class="smm-timesheet-form" id="timesheet-form" style="display: none;">
                <form class="smm-time-entry-form">
                    <div class="smm-form-row">
                        <div class="smm-form-group">
                            <label>Date</label>
                            <input type="date" name="work_date" required>
                        </div>
                        <div class="smm-form-group">
                            <label>Hours</label>
                            <input type="number" name="hours_worked" step="0.25" min="0" max="24" required>
                        </div>
                    </div>
                    <div class="smm-form-group">
                        <label>Task Description</label>
                        <textarea name="task_description" rows="3" required></textarea>
                    </div>
                    <div class="smm-form-actions">
                        <button type="submit" class="smm-btn smm-btn-primary">Save Entry</button>
                        <button type="button" class="smm-btn smm-btn-secondary" id="cancel-entry">Cancel</button>
                    </div>
                </form>
            </div>
            
            <div class="smm-timesheet-list">
                <table class="smm-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Hours</th>
                            <th>Task</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="timesheet-entries">
                        <!-- Timesheet entries will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_messaging($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please log in to access messaging.</p>';
        }
        
        ob_start();
        ?>
        <div class="smm-messaging-section">
            <div class="smm-messaging-layout">
                <div class="smm-conversations-list">
                    <div class="smm-conversations-header">
                        <h3>Conversations</h3>
                        <button class="smm-btn smm-btn-primary smm-btn-sm">New Message</button>
                    </div>
                    <div class="smm-conversations">
                        <!-- Conversations will be loaded here -->
                    </div>
                </div>
                
                <div class="smm-message-area">
                    <div class="smm-message-header">
                        <h3>Select a conversation</h3>
                    </div>
                    <div class="smm-messages">
                        <!-- Messages will be displayed here -->
                    </div>
                    <div class="smm-message-input">
                        <form class="smm-send-message-form">
                            <div class="smm-input-group">
                                <textarea name="message_content" placeholder="Type your message..." rows="3"></textarea>
                                <button type="submit" class="smm-btn smm-btn-primary">Send</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_reports($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please log in to access reports.</p>';
        }
        
        ob_start();
        ?>
        <div class="smm-reports-section">
            <div class="smm-reports-header">
                <h2>Reports & Analytics</h2>
                <div class="smm-report-filters">
                    <select name="report_type">
                        <option value="performance">Performance Report</option>
                        <option value="engagement">Engagement Report</option>
                        <option value="growth">Growth Report</option>
                    </select>
                    <select name="date_range">
                        <option value="7">Last 7 days</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 90 days</option>
                    </select>
                    <button class="smm-btn smm-btn-primary">Generate Report</button>
                </div>
            </div>
            
            <div class="smm-report-content">
                <div class="smm-report-summary">
                    <div class="smm-summary-card">
                        <h4>Total Impressions</h4>
                        <div class="smm-summary-value">125,430</div>
                    </div>
                    <div class="smm-summary-card">
                        <h4>Total Engagement</h4>
                        <div class="smm-summary-value">5,234</div>
                    </div>
                    <div class="smm-summary-card">
                        <h4>Avg. Engagement Rate</h4>
                        <div class="smm-summary-value">4.2%</div>
                    </div>
                    <div class="smm-summary-card">
                        <h4>New Followers</h4>
                        <div class="smm-summary-value">+456</div>
                    </div>
                </div>
                
                <div class="smm-report-charts">
                    <div class="smm-chart-container">
                        <h3>Performance Over Time</h3>
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_settings($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please log in to access settings.</p>';
        }
        
        ob_start();
        ?>
        <div class="smm-settings-section">
            <h2>Settings</h2>
            <div class="smm-settings-tabs">
                <ul class="smm-tabs-nav">
                    <li class="active"><a href="#general" data-tab="general">General</a></li>
                    <li><a href="#notifications" data-tab="notifications">Notifications</a></li>
                    <li><a href="#integrations" data-tab="integrations">Integrations</a></li>
                </ul>
                
                <div class="smm-tabs-content">
                    <div id="general" class="smm-tab-content active">
                        <form class="smm-settings-form">
                            <div class="smm-form-group">
                                <label>Default Posting Time</label>
                                <input type="time" name="default_post_time">
                            </div>
                            <div class="smm-form-group">
                                <label>Time Zone</label>
                                <select name="timezone">
                                    <option value="UTC">UTC</option>
                                    <option value="America/New_York">Eastern Time</option>
                                    <option value="America/Chicago">Central Time</option>
                                    <option value="America/Denver">Mountain Time</option>
                                    <option value="America/Los_Angeles">Pacific Time</option>
                                </select>
                            </div>
                            <button type="submit" class="smm-btn smm-btn-primary">Save Settings</button>
                        </form>
                    </div>
                    
                    <div id="notifications" class="smm-tab-content">
                        <form class="smm-settings-form">
                            <div class="smm-form-group">
                                <label>
                                    <input type="checkbox" name="email_notifications" checked>
                                    Email Notifications
                                </label>
                            </div>
                            <div class="smm-form-group">
                                <label>
                                    <input type="checkbox" name="post_reminders" checked>
                                    Post Reminders
                                </label>
                            </div>
                            <button type="submit" class="smm-btn smm-btn-primary">Save Settings</button>
                        </form>
                    </div>
                    
                    <div id="integrations" class="smm-tab-content">
                        <div class="smm-integrations-list">
                            <div class="smm-integration-item">
                                <h4>Facebook</h4>
                                <p>Connect your Facebook account</p>
                                <button class="smm-btn smm-btn-secondary">Connect</button>
                            </div>
                            <div class="smm-integration-item">
                                <h4>Instagram</h4>
                                <p>Connect your Instagram account</p>
                                <button class="smm-btn smm-btn-secondary">Connect</button>
                            </div>
                            <div class="smm-integration-item">
                                <h4>Twitter</h4>
                                <p>Connect your Twitter account</p>
                                <button class="smm-btn smm-btn-secondary">Connect</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
