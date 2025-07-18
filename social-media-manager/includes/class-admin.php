<?php
/**
 * Admin dashboard management class - Redesigned
 */

if (!defined('ABSPATH')) {
    exit;
}

class SMM_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_ajax_smm_filter_posts', array($this, 'ajax_filter_posts'));
        add_action('wp_ajax_smm_refresh_dashboard_data', array($this, 'ajax_refresh_dashboard_data'));
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
            <h1>Social Media Manager Dashboard</h1>
            <?php $this->render_dashboard(); ?>
        </div>
        
        <style>
        .smm-dashboard-overview {
            margin: 20px 0;
        }
        .smm-metrics-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .smm-metric-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .smm-metric-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }
        .smm-metric-value {
            font-size: 2em;
            font-weight: bold;
            color: #333;
        }
        .smm-api-status {
            margin-top: 10px;
        }
        .smm-api-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 5px 0;
        }
        .smm-api-indicator.connected {
            color: #28a745;
        }
        .smm-api-indicator.disconnected {
            color: #dc3545;
        }
        .smm-posts-management {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .smm-posts-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        .smm-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .smm-filters select {
            margin-left: 10px;
            padding: 5px;
        }
        .smm-posts-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .smm-posts-table th,
        .smm-posts-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .smm-posts-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .smm-bottom-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .smm-messages-section,
        .smm-invoices-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        .smm-messages-list,
        .smm-invoices-table {
            max-height: 300px;
            overflow-y: auto;
        }
        .smm-message-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        .smm-message-item:last-child {
            border-bottom: none;
        }
        .smm-message-unread {
            font-weight: bold;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Filter functionality for posts
            $('.smm-filters select').on('change', function() {
                var section = $(this).closest('.smm-posts-section');
                var status = section.find('h3').text().toLowerCase().includes('approved') ? 'approved' : 'pending';
                var dateFilter = section.find('[id*="date-filter"]').val();
                var platformFilter = section.find('[id*="platform-filter"]').val();
                var clientFilter = section.find('[id*="client-filter"]').val();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'smm_filter_posts',
                        status: status,
                        date_range: dateFilter,
                        platform: platformFilter,
                        client_id: clientFilter,
                        nonce: '<?php echo wp_create_nonce('smm_filter_posts'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            section.find('.smm-posts-table').html(response.data);
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    private function render_dashboard() {
        global $wpdb;
        
        // Get dashboard data
        $total_clients = $this->get_total_clients();
        $new_clients = $this->get_new_clients();
        $total_revenue = $this->get_total_revenue();
        $api_status = $this->get_api_status();
        $engagement_metrics = $this->get_engagement_metrics();
        $active_campaigns = $this->get_active_campaigns();
        $pending_posts = $this->get_pending_posts_count();
        
        ?>
        <div class="smm-dashboard-overview">
            <!-- Key Metrics Row -->
            <div class="smm-widget-row">
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-header">
                        <h3>Total Clients</h3>
                    </div>
                    <div class="smm-metric-card">
                        <div class="smm-metric-icon">👥</div>
                        <div class="smm-metric-info">
                            <div class="smm-metric-value large"><?php echo esc_html($total_clients); ?></div>
                            <div class="smm-metric-change">Active accounts</div>
                        </div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-header">
                        <h3>New Clients</h3>
                    </div>
                    <div class="smm-metric-card">
                        <div class="smm-metric-icon">✨</div>
                        <div class="smm-metric-info">
                            <div class="smm-metric-value large"><?php echo esc_html($new_clients); ?></div>
                            <div class="smm-metric-change">Last 30 days</div>
                        </div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-header">
                        <h3>Revenue</h3>
                    </div>
                    <div class="smm-metric-card">
                        <div class="smm-metric-icon">💰</div>
                        <div class="smm-metric-info">
                            <div class="smm-metric-value large">$<?php echo esc_html(number_format($total_revenue, 2)); ?></div>
                            <div class="smm-metric-change">Last 30 days</div>
                        </div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-quarter">
                    <div class="smm-widget-header">
                        <h3>API Status</h3>
                    </div>
                    <div class="smm-api-status">
                        <?php foreach ($api_status as $api => $status): ?>
                            <div class="smm-api-item">
                                <span class="smm-api-name"><?php echo esc_html(ucfirst($api)); ?></span>
                                <span class="smm-api-indicator <?php echo $status ? 'connected' : 'disconnected'; ?>">
                                    <?php echo $status ? '●' : '●'; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Secondary Metrics Row -->
            <div class="smm-widget-row">
                <div class="smm-widget smm-widget-third">
                    <div class="smm-widget-header">
                        <h3>Social Engagement</h3>
                    </div>
                    <div class="smm-engagement-metrics">
                        <div class="smm-metric-card">
                            <div class="smm-metric-info">
                                <h4>Avg. Engagement Rate</h4>
                                <div class="smm-metric-value"><?php echo esc_html($engagement_metrics['avg_rate']); ?>%</div>
                            </div>
                        </div>
                        <div class="smm-metric-card">
                            <div class="smm-metric-info">
                                <h4>Total Interactions</h4>
                                <div class="smm-metric-value"><?php echo esc_html(number_format($engagement_metrics['total_interactions'])); ?></div>
                            </div>
                        </div>
                        <div class="smm-engagement-chart">
                            <canvas id="engagementChart" width="300" height="150"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-third">
                    <div class="smm-widget-header">
                        <h3>Active Campaigns</h3>
                    </div>
                    <div class="smm-campaigns-overview">
                        <div class="smm-metric-card">
                            <div class="smm-metric-info">
                                <h4>Running Campaigns</h4>
                                <div class="smm-metric-value"><?php echo esc_html($active_campaigns['count']); ?></div>
                            </div>
                        </div>
                        <div class="smm-campaigns-list">
                            <?php if (!empty($active_campaigns['campaigns'])): ?>
                                <?php foreach (array_slice($active_campaigns['campaigns'], 0, 3) as $campaign): ?>
                                    <div class="smm-campaign-item">
                                        <span class="campaign-name"><?php echo esc_html($campaign->campaign_name); ?></span>
                                        <span class="campaign-platform"><?php echo esc_html(ucfirst($campaign->platform)); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-data">No active campaigns</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="smm-widget smm-widget-third">
                    <div class="smm-widget-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="smm-quick-actions">
                        <div class="smm-action-item">
                            <div class="smm-action-stat">
                                <span class="stat-number"><?php echo esc_html($pending_posts); ?></span>
                                <span class="stat-label">Pending Posts</span>
                            </div>
                            <button class="smm-btn smm-btn-sm" onclick="location.href='#pending-posts'">Review</button>
                        </div>
                        <div class="smm-action-item">
                            <button class="smm-btn smm-btn-primary smm-ask-ai">Ask AI Assistant</button>
                        </div>
                        <div class="smm-action-item">
                            <button class="smm-btn smm-btn-secondary" onclick="location.href='<?php echo admin_url('admin.php?page=smm-clients'); ?>'">Manage Clients</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Posts Management Row -->
            <div class="smm-posts-management">
                <div class="smm-posts-section">
                    <div class="smm-section-header">
                        <h3>Approved Posts</h3>
                        <div class="smm-filters">
                            <select id="approved-date-filter">
                                <option value="7">Last 7 days</option>
                                <option value="30" selected>Last 30 days</option>
                                <option value="90">Last 90 days</option>
                            </select>
                            <select id="approved-platform-filter">
                                <option value="">All Platforms</option>
                                <option value="facebook">Facebook</option>
                                <option value="instagram">Instagram</option>
                                <option value="twitter">Twitter</option>
                                <option value="linkedin">LinkedIn</option>
                            </select>
                            <select id="approved-client-filter">
                                <option value="">All Clients</option>
                                <?php echo $this->get_client_options(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="smm-posts-table" id="approved-posts-table">
                        <?php echo $this->render_posts_table('approved'); ?>
                    </div>
                </div>
                
                <div class="smm-posts-section">
                    <div class="smm-section-header">
                        <h3>Pending Posts</h3>
                        <div class="smm-filters">
                            <select id="pending-date-filter">
                                <option value="7">Last 7 days</option>
                                <option value="30" selected>Last 30 days</option>
                                <option value="90">Last 90 days</option>
                            </select>
                            <select id="pending-platform-filter">
                                <option value="">All Platforms</option>
                                <option value="facebook">Facebook</option>
                                <option value="instagram">Instagram</option>
                                <option value="twitter">Twitter</option>
                                <option value="linkedin">LinkedIn</option>
                            </select>
                            <select id="pending-client-filter">
                                <option value="">All Clients</option>
                                <?php echo $this->get_client_options(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="smm-posts-table" id="pending-posts-table">
                        <?php echo $this->render_posts_table('pending'); ?>
                    </div>
                </div>
            </div>
            
            <!-- Communication & Invoices Row -->
            <div class="smm-bottom-row">
                <div class="smm-messages-section">
                    <div class="smm-section-header">
                        <h3>Latest Messages</h3>
                    </div>
                    <div class="smm-messages-list">
                        <?php echo $this->render_latest_messages(); ?>
                    </div>
                </div>
                
                <div class="smm-invoices-section">
                    <div class="smm-section-header">
                        <h3>Latest Invoices</h3>
                    </div>
                    <div class="smm-invoices-table">
                        <?php echo $this->render_latest_invoices(); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function get_total_clients() {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_clients';
        return $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }
    
    private function get_new_clients() {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_clients';
        return $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    }
    
    private function get_total_revenue() {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_invoices';
        $result = $wpdb->get_var("SELECT SUM(amount) FROM $table WHERE status = 'paid' AND paid_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        return $result ? $result : 0;
    }
    
    private function get_api_status() {
        $settings = get_option('smm_settings', array());
        return array(
            'chatgpt' => !empty($settings['chatgpt_api_key']),
            'facebook' => !empty($settings['facebook_api_key']),
            'instagram' => !empty($settings['instagram_api_key']),
            'twitter' => !empty($settings['twitter_api_key']),
            'linkedin' => !empty($settings['linkedin_api_key'])
        );
    }
    
    private function get_engagement_metrics() {
        global $wpdb;
        $analytics_table = $wpdb->prefix . 'smm_analytics';
        
        // Get engagement data from last 30 days
        $engagement_data = $wpdb->get_results("
            SELECT metric_name, AVG(CAST(metric_value AS DECIMAL(10,2))) as avg_value, 
                   SUM(CAST(metric_value AS DECIMAL(10,2))) as total_value
            FROM $analytics_table 
            WHERE metric_name IN ('likes', 'comments', 'shares', 'engagement_rate') 
            AND date_recorded >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY metric_name
        ");
        
        $metrics = array(
            'avg_rate' => '0.0',
            'total_interactions' => 0
        );
        
        if ($engagement_data) {
            foreach ($engagement_data as $data) {
                if ($data->metric_name === 'engagement_rate') {
                    $metrics['avg_rate'] = number_format($data->avg_value, 1);
                } elseif (in_array($data->metric_name, ['likes', 'comments', 'shares'])) {
                    $metrics['total_interactions'] += intval($data->total_value);
                }
            }
        }
        
        return $metrics;
    }
    
    private function get_active_campaigns() {
        global $wpdb;
        $campaigns_table = $wpdb->prefix . 'smm_campaigns';
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $campaigns_table WHERE status = 'active'");
        $campaigns = $wpdb->get_results("
            SELECT campaign_name, platform 
            FROM $campaigns_table 
            WHERE status = 'active' 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        
        return array(
            'count' => intval($count),
            'campaigns' => $campaigns ?: array()
        );
    }
    
    private function get_pending_posts_count() {
        global $wpdb;
        $posts_table = $wpdb->prefix . 'smm_posts';
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $posts_table WHERE status = 'pending'");
        return intval($count);
    }
    
    private function get_client_options() {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_clients';
        $clients = $wpdb->get_results("SELECT id, company_name FROM $table ORDER BY company_name");
        
        $options = '';
        foreach ($clients as $client) {
            $options .= '<option value="' . esc_attr($client->id) . '">' . esc_html($client->company_name) . '</option>';
        }
        return $options;
    }
    
    private function render_posts_table($status, $date_range = 30, $platform = '', $client_id = '') {
        global $wpdb;
        $posts_table = $wpdb->prefix . 'smm_posts';
        $clients_table = $wpdb->prefix . 'smm_clients';
        
        $where_conditions = array("p.status = %s");
        $params = array($status);
        
        if ($date_range) {
            $where_conditions[] = "p.created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)";
            $params[] = $date_range;
        }
        
        if ($platform) {
            $where_conditions[] = "p.platform = %s";
            $params[] = $platform;
        }
        
        if ($client_id) {
            $where_conditions[] = "p.client_id = %d";
            $params[] = $client_id;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = "SELECT p.*, c.company_name 
                FROM $posts_table p 
                LEFT JOIN $clients_table c ON p.client_id = c.id 
                WHERE $where_clause 
                ORDER BY p.created_at DESC 
                LIMIT 10";
        
        $posts = $wpdb->get_results($wpdb->prepare($sql, $params));
        
        ob_start();
        ?>
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Platform</th>
                    <th>Content</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($posts): ?>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?php echo esc_html($post->company_name ?: 'Unknown'); ?></td>
                            <td><?php echo esc_html(ucfirst($post->platform)); ?></td>
                            <td><?php echo esc_html(wp_trim_words($post->post_content, 10)); ?></td>
                            <td><?php echo esc_html(date('M j, Y', strtotime($post->created_at))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No posts found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }
    
    private function render_latest_messages() {
        global $wpdb;
        $messages_table = $wpdb->prefix . 'smm_messages';
        $clients_table = $wpdb->prefix . 'smm_clients';
        
        $sql = "SELECT m.*, c.company_name 
                FROM $messages_table m 
                LEFT JOIN $clients_table c ON m.client_id = c.id 
                ORDER BY m.created_at DESC 
                LIMIT 10";
        
        $messages = $wpdb->get_results($sql);
        
        ob_start();
        if ($messages):
            foreach ($messages as $message):
                $unread_class = $message->is_read ? '' : 'smm-message-unread';
                ?>
                <div class="smm-message-item <?php echo $unread_class; ?>">
                    <div class="smm-message-content">
                        <strong><?php echo esc_html($message->company_name ?: 'Unknown Client'); ?></strong>
                        <p><?php echo esc_html(wp_trim_words($message->message_content, 15)); ?></p>
                    </div>
                    <div class="smm-message-date">
                        <?php echo esc_html(date('M j', strtotime($message->created_at))); ?>
                    </div>
                </div>
                <?php
            endforeach;
        else:
            echo '<p>No messages found</p>';
        endif;
        return ob_get_clean();
    }
    
    private function render_latest_invoices() {
        global $wpdb;
        $invoices_table = $wpdb->prefix . 'smm_invoices';
        $clients_table = $wpdb->prefix . 'smm_clients';
        
        $sql = "SELECT i.*, c.company_name 
                FROM $invoices_table i 
                LEFT JOIN $clients_table c ON i.client_id = c.id 
                ORDER BY i.created_at DESC 
                LIMIT 10";
        
        $invoices = $wpdb->get_results($sql);
        
        ob_start();
        ?>
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($invoices): ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?php echo esc_html($invoice->invoice_number); ?></td>
                            <td><?php echo esc_html($invoice->company_name ?: 'Unknown'); ?></td>
                            <td>$<?php echo esc_html(number_format($invoice->amount, 2)); ?></td>
                            <td><?php echo esc_html(ucfirst($invoice->status)); ?></td>
                            <td><?php echo esc_html(date('M j, Y', strtotime($invoice->created_at))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No invoices found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }
    
    public function ajax_filter_posts() {
        check_ajax_referer('smm_filter_posts', 'nonce');
        
        $status = sanitize_text_field($_POST['status']);
        $date_range = intval($_POST['date_range']);
        $platform = sanitize_text_field($_POST['platform']);
        $client_id = intval($_POST['client_id']);
        
        $html = $this->render_posts_table($status, $date_range, $platform, $client_id);
        
        wp_send_json_success($html);
    }
    
    public function ajax_refresh_dashboard_data() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        $data = array(
            'total_clients' => $this->get_total_clients(),
            'new_clients' => $this->get_new_clients(),
            'total_revenue' => $this->get_total_revenue(),
            'pending_posts' => $this->get_pending_posts_count()
        );
        
        wp_send_json_success($data);
    }
    
    public function clients_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_clients';

        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 10;
        $offset = ($paged - 1) * $per_page;

        $total_clients = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $clients = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset));
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
                                    <a href="<?php echo admin_url('admin.php?page=smm-clients&action=edit&id=' . intval($client->id)); ?>">Edit</a>
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
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
