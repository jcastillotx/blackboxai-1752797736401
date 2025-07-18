/**
 * Social Media Manager - Frontend JavaScript
 */

jQuery(document).ready(function($) {
    
    // Tab switching functionality
    $('.smm-nav-tabs a, .smm-tabs-nav a').on('click', function(e) {
        e.preventDefault();
        
        var targetTab = $(this).attr('href').substring(1);
        
        // Remove active class from all tabs and content
        $(this).closest('ul').find('li').removeClass('active');
        $('.smm-tab-content').removeClass('active');
        
        // Add active class to clicked tab and corresponding content
        $(this).parent().addClass('active');
        $('#' + targetTab).addClass('active');
    });
    
    // Initialize frontend charts
    if (typeof Chart !== 'undefined') {
        initializeFrontendCharts();
    }
    
    // Setup client intake form
    setupClientIntakeForm();
    
    // Setup timesheet functionality
    setupTimesheetFunctionality();
    
    // Setup messaging functionality
    setupMessagingFunctionality();
    
    // Setup settings functionality
    setupSettingsFunctionality();

    // Load client dashboard data
    loadClientDashboardData();
});

/**
 * Initialize frontend charts
 */
function initializeFrontendCharts() {
    // Posts Performance Chart
    var postsCtx = document.getElementById('postsChart');
    if (postsCtx) {
        new Chart(postsCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Engagement',
                    data: [65, 59, 80, 81, 56, 55, 40],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    // Platform Distribution Chart
    var platformCtx = document.getElementById('platformChart');
    if (platformCtx) {
        new Chart(platformCtx, {
            type: 'doughnut',
            data: {
                labels: ['Facebook', 'Instagram', 'Twitter', 'LinkedIn'],
                datasets: [{
                    data: [40, 30, 20, 10],
                    backgroundColor: ['#3b5998', '#e4405f', '#1da1f2', '#0077b5']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    // Client Analytics Chart
    var clientAnalyticsCtx = document.getElementById('clientAnalyticsChart');
    if (clientAnalyticsCtx) {
        new Chart(clientAnalyticsCtx, {
            type: 'bar',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    label: 'Engagement',
                    data: [120, 190, 300, 250],
                    backgroundColor: '#27ae60'
                }, {
                    label: 'Reach',
                    data: [200, 300, 400, 350],
                    backgroundColor: '#3498db'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Performance Chart
    var performanceCtx = document.getElementById('performanceChart');
    if (performanceCtx) {
        new Chart(performanceCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Impressions',
                    data: [1000, 1200, 1100, 1300, 1250, 1400],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Engagement',
                    data: [50, 60, 55, 65, 62, 70],
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39, 174, 96, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

/**
 * Load client dashboard data and render UI components
 */
function loadClientDashboardData() {
    loadPostCalendar();
    loadPendingPosts();
    loadUnreadMessages();
    loadPrivateMessages();
    loadClientSettings();
}

/**
 * Load and render post content calendar
 */
function loadPostCalendar() {
    $.ajax({
        url: smm_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'smm_get_post_calendar',
            nonce: smm_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                renderPostCalendar(response.data.posts);
            } else {
                $('#smm-post-calendar').html('<p>Error loading calendar.</p>');
            }
        },
        error: function() {
            $('#smm-post-calendar').html('<p>Error loading calendar.</p>');
        }
    });
}

/**
 * Render post content calendar (simple list for demo)
 */
function renderPostCalendar(posts) {
    if (!posts || posts.length === 0) {
        $('#smm-post-calendar').html('<p>No scheduled posts found.</p>');
        return;
    }
    var html = '<ul class="smm-post-calendar-list">';
    posts.forEach(function(post) {
        html += '<li><strong>' + post.platform + '</strong>: ' + post.post_content + ' (Scheduled: ' + post.scheduled_time + ')</li>';
    });
    html += '</ul>';
    $('#smm-post-calendar').html(html);
}

/**
 * Load and render pending posts table
 */
function loadPendingPosts() {
    $.ajax({
        url: smm_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'smm_get_pending_posts',
            nonce: smm_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                renderPendingPostsTable(response.data.pending_posts);
            } else {
                $('#smm-pending-posts-table').html('<p>Error loading pending posts.</p>');
            }
        },
        error: function() {
            $('#smm-pending-posts-table').html('<p>Error loading pending posts.</p>');
        }
    });
}

/**
 * Render pending posts table
 */
function renderPendingPostsTable(posts) {
    if (!posts || posts.length === 0) {
        $('#smm-pending-posts-table').html('<p>No pending posts found.</p>');
        return;
    }
    var html = '<table class="smm-table"><thead><tr><th>Platform</th><th>Content</th><th>Scheduled Time</th><th>Actions</th></tr></thead><tbody>';
    posts.forEach(function(post) {
        html += '<tr>';
        html += '<td>' + post.platform + '</td>';
        html += '<td>' + post.post_content + '</td>';
        html += '<td>' + post.scheduled_time + '</td>';
        html += '<td><button class="smm-btn smm-btn-sm">View</button> <button class="smm-btn smm-btn-sm">Edit</button></td>';
        html += '</tr>';
    });
    html += '</tbody></table>';
    $('#smm-pending-posts-table').html(html);
}

/**
 * Load and render unread messages table
 */
function loadUnreadMessages() {
    $.ajax({
        url: smm_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'smm_get_unread_messages',
            nonce: smm_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                renderUnreadMessagesTable(response.data.unread_messages);
            } else {
                $('#smm-unread-messages-table').html('<p>Error loading unread messages.</p>');
            }
        },
        error: function() {
            $('#smm-unread-messages-table').html('<p>Error loading unread messages.</p>');
        }
    });
}

/**
 * Render unread messages table
 */
function renderUnreadMessagesTable(messages) {
    if (!messages || messages.length === 0) {
        $('#smm-unread-messages-table').html('<p>No unread messages found.</p>');
        return;
    }
    var html = '<table class="smm-table"><thead><tr><th>From</th><th>Subject</th><th>Date</th></tr></thead><tbody>';
    messages.forEach(function(message) {
        html += '<tr>';
        html += '<td>' + message.sender_name + '</td>';
        html += '<td>' + message.subject + '</td>';
        html += '<td>' + message.created_at + '</td>';
        html += '</tr>';
    });
    html += '</tbody></table>';
    $('#smm-unread-messages-table').html(html);
}

/**
 * Load and render private messages section
 */
function loadPrivateMessages() {
    $.ajax({
        url: smm_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'smm_get_private_messages',
            nonce: smm_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                renderPrivateMessages(response.data.messages);
            } else {
                $('#smm-private-messages-section').html('<p>Error loading messages.</p>');
            }
        },
        error: function() {
            $('#smm-private-messages-section').html('<p>Error loading messages.</p>');
        }
    });
}

/**
 * Render private messages
 */
function renderPrivateMessages(messages) {
    if (!messages || messages.length === 0) {
        $('#smm-private-messages-section').html('<p>No messages found.</p>');
        return;
    }
    var html = '<div class="smm-messages-list">';
    messages.forEach(function(message) {
        html += '<div class="smm-message-item">';
        html += '<strong>' + message.sender_name + '</strong><br>';
        html += '<p>' + message.message_content + '</p>';
        html += '<small>' + message.created_at + '</small>';
        html += '</div>';
    });
    html += '</div>';
    $('#smm-private-messages-section').html(html);
}

/**
 * Load and render client settings form
 */
function loadClientSettings() {
    $.ajax({
        url: smm_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'smm_get_client_settings',
            nonce: smm_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                renderClientSettingsForm(response.data.settings);
            } else {
                $('#smm-client-settings-section').html('<p>Error loading settings.</p>');
            }
        },
        error: function() {
            $('#smm-client-settings-section').html('<p>Error loading settings.</p>');
        }
    });
}

/**
 * Render client settings form
 */
function renderClientSettingsForm(settings) {
    var email = settings.email || '';
    var html = '<form id="smm-client-settings-form">';
    html += '<div class="smm-form-group">';
    html += '<label for="client-email">Email</label>';
    html += '<input type="email" id="client-email" name="email" value="' + email + '">';
    html += '</div>';
    html += '<div class="smm-form-group">';
    html += '<label for="client-password">Password</label>';
    html += '<input type="password" id="client-password" name="password" placeholder="Enter new password">';
    html += '</div>';
    html += '<button type="submit" class="smm-btn smm-btn-primary">Save Settings</button>';
    html += '</form>';
    $('#smm-client-settings-section').html(html);

    $('#smm-client-settings-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        formData += '&action=smm_save_client_settings&nonce=' + smm_ajax.nonce;
        $.ajax({
            url: smm_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showNotification('Settings saved successfully!', 'success');
                } else {
                    showNotification('Error saving settings.', 'error');
                }
            },
            error: function() {
                showNotification('Error saving settings.', 'error');
            }
        });
    });
}

/**
 * Setup client intake form
 */
function setupClientIntakeForm() {
    // Form validation
    $('#smm-intake-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        var originalText = $submitBtn.text();
        
        // Basic validation
        var isValid = validateIntakeForm($form);
        if (!isValid) {
            return;
        }
        
        // Show loading state
        $submitBtn.prop('disabled', true).text('Generating Strategy...');
        
        // Collect form data
        var formData = new FormData(this);
        formData.append('action', 'smm_submit_intake');
        
        $.ajax({
            url: smm_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Hide form and show strategy
                    $form.parent().hide();
                    displayGeneratedStrategy(response.data.strategy);
                } else {
                    showNotification('Error: ' + response.data.message, 'error');
                }
            },
            error: function() {
                showNotification('An error occurred. Please try again.', 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Dynamic form interactions
    setupFormInteractions();
}

/**
 * Validate intake form
 */
function validateIntakeForm($form) {
    var isValid = true;
    var requiredFields = $form.find('[required]');
    
    requiredFields.each(function() {
        var $field = $(this);
        var value = $field.val();
        
        if (!value || value.trim() === '') {
            $field.addClass('error');
            isValid = false;
        } else {
            $field.removeClass('error');
        }
    });
    
    // Check checkbox groups
    var checkboxGroups = ['primary_goals[]', 'current_platforms[]', 'content_types[]'];
    checkboxGroups.forEach(function(groupName) {
        var $group = $form.find('input[name="' + groupName + '"]');
        var checkedCount = $group.filter(':checked').length;
        
        if (checkedCount === 0) {
            $group.closest('.smm-checkbox-group').addClass('error');
            isValid = false;
        } else {
            $group.closest('.smm-checkbox-group').removeClass('error');
        }
    });
    
    if (!isValid) {
        showNotification('Please fill in all required fields.', 'error');
        $('html, body').animate({
            scrollTop: $form.find('.error').first().offset().top - 100
        }, 500);
    }
    
    return isValid;
}

/**
 * Setup form interactions
 */
function setupFormInteractions() {
    // Industry-specific suggestions
    $('#industry').on('change', function() {
        var industry = $(this).val();
        updateIndustrySuggestions(industry);
    });
    
    // Platform-specific content type suggestions
    $('input[name="current_platforms[]"]').on('change', function() {
        updateContentTypeSuggestions();
    });
    
    // Budget range warnings
    $('#monthly_budget').on('change', function() {
        var budget = $(this).val();
        showBudgetGuidance(budget);
    });
}

/**
 * Update industry-specific suggestions
 */
function updateIndustrySuggestions(industry) {
    var suggestions = {
        'technology': {
            'target_interests': 'Tech enthusiasts, early adopters, software developers, IT professionals',
            'content_themes': 'Product updates, tech tutorials, industry insights, innovation showcases'
        },
        'healthcare': {
            'target_interests': 'Health-conscious individuals, patients, medical professionals, wellness seekers',
            'content_themes': 'Health tips, patient testimonials, medical insights, wellness content'
        },
        'retail': {
            'target_interests': 'Shoppers, deal seekers, fashion enthusiasts, lifestyle consumers',
            'content_themes': 'Product showcases, sales promotions, customer reviews, lifestyle content'
        }
    };
    
    if (suggestions[industry]) {
        var suggestion = suggestions[industry];
        
        if ($('#target_interests').val() === '') {
            $('#target_interests').attr('placeholder', suggestion.target_interests);
        }
        
        if ($('#content_themes').val() === '') {
            $('#content_themes').attr('placeholder', suggestion.content_themes);
        }
    }
}

/**
 * Update content type suggestions based on platforms
 */
function updateContentTypeSuggestions() {
    var selectedPlatforms = [];
    $('input[name="current_platforms[]"]:checked').each(function() {
        selectedPlatforms.push($(this).val());
    });
    
    var suggestions = [];
    
    if (selectedPlatforms.includes('instagram')) {
        suggestions.push('Visual content works great on Instagram');
    }
    if (selectedPlatforms.includes('linkedin')) {
        suggestions.push('Professional content performs well on LinkedIn');
    }
    if (selectedPlatforms.includes('tiktok')) {
        suggestions.push('Short-form video content is essential for TikTok');
    }
    
    if (suggestions.length > 0) {
        showPlatformTips(suggestions);
    }
}

/**
 * Show budget guidance
 */
function showBudgetGuidance(budget) {
    var guidance = '';
    
    switch (budget) {
        case 'under_500':
            guidance = 'Focus on organic content and community engagement with this budget.';
            break;
        case '500_1000':
            guidance = 'You can run small paid campaigns and invest in content creation tools.';
            break;
        case '1000_2500':
            guidance = 'Good budget for regular paid advertising and professional content creation.';
            break;
        case '2500_5000':
            guidance = 'Excellent budget for comprehensive campaigns across multiple platforms.';
            break;
        case '5000_10000':
            guidance = 'Premium budget allowing for advanced targeting and high-quality content.';
            break;
        case 'over_10000':
            guidance = 'Enterprise-level budget for comprehensive social media marketing.';
            break;
    }
    
    if (guidance) {
        showBudgetTip(guidance);
    }
}

/**
 * Display generated strategy
 */
function displayGeneratedStrategy(strategy) {
    var $resultDiv = $('#smm-strategy-result');
    var $contentDiv = $resultDiv.find('.smm-strategy-content');
    
    if (strategy.success && strategy.strategy) {
        var strategyHtml = '<div class="smm-strategy-sections">';
        
        if (typeof strategy.strategy === 'object') {
            // Display structured strategy sections
            Object.keys(strategy.strategy).forEach(function(sectionKey) {
                var sectionTitle = sectionKey.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                strategyHtml += '<div class="smm-strategy-section">';
                strategyHtml += '<h3>' + sectionTitle + '</h3>';
                strategyHtml += '<div class="smm-strategy-text">' + strategy.strategy[sectionKey] + '</div>';
                strategyHtml += '</div>';
            });
        } else {
            // Display full response
            strategyHtml += '<div class="smm-strategy-section">';
            strategyHtml += '<div class="smm-strategy-text">' + strategy.full_response + '</div>';
            strategyHtml += '</div>';
        }
        
        strategyHtml += '</div>';
        
        // Add action buttons
        strategyHtml += '<div class="smm-strategy-actions">';
        strategyHtml += '<button class="smm-btn smm-btn-primary download-strategy">Download PDF</button>';
        strategyHtml += '<button class="smm-btn smm-btn-secondary email-strategy">Email Strategy</button>';
        strategyHtml += '<button class="smm-btn smm-btn-secondary start-over">Start Over</button>';
        strategyHtml += '</div>';
        
        $contentDiv.html(strategyHtml);
        $resultDiv.show();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $resultDiv.offset().top - 50
        }, 500);
        
    } else {
        showNotification('Error generating strategy. Please try again.', 'error');
    }
}

/**
 * Setup timesheet functionality
 */
function setupTimesheetFunctionality() {
    // Add time entry
    $('#add-time-entry').on('click', function() {
        $('#timesheet-form').slideDown();
    });
    
    // Cancel time entry
    $('#cancel-entry').on('click', function() {
        $('#timesheet-form').slideUp();
        $('.smm-time-entry-form')[0].reset();
    });
    
    // Submit time entry
    $('.smm-time-entry-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var formData = $form.serialize();
        formData += '&action=smm_save_timesheet&nonce=' + smm_ajax.nonce;
        
        $.ajax({
            url: smm_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showNotification('Time entry saved successfully!', 'success');
                    $form[0].reset();
                    $('#timesheet-form').slideUp();
                    loadTimesheetEntries();
                } else {
                    showNotification('Error saving time entry.', 'error');
                }
            }
        });
    });
    
    // Load existing timesheet entries
    loadTimesheetEntries();
}

