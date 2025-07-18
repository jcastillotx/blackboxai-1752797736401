/**
 * Social Media Manager - Admin JavaScript
 */

jQuery(document).ready(function($) {
    
    // Tab switching functionality
    $('.nav-tabs a').on('click', function(e) {
        e.preventDefault();
        
        var targetTab = $(this).data('tab');
        
        // Remove active class from all tabs and content
        $('.nav-tabs li').removeClass('active');
        $('.tab-content').removeClass('active');
        
        // Add active class to clicked tab and corresponding content
        $(this).parent().addClass('active');
        $('#' + targetTab).addClass('active');
    });
    
    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
    
    // Initialize new dashboard widgets
    initializeDashboardWidgets();
    
    // AJAX form submissions
    setupAjaxForms();
    
    // Client management functions
    setupClientManagement();
    
    // Campaign management functions
    setupCampaignManagement();
    
    // Settings management
    setupSettingsManagement();
});

/**
 * Initialize dashboard charts
 */
function initializeCharts() {
    // Rankings Chart
    var rankingsCtx = document.getElementById('rankingsChart');
    if (rankingsCtx) {
        new Chart(rankingsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Rankings',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Sessions Chart (Doughnut)
    var sessionsCtx = document.getElementById('sessionsChart');
    if (sessionsCtx) {
        new Chart(sessionsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Referral', 'Organic Search', 'Direct', 'Other', 'Paid Search', 'Social', 'Display', 'Email'],
                datasets: [{
                    data: [602, 573, 564, 410, 212, 178, 126, 122],
                    backgroundColor: [
                        '#3498db',
                        '#27ae60',
                        '#e74c3c',
                        '#95a5a6',
                        '#f39c12',
                        '#9b59b6',
                        '#1abc9c',
                        '#34495e'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '60%'
            }
        });
    }
    
    // Conversions Chart
    var conversionsCtx = document.getElementById('conversionsChart');
    if (conversionsCtx) {
        new Chart(conversionsCtx, {
            type: 'bar',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    label: 'Conversions',
                    data: [1200, 1900, 800, 1500],
                    backgroundColor: '#27ae60'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Rating Chart (Doughnut)
    var ratingCtx = document.getElementById('ratingChart');
    if (ratingCtx) {
        new Chart(ratingCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [4.75, 0.25],
                    backgroundColor: ['#27ae60', '#ecf0f1'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '80%'
            }
        });
    }
    
    // Leads Chart
    var leadsCtx = document.getElementById('leadsChart');
    if (leadsCtx) {
        new Chart(leadsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    data: [85000, 87000, 90000, 92000, 94000, 95293],
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
                },
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        display: false
                    }
                },
                elements: {
                    point: {
                        radius: 0
                    }
                }
            }
        });
    }
    
    // Impressions Chart
    var impressionsCtx = document.getElementById('impressionsChart');
    if (impressionsCtx) {
        new Chart(impressionsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    data: [200000, 220000, 240000, 250000, 260000, 262000],
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39, 174, 96, 0.1)',
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
                },
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        display: false
                    }
                },
                elements: {
                    point: {
                        radius: 0
                    }
                }
            }
        });
    }
    
    // Cost Chart
    var costCtx = document.getElementById('costChart');
    if (costCtx) {
        new Chart(costCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    data: [1500, 1800, 1600, 1700],
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
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
                },
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        display: false
                    }
                },
                elements: {
                    point: {
                        radius: 0
                    }
                }
            }
        });
    }
    
    // CTR Chart
    var ctrCtx = document.getElementById('ctrChart');
    if (ctrCtx) {
        new Chart(ctrCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    data: [3.1, 3.3, 3.0, 3.2],
                    borderColor: '#f39c12',
                    backgroundColor: 'rgba(243, 156, 18, 0.1)',
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
                },
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        display: false
                    }
                },
                elements: {
                    point: {
                        radius: 0
                    }
                }
            }
        });
    }
    
    // Engagement Chart
    var engagementCtx = document.getElementById('engagementChart');
    if (engagementCtx) {
        new Chart(engagementCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Engagement',
                    data: [30, 40, 35, 50, 49, 60, 70],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
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
                },
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
 * Initialize dashboard widgets functionality
 */
function initializeDashboardWidgets() {
    // Widget hover effects
    jQuery('.smm-widget').hover(
        function() {
            jQuery(this).addClass('widget-hover');
        },
        function() {
            jQuery(this).removeClass('widget-hover');
        }
    );
    
    // Quick action buttons
    jQuery('.smm-action-item button').on('click', function(e) {
        var $btn = jQuery(this);
        var originalText = $btn.text();
        
        if ($btn.hasClass('smm-ask-ai')) {
            // AI functionality is handled separately
            return;
        }
        
        // Add loading state for other buttons
        $btn.prop('disabled', true).text('Loading...');
        
        setTimeout(function() {
            $btn.prop('disabled', false).text(originalText);
        }, 1000);
    });
    
    // Auto-refresh widgets every 5 minutes
    setInterval(function() {
        refreshWidgetData();
    }, 300000); // 5 minutes
}

/**
 * Refresh widget data via AJAX
 */
function refreshWidgetData() {
    jQuery.ajax({
        url: smm_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'smm_refresh_dashboard_data',
            nonce: smm_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                updateWidgetValues(response.data);
            }
        },
        error: function() {
            console.log('Failed to refresh dashboard data');
        }
    });
}

/**
 * Update widget values with new data
 */
