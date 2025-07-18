<?php
/**
 * ChatGPT Integration class for AI-powered social media strategy generation
 */

if (!defined('ABSPATH')) {
    exit;
}

class SMM_ChatGPT_Integration {
    
    private $api_key;
    private $api_endpoint = 'https://openrouter.ai/api/v1/chat/completions';
    private $model = 'openai/gpt-4o';
    
    public function __construct() {
        $settings = get_option('smm_settings', array());
        $this->api_key = $settings['chatgpt_api_key'] ?? '';
    }
    
    public function generate_strategy($intake_data) {
        if (empty($this->api_key)) {
            return array(
                'error' => 'ChatGPT API key not configured. Please check your settings.'
            );
        }
        
        $system_prompt = $this->get_system_prompt();
        $user_prompt = $this->format_intake_data($intake_data);
        
        $response = $this->make_api_request($system_prompt, $user_prompt);
        
        if (isset($response['error'])) {
            return $response;
        }
        
        return $this->parse_strategy_response($response);
    }
    
    private function get_system_prompt() {
        $settings = get_option('smm_settings', array());
        $custom_prompt = $settings['system_prompt'] ?? '';
        
        if (!empty($custom_prompt)) {
            return $custom_prompt;
        }
        
        // Default system prompt
        return "You are an expert social media strategist and marketing consultant with over 10 years of experience helping businesses grow their online presence. Your task is to create comprehensive, actionable social media strategies based on client intake information.

INSTRUCTIONS:
- Analyze the provided client information thoroughly
- Create a detailed, personalized social media strategy
- Provide specific, actionable recommendations
- Include content ideas, posting schedules, and growth tactics
- Consider the client's industry, target audience, goals, and budget
- Provide platform-specific recommendations
- Include measurable KPIs and success metrics
- Suggest content themes and campaign ideas
- Recommend tools and resources when appropriate

FORMAT YOUR RESPONSE AS A STRUCTURED STRATEGY DOCUMENT WITH THE FOLLOWING SECTIONS:

1. EXECUTIVE SUMMARY
2. SITUATION ANALYSIS
3. TARGET AUDIENCE PROFILE
4. PLATFORM STRATEGY
5. CONTENT STRATEGY
6. POSTING SCHEDULE
7. ENGAGEMENT TACTICS
8. PAID ADVERTISING RECOMMENDATIONS
9. KPIs AND METRICS
10. IMPLEMENTATION TIMELINE
11. BUDGET ALLOCATION
12. TOOLS AND RESOURCES
13. NEXT STEPS

Make your recommendations specific, actionable, and tailored to the client's unique situation. Use professional language but keep it accessible and easy to understand.";
    }
    