/**
 * Load timesheet entries
 */
function loadTimesheetEntries() {
    $.ajax({
        url: smm_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'smm_get_timesheet_entries',
            nonce: smm_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                displayTimesheetEntries(response.data.entries);
            }
        }
    });
}

/**
 * Display timesheet entries
 */
function displayTimesheetEntries(entries) {
    var $tbody = $('#timesheet-entries');
    var html = '';
    
    if (entries && entries.length > 0) {
        entries.forEach(function(entry) {
            html += '<tr>';
            html += '<td>' + entry.work_date + '</td>';
            html += '<td>' + entry.hours_worked + '</td>';
            html += '<td>' + entry.task_description + '</td>';
            html += '<td><span class="status-' + entry.status + '">' + entry.status + '</span></td>';
            html += '<td><button class="smm-btn smm-btn-sm edit-entry" data-id="' + entry.id + '">Edit</button></td>';
            html += '</tr>';
        });
    } else {
        html = '<tr><td colspan="5">No time entries found.</td></tr>';
    }
    
    $tbody.html(html);
}

/**
 * Setup messaging functionality
 */
function setupMessagingFunctionality() {
    // Send message
    $('.smm-send-message-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var message = $form.find('textarea[name="message_content"]').val();
        
        if (!message.trim()) {
            showNotification('Please enter a message.', 'error');
            return;
        }
        
        var formData = $form.serialize();
        formData += '&action=smm_send_message&nonce=' + smm_ajax.nonce;
        
        $.ajax({
            url: smm_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $form.find('textarea').val('');
                    loadMessages();
                } else {
                    showNotification('Error sending message.', 'error');
                }
            }
        });
    });
    
    // Load messages
    loadMessages();
}

