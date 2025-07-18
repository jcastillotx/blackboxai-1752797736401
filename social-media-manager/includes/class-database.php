<?php
/**
 * Database management class - Complete with invoices table
 */

if (!defined('ABSPATH')) {
    exit;
}

class SMM_Database {
    
    public function __construct() {
        // Constructor
    }
    
    public function create_tables() {
        global $wpdb;
        
        if (!isset($wpdb)) {
            error_log('SMM Plugin Error: global $wpdb not available.');
            return false;
        }
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Clients table
        $clients_table = $wpdb->prefix . 'smm_clients';
        $clients_sql = "CREATE TABLE $clients_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            assigned_manager_id bigint(20) DEFAULT NULL,
            company_name varchar(255) NOT NULL,
            industry varchar(100),
            target_audience text,
            business_goals text,
            current_social_presence text,
            budget_range varchar(50),
            preferred_platforms text,
            content_preferences text,
            brand_voice varchar(100),
            competitors text,
            intake_data longtext,
            strategy_generated longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        // Campaigns table
        $campaigns_table = $wpdb->prefix . 'smm_campaigns';
        $campaigns_sql = "CREATE TABLE $campaigns_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            client_id mediumint(9) NOT NULL,
            campaign_name varchar(255) NOT NULL,
            platform varchar(50) NOT NULL,
            campaign_type varchar(50),
            start_date date,
            end_date date,
            budget decimal(10,2),
            status varchar(20) DEFAULT 'active',
            objectives text,
            target_metrics text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY client_id (client_id)
        ) $charset_collate;";
        