    private function format_intake_data($intake_data) {
        $formatted_data = "CLIENT INTAKE INFORMATION:\n\n";
        
        // Company Information
        $formatted_data .= "COMPANY INFORMATION:\n";
        $formatted_data .= "Company Name: " . ($intake_data['company_name'] ?? 'Not provided') . "\n";
        $formatted_data .= "Industry: " . ($intake_data['industry'] ?? 'Not provided') . "\n";
        $formatted_data .= "Company Description: " . ($intake_data['company_description'] ?? 'Not provided') . "\n";
        $formatted_data .= "Company Size: " . ($intake_data['company_size'] ?? 'Not provided') . "\n";
        $formatted_data .= "Website: " . ($intake_data['website_url'] ?? 'Not provided') . "\n\n";
        
        // Target Audience
        $formatted_data .= "TARGET AUDIENCE:\n";
        $formatted_data .= "Age Range: " . ($intake_data['target_age_range'] ?? 'Not provided') . "\n";
        $formatted_data .= "Gender: " . ($intake_data['target_gender'] ?? 'All genders') . "\n";
        $formatted_data .= "Location: " . ($intake_data['target_location'] ?? 'Not provided') . "\n";
        $formatted_data .= "Interests & Behaviors: " . ($intake_data['target_interests'] ?? 'Not provided') . "\n";
        $formatted_data .= "Customer Personas: " . ($intake_data['customer_personas'] ?? 'Not provided') . "\n\n";
        
        // Business Goals
        $formatted_data .= "BUSINESS GOALS:\n";
        if (!empty($intake_data['primary_goals']) && is_array($intake_data['primary_goals'])) {
            $formatted_data .= "Primary Goals: " . implode(', ', $intake_data['primary_goals']) . "\n";
        }
        $formatted_data .= "Success Metrics: " . ($intake_data['success_metrics'] ?? 'Not provided') . "\n";
        $formatted_data .= "Current Challenges: " . ($intake_data['current_challenges'] ?? 'Not provided') . "\n\n";
        
        // Current Social Media Presence
        $formatted_data .= "CURRENT SOCIAL MEDIA PRESENCE:\n";
        if (!empty($intake_data['current_platforms']) && is_array($intake_data['current_platforms'])) {
            $formatted_data .= "Current Platforms: " . implode(', ', $intake_data['current_platforms']) . "\n";
        }
        $formatted_data .= "Current Performance: " . ($intake_data['current_performance'] ?? 'Not provided') . "\n";
        $formatted_data .= "Posting Frequency: " . ($intake_data['posting_frequency'] ?? 'Not provided') . "\n\n";
        
        // Content Preferences
        $formatted_data .= "CONTENT PREFERENCES:\n";
        if (!empty($intake_data['content_types']) && is_array($intake_data['content_types'])) {
            $formatted_data .= "Preferred Content Types: " . implode(', ', $intake_data['content_types']) . "\n";
        }
        $formatted_data .= "Brand Voice: " . ($intake_data['brand_voice'] ?? 'Not provided') . "\n";
        $formatted_data .= "Content Language: " . ($intake_data['content_language'] ?? 'English') . "\n";
        $formatted_data .= "Content Themes: " . ($intake_data['content_themes'] ?? 'Not provided') . "\n\n";
        
        // Competition
        $formatted_data .= "COMPETITION:\n";
        $formatted_data .= "Main Competitors: " . ($intake_data['main_competitors'] ?? 'Not provided') . "\n";
        $formatted_data .= "Competitor Analysis: " . ($intake_data['competitor_analysis'] ?? 'Not provided') . "\n";
        $formatted_data .= "Unique Value Proposition: " . ($intake_data['unique_value_proposition'] ?? 'Not provided') . "\n\n";
        
        // Budget & Resources
        $formatted_data .= "BUDGET & RESOURCES:\n";
        $formatted_data .= "Monthly Budget: " . ($intake_data['monthly_budget'] ?? 'Not provided') . "\n";
        $formatted_data .= "Ad Budget: " . ($intake_data['ad_budget'] ?? 'Not provided') . "\n";
        $formatted_data .= "Internal Resources: " . ($intake_data['internal_resources'] ?? 'Not provided') . "\n\n";
        
        // Timeline
        $formatted_data .= "TIMELINE & EXPECTATIONS:\n";
        $formatted_data .= "Start Timeline: " . ($intake_data['start_timeline'] ?? 'Not provided') . "\n";
        $formatted_data .= "Campaign Duration: " . ($intake_data['campaign_duration'] ?? 'Not provided') . "\n";
        $formatted_data .= "Expected Results Timeline: " . ($intake_data['success_timeline'] ?? 'Not provided') . "\n\n";
        
        // Additional Information
        $formatted_data .= "ADDITIONAL INFORMATION:\n";
        $formatted_data .= "Brand Guidelines: " . ($intake_data['brand_guidelines'] ?? 'Not provided') . "\n";
        $formatted_data .= "Content Restrictions: " . ($intake_data['content_restrictions'] ?? 'None specified') . "\n";
        $formatted_data .= "Additional Notes: " . ($intake_data['additional_notes'] ?? 'None') . "\n";
        
        return $formatted_data;
    }
    
    private function make_api_request($system_prompt, $user_prompt) {
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key,
            'HTTP-Referer: ' . home_url(),
            'X-Title: Social Media Manager Plugin'
        );
        