/**
 * Load messages
 */
function loadMessages() {
    $.ajax({
        url: smm_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'smm_get_messages',
            nonce: smm_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                displayMessages(response.data.messages);
            }
        }
    });
}

/**
 * Display messages
 */
function displayMessages(messages) {
    var $messagesDiv = $('.smm-messages');
    var html = '';
    
    if (messages && messages.length > 0) {
        messages.forEach(function(message) {
            html += '<div class="smm-message">';
            html += '<div class="smm-message-header">';
            html += '<strong>' + message.sender_name + '</strong>';
            html += '<span class="smm-message-time">' + message.created_at + '</span>';
            html += '</div>';
            html += '<div class="smm-message-content">' + message.message_content + '</div>';
            html += '</div>';
        });
    } else {
        html = '<p>No messages found.</p>';
    }
    
    $messagesDiv.html(html);
}

/**
 * Setup settings functionality
 */
function setupSettingsFunctionality() {
    // Save settings
    $('.smm-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var formData = $form.serialize();
        formData += '&action=smm_save_settings&nonce=' + smm_ajax.nonce;
        
        $.ajax({
            url: smm_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showNotification('Settings saved successfully!', 'success');
                } else {
                    showNotification('Error saving settings.', 'error');
                }
            }
        });
    });
}

