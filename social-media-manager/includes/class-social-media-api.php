<?php
/**
 * Social Media API Integration class
 */

if (!defined('ABSPATH')) {
    exit;
}

class SMM_Social_Media_API {
    
    private $settings;
    
    public function __construct() {
        $this->settings = get_option('smm_settings', array());
        add_action('wp_ajax_smm_connect_platform', array($this, 'connect_platform'));
        add_action('wp_ajax_smm_post_to_platform', array($this, 'post_to_platform'));
        add_action('wp_ajax_smm_get_analytics', array($this, 'get_platform_analytics'));
    }
    
    /**
     * Connect to a social media platform
     */
    public function connect_platform() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        $platform = sanitize_text_field($_POST['platform']);
        $auth_data = sanitize_text_field($_POST['auth_data']);
        
        switch ($platform) {
            case 'facebook':
                $result = $this->connect_facebook($auth_data);
                break;
            case 'instagram':
                $result = $this->connect_instagram($auth_data);
                break;
            case 'twitter':
                $result = $this->connect_twitter($auth_data);
                break;
            case 'linkedin':
                $result = $this->connect_linkedin($auth_data);
                break;
            default:
                $result = array('error' => 'Unsupported platform');
        }
        
        if (isset($result['error'])) {
            wp_send_json_error($result);
        } else {
            wp_send_json_success($result);
        }
    }
    
    /**
     * Post content to social media platforms
     */
    public function post_to_platform() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        $platform = sanitize_text_field($_POST['platform']);
        $content = sanitize_textarea_field($_POST['content']);
        $image_url = sanitize_url($_POST['image_url'] ?? '');
        $scheduled_time = sanitize_text_field($_POST['scheduled_time'] ?? '');
        
        $post_data = array(
            'content' => $content,
            'image_url' => $image_url,
            'scheduled_time' => $scheduled_time
        );
        
        switch ($platform) {
            case 'facebook':
                $result = $this->post_to_facebook($post_data);
                break;
            case 'instagram':
                $result = $this->post_to_instagram($post_data);
                break;
            case 'twitter':
                $result = $this->post_to_twitter($post_data);
                break;
            case 'linkedin':
                $result = $this->post_to_linkedin($post_data);
                break;
            default:
                $result = array('error' => 'Unsupported platform');
        }
        
        if (isset($result['error'])) {
            wp_send_json_error($result);
        } else {
            // Save post data to database
            $this->save_post_data($platform, $post_data, $result);
            wp_send_json_success($result);
        }
    }
    
    /**
     * Get analytics from social media platforms
     */
    public function get_platform_analytics() {
        check_ajax_referer('smm_nonce', 'nonce');
        
        $platform = sanitize_text_field($_POST['platform']);
        $date_range = sanitize_text_field($_POST['date_range'] ?? '30');
        
        switch ($platform) {
            case 'facebook':
                $result = $this->get_facebook_analytics($date_range);
                break;
            case 'instagram':
                $result = $this->get_instagram_analytics($date_range);
                break;
            case 'twitter':
                $result = $this->get_twitter_analytics($date_range);
                break;
            case 'linkedin':
                $result = $this->get_linkedin_analytics($date_range);
                break;
            default:
                $result = array('error' => 'Unsupported platform');
        }
        
        if (isset($result['error'])) {
            wp_send_json_error($result);
        } else {
            wp_send_json_success($result);
        }
    }
    
    /**
     * Facebook API Integration
     */
    private function connect_facebook($auth_data) {
        $access_token = $this->settings['facebook_api_key'] ?? '';
        
        if (empty($access_token)) {
            return array('error' => 'Facebook API key not configured');
        }
        
        // Facebook Graph API connection logic
        $url = 'https://graph.facebook.com/me?access_token=' . $access_token;
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return array('error' => 'Failed to connect to Facebook API');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return array('error' => 'Facebook API Error: ' . $data['error']['message']);
        }
        
        return array(
            'success' => true,
            'platform' => 'facebook',
            'user_info' => $data
        );
    }
    
    private function post_to_facebook($post_data) {
        $access_token = $this->settings['facebook_api_key'] ?? '';
        
        if (empty($access_token)) {
            return array('error' => 'Facebook API key not configured');
        }
        
        $url = 'https://graph.facebook.com/me/feed';
        $params = array(
            'message' => $post_data['content'],
            'access_token' => $access_token
        );
        
        if (!empty($post_data['image_url'])) {
            $params['link'] = $post_data['image_url'];
        }
        
        $response = wp_remote_post($url, array(
            'body' => $params
        ));
        
        if (is_wp_error($response)) {
            return array('error' => 'Failed to post to Facebook');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return array('error' => 'Facebook API Error: ' . $data['error']['message']);
        }
        
        return array(
            'success' => true,
            'platform' => 'facebook',
            'post_id' => $data['id'] ?? null
        );
    }
    
    private function get_facebook_analytics($date_range) {
        $access_token = $this->settings['facebook_api_key'] ?? '';
        
        if (empty($access_token)) {
            return array('error' => 'Facebook API key not configured');
        }
        
        // Facebook Insights API call
        $url = 'https://graph.facebook.com/me/insights?access_token=' . $access_token;
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return array('error' => 'Failed to get Facebook analytics');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return array(
            'success' => true,
            'platform' => 'facebook',
            'analytics' => $data
        );
    }
    
    /**
     * Instagram API Integration
     */
    private function connect_instagram($auth_data) {
        $access_token = $this->settings['instagram_api_key'] ?? '';
        
        if (empty($access_token)) {
            return array('error' => 'Instagram API key not configured');
        }
        
        // Instagram Basic Display API
        $url = 'https://graph.instagram.com/me?fields=id,username&access_token=' . $access_token;
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return array('error' => 'Failed to connect to Instagram API');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return array('error' => 'Instagram API Error: ' . $data['error']['message']);
        }
        
        return array(
            'success' => true,
            'platform' => 'instagram',
            'user_info' => $data
        );
    }
    
    private function post_to_instagram($post_data) {
        $access_token = $this->settings['instagram_api_key'] ?? '';
        
        if (empty($access_token)) {
            return array('error' => 'Instagram API key not configured');
        }
        
        // Instagram requires image URL for posts
        if (empty($post_data['image_url'])) {
            return array('error' => 'Instagram posts require an image');
        }
        
        // Create media container
        $url = 'https://graph.facebook.com/v18.0/me/media';
        $params = array(
            'image_url' => $post_data['image_url'],
            'caption' => $post_data['content'],
            'access_token' => $access_token
        );
        
        $response = wp_remote_post($url, array(
            'body' => $params
        ));
        
        if (is_wp_error($response)) {
            return array('error' => 'Failed to create Instagram media container');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return array('error' => 'Instagram API Error: ' . $data['error']['message']);
        }
        
        // Publish the media
        $container_id = $data['id'];
        $publish_url = 'https://graph.facebook.com/v18.0/me/media_publish';
        $publish_params = array(
            'creation_id' => $container_id,
            'access_token' => $access_token
        );
        
        $publish_response = wp_remote_post($publish_url, array(
            'body' => $publish_params
        ));
        
        if (is_wp_error($publish_response)) {
            return array('error' => 'Failed to publish Instagram post');
        }
        
        $publish_body = wp_remote_retrieve_body($publish_response);
        $publish_data = json_decode($publish_body, true);
        
        return array(
            'success' => true,
            'platform' => 'instagram',
            'post_id' => $publish_data['id'] ?? null
        );
    }
    
    private function get_instagram_analytics($date_range) {
        $access_token = $this->settings['instagram_api_key'] ?? '';
        
        if (empty($access_token)) {
            return array('error' => 'Instagram API key not configured');
        }
        
        // Instagram Insights API
        $url = 'https://graph.instagram.com/me/insights?metric=impressions,reach,profile_views&period=day&access_token=' . $access_token;
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return array('error' => 'Failed to get Instagram analytics');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return array(
            'success' => true,
            'platform' => 'instagram',
            'analytics' => $data
        );
    }
    
    /**
     * Twitter API Integration
     */
    private function connect_twitter($auth_data) {
        $api_key = $this->settings['twitter_api_key'] ?? '';
        
        if (empty($api_key)) {
            return array('error' => 'Twitter API key not configured');
        }
        
        // Twitter API v2 connection
        $url = 'https://api.twitter.com/2/users/me';
        $headers = array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        );
        
        $response = wp_remote_get($url, array(
            'headers' => $headers
        ));
        
        if (is_wp_error($response)) {
            return array('error' => 'Failed to connect to Twitter API');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['errors'])) {
            return array('error' => 'Twitter API Error: ' . $data['errors'][0]['message']);
        }
        
        return array(
            'success' => true,
            'platform' => 'twitter',
            'user_info' => $data
        );
    }
    
    private function post_to_twitter($post_data) {
        $api_key = $this->settings['twitter_api_key'] ?? '';
        
        if (empty($api_key)) {
            return array('error' => 'Twitter API key not configured');
        }
        
        $url = 'https://api.twitter.com/2/tweets';
        $headers = array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        );
        
        $tweet_data = array(
            'text' => $post_data['content']
        );
        
        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => json_encode($tweet_data)
        ));
        
        if (is_wp_error($response)) {
            return array('error' => 'Failed to post to Twitter');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['errors'])) {
            return array('error' => 'Twitter API Error: ' . $data['errors'][0]['message']);
        }
        
        return array(
            'success' => true,
            'platform' => 'twitter',
            'post_id' => $data['data']['id'] ?? null
        );
    }
    
    private function get_twitter_analytics($date_range) {
        // Twitter analytics would require additional API endpoints
        return array(
            'success' => true,
            'platform' => 'twitter',
            'analytics' => array(
                'message' => 'Twitter analytics integration coming soon'
            )
        );
    }
    
    /**
     * LinkedIn API Integration
     */
    private function connect_linkedin($auth_data) {
        $access_token = $this->settings['linkedin_api_key'] ?? '';
        
        if (empty($access_token)) {
            return array('error' => 'LinkedIn API key not configured');
        }
        
        $url = 'https://api.linkedin.com/v2/people/~';
        $headers = array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        );
        
        $response = wp_remote_get($url, array(
            'headers' => $headers
        ));
        
        if (is_wp_error($response)) {
            return array('error' => 'Failed to connect to LinkedIn API');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return array('error' => 'LinkedIn API Error: ' . $data['message']);
        }
        
        return array(
            'success' => true,
            'platform' => 'linkedin',
            'user_info' => $data
        );
    }
    
    private function post_to_linkedin($post_data) {
        $access_token = $this->settings['linkedin_api_key'] ?? '';
        
        if (empty($access_token)) {
            return array('error' => 'LinkedIn API key not configured');
        }
        
        $url = 'https://api.linkedin.com/v2/ugcPosts';
        $headers = array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        );
        
        $post_body = array(
            'author' => 'urn:li:person:' . $this->get_linkedin_person_id(),
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => array(
                'com.linkedin.ugc.ShareContent' => array(
                    'shareCommentary' => array(
                        'text' => $post_data['content']
                    ),
                    'shareMediaCategory' => 'NONE'
                )
            ),
            'visibility' => array(
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'
            )
        );
        
        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => json_encode($post_body)
        ));
        
        if (is_wp_error($response)) {
            return array('error' => 'Failed to post to LinkedIn');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return array('error' => 'LinkedIn API Error: ' . $data['message']);
        }
        
        return array(
            'success' => true,
            'platform' => 'linkedin',
            'post_id' => $data['id'] ?? null
        );
    }
    
    private function get_linkedin_analytics($date_range) {
        // LinkedIn analytics implementation
        return array(
            'success' => true,
            'platform' => 'linkedin',
            'analytics' => array(
                'message' => 'LinkedIn analytics integration coming soon'
            )
        );
    }
    
    /**
     * Helper methods
     */
    private function save_post_data($platform, $post_data, $api_result) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'smm_posts';
        $data = array(
            'client_id' => get_current_user_id(), // This should be the actual client ID
            'platform' => $platform,
            'post_content' => $post_data['content'],
            'post_image' => $post_data['image_url'] ?? '',
            'published_time' => current_time('mysql'),
            'status' => 'published',
            'engagement_data' => json_encode($api_result)
        );
        
        $wpdb->insert($table, $data);
    }
    
    private function get_linkedin_person_id() {
        // This would need to be retrieved from the LinkedIn profile API
        // For now, return a placeholder
        return 'PLACEHOLDER_PERSON_ID';
    }
    
    /**
     * Get supported platforms
     */
    public function get_supported_platforms() {
        return array(
            'facebook' => array(
                'name' => 'Facebook',
                'icon' => 'fab fa-facebook',
                'color' => '#1877F2',
                'features' => array('post', 'schedule', 'analytics')
            ),
            'instagram' => array(
                'name' => 'Instagram',
                'icon' => 'fab fa-instagram',
                'color' => '#E4405F',
                'features' => array('post', 'schedule', 'analytics')
            ),
            'twitter' => array(
                'name' => 'Twitter',
                'icon' => 'fab fa-twitter',
                'color' => '#1DA1F2',
                'features' => array('post', 'schedule')
            ),
            'linkedin' => array(
                'name' => 'LinkedIn',
                'icon' => 'fab fa-linkedin',
                'color' => '#0A66C2',
                'features' => array('post', 'schedule')
            )
        );
    }
    
    /**
     * Check platform connection status
     */
    public function check_platform_connections() {
        $platforms = $this->get_supported_platforms();
        $connections = array();
        
        foreach ($platforms as $platform => $info) {
            $api_key = $this->settings[$platform . '_api_key'] ?? '';
            $connections[$platform] = array(
                'connected' => !empty($api_key),
                'name' => $info['name'],
                'icon' => $info['icon'],
                'color' => $info['color']
            );
        }
        
        return $connections;
    }
}