function updateWidgetValues(data) {
    if (data.total_clients) {
        jQuery('.smm-widget:contains("Total Clients") .smm-metric-value').text(data.total_clients);
    }
    if (data.new_clients) {
        jQuery('.smm-widget:contains("New Clients") .smm-metric-value').text(data.new_clients);
    }
    if (data.total_revenue) {
        jQuery('.smm-widget:contains("Revenue") .smm-metric-value').text('$' + parseFloat(data.total_revenue).toFixed(2));
    }
    if (data.pending_posts) {
        jQuery('.stat-number').text(data.pending_posts);
    }
}

/**
 * Setup AJAX form submissions
 */
function setupAjaxForms() {
    jQuery(document).on('submit', '.smm-ajax-form', function(e) {
        e.preventDefault();
        
        var $form = jQuery(this);
        var $submitBtn = $form.find('button[type="submit"]');
        var originalText = $submitBtn.text();
        
        // Show loading state
        $submitBtn.prop('disabled', true).text('Processing...');
        
        jQuery.ajax({
            url: smm_ajax.ajax_url,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    showNotification('Success: ' + response.data.message, 'success');
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    }
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
}

/**
 * Setup client management functions
 */
function setupClientManagement() {
    // Delete client confirmation
    jQuery(document).on('click', '.delete-client', function(e) {
        if (!confirm('Are you sure you want to delete this client? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
    
    // Generate strategy for client
    jQuery(document).on('click', '.generate-strategy', function(e) {
        e.preventDefault();
        
        var clientId = jQuery(this).data('client-id');
        var $btn = jQuery(this);
        var originalText = $btn.text();
        
        $btn.prop('disabled', true).text('Generating...');
        
        jQuery.ajax({
            url: smm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'smm_generate_strategy',
                client_id: clientId,
                nonce: smm_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showStrategyModal(response.data.strategy);
                } else {
                    showNotification('Error generating strategy: ' + response.data.message, 'error');
                }
            },
            error: function() {
                showNotification('An error occurred while generating strategy.', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });
}

/**
 * Setup campaign management functions
 */
function setupCampaignManagement() {
    // Campaign status toggle
    jQuery(document).on('change', '.campaign-status-toggle', function() {
        var campaignId = jQuery(this).data('campaign-id');
        var status = jQuery(this).is(':checked') ? 'active' : 'paused';
        
        jQuery.ajax({
            url: smm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'smm_update_campaign_status',
                campaign_id: campaignId,
                status: status,
                nonce: smm_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Campaign status updated successfully.', 'success');
                } else {
                    showNotification('Error updating campaign status.', 'error');
                }
            }
        });
    });
}

/**
 * Setup settings management
 */
function setupSettingsManagement() {
    // Test API connection
    jQuery(document).on('click', '.test-api-connection', function(e) {
        e.preventDefault();
        
        var platform = jQuery(this).data('platform');
        var $btn = jQuery(this);
        var originalText = $btn.text();
        
        $btn.prop('disabled', true).text('Testing...');
        
        jQuery.ajax({
            url: smm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'smm_test_api_connection',
                platform: platform,
                nonce: smm_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('API connection successful!', 'success');
                } else {
                    showNotification('API connection failed: ' + response.data.message, 'error');
                }
            },
            error: function() {
                showNotification('An error occurred while testing API connection.', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });
}

/**
 * Show notification message
 */
function showNotification(message, type) {
    var notificationClass = type === 'success' ? 'notice-success' : 'notice-error';
    var $notification = jQuery('<div class="notice ' + notificationClass + ' is-dismissible"><p>' + message + '</p></div>');
    
    jQuery('.wrap').prepend($notification);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $notification.fadeOut();
    }, 5000);
}

/**
 * Show strategy modal
 */
function showStrategyModal(strategy) {
    var modalHtml = '<div class="smm-modal-overlay">' +
        '<div class="smm-modal">' +
        '<div class="smm-modal-header">' +
        '<h2>Generated Social Media Strategy</h2>' +
        '<button class="smm-modal-close">&times;</button>' +
        '</div>' +
        '<div class="smm-modal-content">' +
        '<div class="smm-strategy-content">' + strategy.full_response + '</div>' +
        '</div>' +
        '<div class="smm-modal-footer">' +
        '<button class="smm-btn smm-btn-secondary smm-modal-close">Close</button>' +
        '<button class="smm-btn smm-btn-primary save-strategy">Save Strategy</button>' +
        '</div>' +
        '</div>' +
        '</div>';
    
    jQuery('body').append(modalHtml);
    
    // Close modal handlers
    jQuery(document).on('click', '.smm-modal-close, .smm-modal-overlay', function(e) {
        if (e.target === this) {
            jQuery('.smm-modal-overlay').remove();
        }
    });
    
    // Save strategy handler
    jQuery(document).on('click', '.save-strategy', function() {
        // Implementation for saving strategy
        showNotification('Strategy saved successfully!', 'success');
        jQuery('.smm-modal-overlay').remove();
    });
}

/**
 * Ask AI functionality
 */
jQuery(document).on('click', '.smm-ask-ai', function(e) {
    e.preventDefault();
    
    var question = prompt('What would you like to ask the AI about your social media strategy?');
    if (!question) return;
    
    var $btn = jQuery(this);
    var originalText = $btn.text();
    
    $btn.prop('disabled', true).text('Asking AI...');
    
    jQuery.ajax({
        url: smm_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'smm_ask_ai',
            question: question,
            nonce: smm_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                alert('AI Response: ' + response.data.answer);
            } else {
                showNotification('Error: ' + response.data.message, 'error');
            }
        },
        error: function() {
            showNotification('An error occurred while asking AI.', 'error');
        },
        complete: function() {
            $btn.prop('disabled', false).text(originalText);
        }
    });
});