        // Posts table
        $posts_table = $wpdb->prefix . 'smm_posts';
        $posts_sql = "CREATE TABLE $posts_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            client_id mediumint(9) NOT NULL,
            campaign_id mediumint(9),
            platform varchar(50) NOT NULL,
            post_content text NOT NULL,
            post_image varchar(255),
            scheduled_time datetime,
            published_time datetime,
            status varchar(20) DEFAULT 'draft',
            engagement_data text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY client_id (client_id),
            KEY campaign_id (campaign_id)
        ) $charset_collate;";
        
        // Analytics table
        $analytics_table = $wpdb->prefix . 'smm_analytics';
        $analytics_sql = "CREATE TABLE $analytics_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            client_id mediumint(9) NOT NULL,
            platform varchar(50) NOT NULL,
            metric_name varchar(100) NOT NULL,
            metric_value varchar(255),
            date_recorded date,
            additional_data text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY client_id (client_id),
            KEY platform (platform),
            KEY date_recorded (date_recorded)
        ) $charset_collate;";
        
        // Timesheets table
        $timesheets_table = $wpdb->prefix . 'smm_timesheets';
        $timesheets_sql = "CREATE TABLE $timesheets_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            client_id mediumint(9) NOT NULL,
            task_description text NOT NULL,
            hours_worked decimal(4,2) NOT NULL,
            work_date date NOT NULL,
            hourly_rate decimal(8,2),
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY client_id (client_id)
        ) $charset_collate;";
        
        // Messages table
        $messages_table = $wpdb->prefix . 'smm_messages';
        $messages_sql = "CREATE TABLE $messages_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            sender_id bigint(20) NOT NULL,
            recipient_id bigint(20) NOT NULL,
            client_id mediumint(9),
            subject varchar(255),
            message_content text NOT NULL,
            is_read tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY sender_id (sender_id),
            KEY recipient_id (recipient_id),
            KEY client_id (client_id)
        ) $charset_collate;";
        
        // Invoices table
        $invoices_table = $wpdb->prefix . 'smm_invoices';
        $invoices_sql = "CREATE TABLE $invoices_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            client_id mediumint(9) NOT NULL,
            invoice_number varchar(50) NOT NULL,
            amount decimal(10,2) NOT NULL,
            status varchar(20) DEFAULT 'pending',
            due_date date,
            paid_date date,
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY client_id (client_id),
            KEY status (status),
            UNIQUE KEY invoice_number (invoice_number)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create tables with error logging
        $tables = array(
            'clients' => $clients_sql,
            'campaigns' => $campaigns_sql,
            'posts' => $posts_sql,
            'analytics' => $analytics_sql,
            'timesheets' => $timesheets_sql,
            'messages' => $messages_sql,
            'invoices' => $invoices_sql
        );
        
        foreach ($tables as $table_name => $sql) {
            $result = dbDelta($sql);
            if (empty($result)) {
                error_log("SMM Plugin Error: Failed to create {$table_name} table");
            } else {
                error_log("SMM Plugin: Successfully created/updated {$table_name} table");
            }
        }
        
        return true;
    }
    
    public function get_client_data($client_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_clients';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $client_id));
    }
    
    public function save_client_data($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_clients';
        
        return $wpdb->insert($table, $data);
    }

    // Get client ID by user ID
    public function get_client_id_by_user_id($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_clients';
        return $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE user_id = %d", $user_id));
    }

    // Get messages between two users
    public function get_messages_between_users($user1_id, $user2_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_messages';
        $sql = $wpdb->prepare(
            "SELECT * FROM $table WHERE (sender_id = %d AND recipient_id = %d) OR (sender_id = %d AND recipient_id = %d) ORDER BY created_at ASC",
            $user1_id, $user2_id, $user2_id, $user1_id
        );
        return $wpdb->get_results($sql);
    }

    // Get messages for social media manager (all messages with clients)
    public function get_messages_for_manager($manager_user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_messages';
        $clients_table = $wpdb->prefix . 'smm_clients';

        $sql = $wpdb->prepare(
            "SELECT m.* FROM $table m
             LEFT JOIN $clients_table c ON m.sender_id = c.user_id OR m.recipient_id = c.user_id
             WHERE c.assigned_manager_id = %d
             ORDER BY m.created_at DESC",
            $manager_user_id
        );
        return $wpdb->get_results($sql);
    }

    // Get all messages (for admin)
    public function get_all_messages() {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_messages';
        $sql = "SELECT * FROM $table ORDER BY created_at DESC";
        return $wpdb->get_results($sql);
    }

    // Get unread messages between two users
    public function get_unread_messages_between_users($user1_id, $user2_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_messages';
        $sql = $wpdb->prepare(
            "SELECT * FROM $table WHERE is_read = 0 AND ((sender_id = %d AND recipient_id = %d) OR (sender_id = %d AND recipient_id = %d)) ORDER BY created_at ASC",
            $user1_id, $user2_id, $user2_id, $user1_id
        );
        return $wpdb->get_results($sql);
    }

    // Get unread messages for social media manager
    public function get_unread_messages_for_manager($manager_user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_messages';
        $clients_table = $wpdb->prefix . 'smm_clients';

        $sql = $wpdb->prepare(
            "SELECT m.* FROM $table m
             LEFT JOIN $clients_table c ON m.sender_id = c.user_id OR m.recipient_id = c.user_id
             WHERE c.assigned_manager_id = %d AND m.is_read = 0
             ORDER BY m.created_at DESC",
            $manager_user_id
        );
        return $wpdb->get_results($sql);
    }

    // Get all unread messages (for admin)
    public function get_all_unread_messages() {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_messages';
        $sql = "SELECT * FROM $table WHERE is_read = 0 ORDER BY created_at DESC";
        return $wpdb->get_results($sql);
    }

    // Get assigned social media manager ID for a client
    public function get_assigned_manager_id($client_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_clients';
        return $wpdb->get_var($wpdb->prepare("SELECT assigned_manager_id FROM $table WHERE id = %d", $client_id));
    }

    // Set assigned social media manager ID for a client
    public function set_assigned_manager_id($client_id, $manager_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_clients';
        return $wpdb->update($table, array('assigned_manager_id' => $manager_id), array('id' => $client_id));
    }
    
    public function get_analytics_data($client_id, $platform = null, $date_range = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_analytics';
        
        $where_clause = "WHERE client_id = %d AND date_recorded >= DATE_SUB(CURDATE(), INTERVAL %d DAY)";
        $params = array($client_id, $date_range);
        
        if ($platform) {
            $where_clause .= " AND platform = %s";
            $params[] = $platform;
        }
        
        $sql = "SELECT * FROM $table $where_clause ORDER BY date_recorded DESC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }
    
    public function save_timesheet_entry($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_timesheets';
        
        return $wpdb->insert($table, $data);
    }
    
    public function get_messages($user_id, $client_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_messages';
        
        $where_clause = "WHERE (sender_id = %d OR recipient_id = %d)";
        $params = array($user_id, $user_id);
        
        if ($client_id) {
            $where_clause .= " AND client_id = %d";
            $params[] = $client_id;
        }
        
        $sql = "SELECT * FROM $table $where_clause ORDER BY created_at DESC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }
    
    public function save_invoice($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_invoices';
        
        return $wpdb->insert($table, $data);
    }
    
    public function get_invoices($client_id = null, $status = null, $limit = 10) {
        global $wpdb;
        $invoices_table = $wpdb->prefix . 'smm_invoices';
        $clients_table = $wpdb->prefix . 'smm_clients';
        
        $where_conditions = array();
        $params = array();
        
        if ($client_id) {
            $where_conditions[] = "i.client_id = %d";
            $params[] = $client_id;
        }
        
        if ($status) {
            $where_conditions[] = "i.status = %s";
            $params[] = $status;
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $sql = "SELECT i.*, c.company_name 
                FROM $invoices_table i 
                LEFT JOIN $clients_table c ON i.client_id = c.id 
                $where_clause 
                ORDER BY i.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT %d";
            $params[] = $limit;
        }
        
        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($sql, $params));
        } else {
            return $wpdb->get_results($sql);
        }
    }

    // Insert a new message into the messages table
    public function insert_message($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'smm_messages';

        $inserted = $wpdb->insert($table, $data);

        return $inserted;
    }
}