/**
 * Utility functions
 */
function showNotification(message, type) {
    var notificationClass = type === 'success' ? 'smm-notification-success' : 'smm-notification-error';
    var $notification = $('<div class="smm-notification ' + notificationClass + '">' + message + '</div>');
    
    $('body').append($notification);
    
    setTimeout(function() {
        $notification.addClass('show');
    }, 100);
    
    setTimeout(function() {
        $notification.removeClass('show');
        setTimeout(function() {
            $notification.remove();
        }, 300);
    }, 5000);
}

function showPlatformTips(tips) {
    var tipsHtml = '<div class="smm-platform-tips"><h4>Platform Tips:</h4><ul>';
    tips.forEach(function(tip) {
        tipsHtml += '<li>' + tip + '</li>';
    });
    tipsHtml += '</ul></div>';
    
    $('.smm-content-preferences').append(tipsHtml);
}

function showBudgetTip(tip) {
    var $budgetField = $('#monthly_budget');
    var $existingTip = $budgetField.next('.smm-budget-tip');
    
    if ($existingTip.length) {
        $existingTip.text(tip);
    } else {
        $budgetField.after('<div class="smm-budget-tip">' + tip + '</div>');
    }
}

// Strategy action handlers
$(document).on('click', '.download-strategy', function() {
    // Implementation for downloading strategy as PDF
    showNotification('PDF download feature coming soon!', 'info');
});

$(document).on('click', '.email-strategy', function() {
    var email = prompt('Enter email address to send strategy:');
    if (email) {
        $.ajax({
            url: smm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'smm_email_strategy',
                email: email,
                nonce: smm_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Strategy emailed successfully!', 'success');
                } else {
                    showNotification('Error emailing strategy.', 'error');
                }
            }
        });
    }
});

$(document).on('click', '.start-over', function() {
    if (confirm('Are you sure you want to start over? This will clear the current strategy.')) {
        location.reload();
    }
});
