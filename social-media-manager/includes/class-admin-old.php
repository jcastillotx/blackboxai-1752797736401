<?php
/**
 * Admin dashboard management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class SMM_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Social Media Manager',
            'Social Media Manager',
            'manage_options',
            'social-media-manager',
            array($this, 'admin_page'),
            'dashicons-share',
            30
        );
        
        add_submenu_page(
            'social-media-manager',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'social-media-manager',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'social-media-manager',
            'Clients',
            'Clients',
            'manage_options',
            'smm-clients',
            array($this, 'clients_page')
        );
        
        add_submenu_page(
            'social-media-manager',
            'Campaigns',
            'Campaigns',
            'manage_options',
            'smm-campaigns',
            array($this, 'campaigns_page')
        );
        
        add_submenu_page(
            'social-media-manager',
            'Analytics',
            'Analytics',
            'manage_options',
            'smm-analytics',
            array($this, 'analytics_page')
        );
        
        add_submenu_page(
            'social-media-manager',
            'Settings',
            'Settings',
            'manage_options',
            'smm-settings',
            array($this, 'settings_page')
        );
    }
    
    public function admin_init() {
        register_setting('smm_settings', 'smm_settings');
    }
    
    public function admin_page() {
        ?>
        <div class="wrap smm-admin-wrap">
            <div class="smm-dashboard-header">
                <div class="smm-logo">
                    <h1>yourLOGO</h1>
                </div>
                <div class="smm-client-selector">
                    <select class="smm-client-dropdown">
                        <option>My Client</option>
                    </select>
                </div>
                <div class="smm-header-actions">
                    <button class="smm-btn smm-btn-secondary">Ask AI</button>
                    <select class="smm-date-range">
                        <option>Last 30 Days</option>
                    </select>
                    <button class="smm-btn smm-btn-primary">Edit Dashboard</button>
                </div>
            </div>
            
            <div class="smm-dashboard-nav">
                <ul class="nav-tabs">
                    <li class="active"><a href="#overview" data-tab="overview">Overview</a></li>
                    <li><a href="#dashboard" data-tab="dashboard">Dashboard</a></li>
                    <li><a href="#activity" data-tab="activity">Activity</a></li>
                    <li><a href="#tasks" data-tab="tasks">Tasks</a></li>
                    <li><a href="#goals" data-tab="goals">Goals</a></li>
                </ul>
            </div>
            
            <div class="smm-dashboard-content">
                <div id="overview" class="tab-content active">
                    <?php $this->render_overview_tab(); ?>
                </div>
                <div id="dashboard" class="tab-content">
                    <?php $this->render_dashboard_tab(); ?>
                </div>
                <div id="activity" class="tab-content">
                    <?php $this->render_activity_tab(); ?>
                </div>
                <div id="tasks" class="tab-content">
                    <?php $this->render_tasks_tab(); ?>
                </div>
                <div id="goals" class="tab-content">
                    <?php $this->render_goals_tab(); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_overview_tab() {
        ?>
        <div class="smm-overview-grid">
            <!-- First Row -->
            <div class="smm-widget-row">
                <div class="smm-widget smm-widget-third">
                    <div class="smm-widget-header">
                        <h3>Rankings</h3>
                    </div>
                    <div class="smm-widget-content">
                        <div class="smm-rankings-grid">
                            <div class="smm-metric-card">
                                <div class="smm-metric-icon">📊</div>
                                <div class="smm-metric-info">
                                    <h4>Google Rankings</h4>
                                    <div class="smm-metric-value large">10</div>
                                </div>
                            </div>
                            <div class="smm-metric-card">
                                <div class="smm-metric-icon">📈</div>
                                <div class="smm-metric-info">
                                    <h4>Google Change</h4>
                                    <div class="smm-metric-value large positive">▲ 4</div>
                                </div>
                            </div>
                        </div>
                        <div class="smm-rankings-chart">
                            <canvas id="rankingsChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-third">
                    <div class="smm-widget-header">
                        <h3>Google Analytics</h3>
                    </div>
                    <div class="smm-widget-content">
                        <div class="smm-sessions-display">
                            <div class="smm-sessions-chart">
                                <canvas id="sessionsChart"></canvas>
                            </div>
                            <div class="smm-sessions-center">
                                <div class="smm-sessions-number">2,787</div>
                                <div class="smm-sessions-label">Sessions</div>
                            </div>
                        </div>
                        <div class="smm-sessions-legend">
                            <div class="legend-item"><span class="color-dot referral"></span> Referral - 602</div>
                            <div class="legend-item"><span class="color-dot organic"></span> Organic Search - 573</div>
                            <div class="legend-item"><span class="color-dot direct"></span> Direct - 564</div>
                            <div class="legend-item"><span class="color-dot other"></span> Other - 410</div>
                            <div class="legend-item"><span class="color-dot paid"></span> Paid Search - 212</div>
                            <div class="legend-item"><span class="color-dot social"></span> Social - 178</div>
                            <div class="legend-item"><span class="color-dot display"></span> Display - 126</div>
                            <div class="legend-item"><span class="color-dot email"></span> Email - 122</div>
                        </div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-third">
                    <div class="smm-widget-header">
                        <h3>Google Ads</h3>
                    </div>
                    <div class="smm-widget-content">
                        <div class="smm-conversions-display">
                            <div class="smm-metric-icon">▲</div>
                            <div class="smm-metric-info">
                                <h4>Conversions</h4>
                                <div class="smm-metric-value xlarge">4,414</div>
                            </div>
                        </div>
                        <div class="smm-conversions-chart">
                            <canvas id="conversionsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Second Row -->
            <div class="smm-widget-row">
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-header">
                        <h3>Instagram</h3>
                    </div>
                    <div class="smm-widget-content">
                        <div class="smm-metric-card">
                            <div class="smm-metric-icon">👥</div>
                            <div class="smm-metric-info">
                                <h4>Followers</h4>
                                <div class="smm-metric-value xlarge">3,306</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-header">
                        <h3>Google Business Profile</h3>
                    </div>
                    <div class="smm-widget-content">
                        <div class="smm-rating-display">
                            <div class="smm-rating-circle">
                                <canvas id="ratingChart"></canvas>
                                <div class="smm-rating-center">4.75</div>
                            </div>
                            <div class="smm-rating-info">
                                <h4>Average Rating</h4>
                            </div>
                        </div>
                        <div class="smm-metric-card">
                            <div class="smm-metric-icon">👥</div>
                            <div class="smm-metric-info">
                                <h4>Reviews</h4>
                                <div class="smm-metric-value large">587</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-header">
                        <h3>Salesforce</h3>
                    </div>
                    <div class="smm-widget-content">
                        <div class="smm-metric-card">
                            <div class="smm-metric-icon">💼</div>
                            <div class="smm-metric-info">
                                <h4>Leads</h4>
                                <div class="smm-metric-value xlarge">95,293</div>
                            </div>
                        </div>
                        <div class="smm-trend-line">
                            <canvas id="leadsChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-header">
                        <h3>Google Search Console</h3>
                    </div>
                    <div class="smm-widget-content">
                        <div class="smm-metric-card">
                            <div class="smm-metric-icon">👁️</div>
                            <div class="smm-metric-info">
                                <h4>Impressions</h4>
                                <div class="smm-metric-value xlarge">262 K</div>
                            </div>
                        </div>
                        <div class="smm-trend-line">
                            <canvas id="impressionsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Third Row -->
            <div class="smm-widget-row">
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-content">
                        <div class="smm-metric-icon">📊</div>
                        <div class="smm-metric-info">
                            <h4>Sessions</h4>
                            <div class="smm-metric-value xxlarge">2,787</div>
                        </div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-content">
                        <div class="smm-metric-icon">🎯</div>
                        <div class="smm-metric-info">
                            <h4>Goal Completions</h4>
                            <div class="smm-metric-value xxlarge">3,306</div>
                        </div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-content">
                        <div class="smm-metric-icon">💰</div>
                        <div class="smm-metric-info">
                            <h4>Cost</h4>
                            <div class="smm-metric-value xxlarge">$6,596.00</div>
                        </div>
                        <div class="smm-mini-chart">
                            <canvas id="costChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-content">
                        <div class="smm-metric-icon">📈</div>
                        <div class="smm-metric-info">
                            <h4>CTR</h4>
                            <div class="smm-metric-value xxlarge">3.20%</div>
                        </div>
                        <div class="smm-mini-chart">
                            <canvas id="ctrChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_dashboard_tab() {
        echo '<div class="smm-tab-content">';
        echo '<h2>Dashboard Analytics</h2>';
        echo '<p>Detailed analytics and reporting dashboard will be displayed here.</p>';
        echo '</div>';
    }
    
    private function render_activity_tab() {
        echo '<div class="smm-tab-content">';
        echo '<h2>Recent Activity</h2>';
        echo '<p>Recent social media activities and updates will be displayed here.</p>';
        echo '</div>';
    }
    
    private function render_tasks_tab() {
        echo '<div class="smm-tab-content">';
        echo '<h2>Tasks Management</h2>';
        echo '<p>Task management and scheduling interface will be displayed here.</p>';
        echo '</div>';
    }
    
    private function render_goals_tab() {
        echo '<div class="smm-tab-content">';
        echo '<h2>Goals & Objectives</h2>';
        echo '<p>Goals tracking and performance metrics will be displayed here.</p>';
        echo '</div>';
    }
    
    public function clients_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_clients';

        // Pagination parameters
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 10;
        $offset = ($paged - 1) * $per_page;

        // Get total clients count
        $total_clients = $wpdb->get_var("SELECT COUNT(*) FROM $table");

        // Get clients data with pagination
        $clients = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset));

        // Calculate total pages
        $total_pages = ceil($total_clients / $per_page);

        ?>
        <div class="wrap">
            <h1>Clients Management</h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company Name</th>
                        <th>Industry</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($clients) : ?>
                        <?php foreach ($clients as $client) : ?>
                            <tr>
                                <td><?php echo esc_html($client->id); ?></td>
                                <td><?php echo esc_html($client->company_name); ?></td>
                                <td><?php echo esc_html($client->industry); ?></td>
                                <td><?php echo esc_html($client->created_at); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=smm-clients&action=view&id=' . intval($client->id)); ?>">View</a> |
                                    <a href="<?php echo admin_url('admin.php?page=smm-clients&action=edit&id=' . intval($client->id)); ?>">Edit</a> |
                                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=smm_delete_client&id=' . intval($client->id)), 'smm_delete_client'); ?>" onclick="return confirm('Are you sure you want to delete this client?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">No clients found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        $page_links = paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total' => $total_pages,
                            'current' => $paged
                        ));
                        echo $page_links;
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function campaigns_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_campaigns';

        // Pagination parameters
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 10;
        $offset = ($paged - 1) * $per_page;

        // Get total campaigns count
        $total_campaigns = $wpdb->get_var("SELECT COUNT(*) FROM $table");

        // Get campaigns data with pagination
        $campaigns = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset));

        // Calculate total pages
        $total_pages = ceil($total_campaigns / $per_page);

        ?>
        <div class="wrap">
            <h1>Campaigns Management</h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client ID</th>
                        <th>Campaign Name</th>
                        <th>Platform</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($campaigns) : ?>
                        <?php foreach ($campaigns as $campaign) : ?>
                            <tr>
                                <td><?php echo esc_html($campaign->id); ?></td>
                                <td><?php echo esc_html($campaign->client_id); ?></td>
                                <td><?php echo esc_html($campaign->campaign_name); ?></td>
                                <td><?php echo esc_html($campaign->platform); ?></td>
                                <td><?php echo esc_html($campaign->status); ?></td>
                                <td><?php echo esc_html($campaign->start_date); ?></td>
                                <td><?php echo esc_html($campaign->end_date); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=smm-campaigns&action=view&id=' . intval($campaign->id)); ?>">View</a> |
                                    <a href="<?php echo admin_url('admin.php?page=smm-campaigns&action=edit&id=' . intval($campaign->id)); ?>">Edit</a> |
                                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=smm_delete_campaign&id=' . intval($campaign->id)), 'smm_delete_campaign'); ?>" onclick="return confirm('Are you sure you want to delete this campaign?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8">No campaigns found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        $page_links = paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total' => $total_pages,
                            'current' => $paged
                        ));
                        echo $page_links;
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function analytics_page() {
        ?>
        <div class="wrap">
            <h1>Analytics & Reports</h1>
            <p>Detailed analytics and reporting interface will be implemented here.</p>
        </div>
        <?php
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Social Media Manager Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('smm_settings');
                do_settings_sections('smm_settings');
                $options = get_option('smm_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">ChatGPT API Key</th>
                        <td>
                            <input type="password" name="smm_settings[chatgpt_api_key]" 
                                   value="<?php echo esc_attr($options['chatgpt_api_key'] ?? ''); ?>" 
                                   class="regular-text" />
                            <p class="description">Enter your OpenAI API key for ChatGPT integration.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Facebook API Key</th>
                        <td>
                            <input type="password" name="smm_settings[facebook_api_key]" 
                                   value="<?php echo esc_attr($options['facebook_api_key'] ?? ''); ?>" 
                                   class="regular-text" />
                            <p class="description">Enter your Facebook API key.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Instagram API Key</th>
                        <td>
                            <input type="password" name="smm_settings[instagram_api_key]" 
                                   value="<?php echo esc_attr($options['instagram_api_key'] ?? ''); ?>" 
                                   class="regular-text" />
                            <p class="description">Enter your Instagram API key.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Twitter API Key</th>
                        <td>
                            <input type="password" name="smm_settings[twitter_api_key]" 
                                   value="<?php echo esc_attr($options['twitter_api_key'] ?? ''); ?>" 
                                   class="regular-text" />
                            <p class="description">Enter your Twitter API key.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">LinkedIn API Key</th>
                        <td>
                            <input type="password" name="smm_settings[linkedin_api_key]" 
                                   value="<?php echo esc_attr($options['linkedin_api_key'] ?? ''); ?>" 
                                   class="regular-text" />
                            <p class="description">Enter your LinkedIn API key.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">AI System Prompt</th>
                        <td>
                            <textarea name="smm_settings[system_prompt]" 
                                      rows="10" 
                                      cols="80" 
                                      class="large-text"><?php echo esc_textarea($options['system_prompt'] ?? ''); ?></textarea>
                            <p class="description">Customize the AI system prompt used for generating social media strategies. Leave blank to use the default prompt.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