        $data = array(
            'model' => $this->model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $system_prompt
                ),
                array(
                    'role' => 'user',
                    'content' => $user_prompt
                )
            ),
            'max_tokens' => 4000,
            'temperature' => 0.7,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            return array('error' => 'Network error: ' . $curl_error);
        }
        
        if ($http_code !== 200) {
            return array('error' => 'API request failed with status code: ' . $http_code);
        }
        
        $decoded_response = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array('error' => 'Invalid JSON response from API');
        }
        
        if (isset($decoded_response['error'])) {
            return array('error' => 'API Error: ' . $decoded_response['error']['message']);
        }
        
        if (!isset($decoded_response['choices'][0]['message']['content'])) {
            return array('error' => 'Unexpected API response format');
        }
        
        return $decoded_response['choices'][0]['message']['content'];
    }
    
    private function parse_strategy_response($response) {
        // Parse the AI response into structured sections
        $sections = array();
        $current_section = '';
        $current_content = '';
        
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Check if this line is a section header
            if (preg_match('/^\d+\.\s*([A-Z\s&]+)$/', $line, $matches)) {
                // Save previous section if exists
                if (!empty($current_section) && !empty($current_content)) {
                    $sections[$current_section] = trim($current_content);
                }
                
                // Start new section
                $current_section = strtolower(str_replace(' ', '_', trim($matches[1])));
                $current_content = '';
            } else {
                // Add content to current section
                if (!empty($line)) {
                    $current_content .= $line . "\n";
                }
            }
        }
        
        // Save the last section
        if (!empty($current_section) && !empty($current_content)) {
            $sections[$current_section] = trim($current_content);
        }
        
        // If no sections were parsed, return the full response
        if (empty($sections)) {
            $sections['full_strategy'] = $response;
        }
        
        return array(
            'success' => true,
            'strategy' => $sections,
            'full_response' => $response,
            'generated_at' => current_time('mysql')
        );
    }
    
    public function generate_content_ideas($client_data, $content_type = 'general', $count = 10) {
        if (empty($this->api_key)) {
            return array('error' => 'ChatGPT API key not configured.');
        }
        
        $system_prompt = "You are a creative social media content strategist. Generate engaging, platform-appropriate content ideas based on the client information provided. Focus on creating content that aligns with their brand voice, target audience, and business goals.";
        
        $user_prompt = "Based on this client information:\n\n";
        $user_prompt .= "Company: " . ($client_data['company_name'] ?? '') . "\n";
        $user_prompt .= "Industry: " . ($client_data['industry'] ?? '') . "\n";
        $user_prompt .= "Target Audience: " . ($client_data['target_audience'] ?? '') . "\n";
        $user_prompt .= "Brand Voice: " . ($client_data['brand_voice'] ?? '') . "\n";
        $user_prompt .= "Content Type Focus: " . $content_type . "\n\n";
        $user_prompt .= "Generate " . $count . " specific, actionable content ideas. For each idea, provide:\n";
        $user_prompt .= "1. Content title/topic\n";
        $user_prompt .= "2. Brief description\n";
        $user_prompt .= "3. Suggested platform(s)\n";
        $user_prompt .= "4. Content format (image, video, carousel, etc.)\n";
        $user_prompt .= "5. Key message/call-to-action\n\n";
        $user_prompt .= "Format as a numbered list with clear sections for each idea.";
        
        $response = $this->make_api_request($system_prompt, $user_prompt);
        
        if (isset($response['error'])) {
            return $response;
        }
        
        return array(
            'success' => true,
            'content_ideas' => $response,
            'generated_at' => current_time('mysql')
        );
    }
    
    public function generate_post_caption($post_data) {
        if (empty($this->api_key)) {
            return array('error' => 'ChatGPT API key not configured.');
        }
        
        $system_prompt = "You are a social media copywriter specializing in creating engaging, platform-optimized captions. Write captions that drive engagement, include relevant hashtags, and align with the brand voice.";
        
        $user_prompt = "Create a social media caption for:\n\n";
        $user_prompt .= "Platform: " . ($post_data['platform'] ?? 'Instagram') . "\n";
        $user_prompt .= "Content Topic: " . ($post_data['topic'] ?? '') . "\n";
        $user_prompt .= "Brand Voice: " . ($post_data['brand_voice'] ?? 'Professional') . "\n";
        $user_prompt .= "Target Audience: " . ($post_data['target_audience'] ?? '') . "\n";
        $user_prompt .= "Call-to-Action: " . ($post_data['cta'] ?? 'Engage with the post') . "\n";
        $user_prompt .= "Key Message: " . ($post_data['key_message'] ?? '') . "\n\n";
        $user_prompt .= "Include relevant hashtags and make it engaging for the target audience.";
        
        $response = $this->make_api_request($system_prompt, $user_prompt);
        
        if (isset($response['error'])) {
            return $response;
        }
        
        return array(
            'success' => true,
            'caption' => $response,
            'generated_at' => current_time('mysql')
        );
    }
    
    public function analyze_performance($analytics_data) {
        if (empty($this->api_key)) {
            return array('error' => 'ChatGPT API key not configured.');
        }
        
        $system_prompt = "You are a social media analytics expert. Analyze the provided performance data and provide actionable insights, recommendations, and strategies for improvement.";
        
        $user_prompt = "Analyze this social media performance data and provide insights:\n\n";
        $user_prompt .= json_encode($analytics_data, JSON_PRETTY_PRINT);
        $user_prompt .= "\n\nProvide:\n";
        $user_prompt .= "1. Key performance insights\n";
        $user_prompt .= "2. Areas of strength\n";
        $user_prompt .= "3. Areas for improvement\n";
        $user_prompt .= "4. Specific recommendations\n";
        $user_prompt .= "5. Content strategy adjustments\n";
        $user_prompt .= "6. Posting time/frequency recommendations\n";
        
        $response = $this->make_api_request($system_prompt, $user_prompt);
        
        if (isset($response['error'])) {
            return $response;
        }
        
        return array(
            'success' => true,
            'analysis' => $response,
            'generated_at' => current_time('mysql')
        );
    }
}
