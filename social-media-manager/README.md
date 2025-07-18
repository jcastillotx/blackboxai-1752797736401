# Social Media Manager Plugin

## Overview

The Social Media Manager plugin is a comprehensive WordPress plugin designed to help businesses manage their social media presence effectively. It includes AI-powered strategy generation, client intake forms, social media account management, content scheduling, analytics, messaging, and admin management features.

## Features

- Client Intake Form: Collect detailed information from clients to tailor social media strategies.
- Admin Dashboard: Manage clients, assign social media managers, view key metrics, campaigns, posts, messages, and invoices.
- Client Dashboard: Clients can view their social media strategy status, scheduled posts, analytics, messages, and settings.
- Role-Based Messaging: Secure private messaging between clients and their assigned social media managers, with admin oversight.
- AI Strategy Generation: Generate personalized social media strategies using ChatGPT integration.
- Social Media API Integration: Connect and post to Facebook, Instagram, Twitter, LinkedIn, and more.
- Timesheet and Invoice Management: Track work hours and manage invoices.
- Settings Management: Configure API keys, notifications, and integrations.

## Installation

1. Upload the `social-media-manager` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Upon activation, the plugin will create necessary database tables.
4. Configure API keys and settings via the admin settings page.

## User Roles

- **Administrator**: Full access to all plugin features, including assigning social media managers to clients.
- **Social Media Manager**: Manage clients assigned to them, communicate via private messaging, and oversee social media campaigns.
- **SMM Client**: Access their dashboard, view strategies, scheduled posts, analytics, and communicate with their assigned manager.

## Shortcodes

- `[smm_client_intake]`  
  Displays the client intake form for new clients to submit their information.

- `[smm_client_dashboard]`  
  Displays the client dashboard with social media strategy status, post calendar, messages, analytics, and settings.

- `[smm_dashboard]`  
  Displays the general dashboard interface (used for social media managers and admins).

- `[smm_profile]`  
  Displays the profile management form for users to update their information.

- `[smm_timesheet]`  
  Displays the timesheet interface for tracking work hours.

- `[smm_messaging]`  
  Displays the messaging interface for private communication.

- `[smm_reports]`  
  Displays reports and analytics.

- `[smm_settings]`  
  Displays the settings page for managing plugin configurations.

## Admin Management

- Admins can view all clients and assign social media managers to them via the Clients management page.
- Assignments control messaging permissions and client-manager relationships.

## Messaging Permissions

- Clients can only message their assigned social media manager.
- Social media managers can message any client assigned to them.
- Admins can message any user.

## Support

For support and inquiries, please contact Kre8ivTech, LLC at [https://www.kre8itech.com](https://www.kre8itech.com).

## License

This plugin is licensed under the GPL v2 or later.

---
