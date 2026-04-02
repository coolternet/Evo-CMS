<?php
return [
    // Titles and descriptions
    'module_name' => 'Statistics',
    'module_description' => 'Advanced statistics module for EvoCMS',
    'dashboard_title' => 'Statistics Dashboard',
    'dashboard_subtitle' => 'Complete tracking of your website performance',
    
    // Metrics
    'visitors' => 'Visitors',
    'page_views' => 'Page Views',
    'unique_visitors' => 'Unique Visitors',
    'total_visits' => 'Total Visits',
    'bounce_rate' => 'Bounce Rate',
    'avg_time' => 'Average Time',
    
    // Periods
    'today' => 'Today',
    'yesterday' => 'Yesterday',
    'this_week' => 'This Week',
    'this_month' => 'This Month',
    'last_7_days' => 'Last 7 Days',
    'last_30_days' => 'Last 30 Days',
    
    // Pages and sections
    'pages_popular' => 'Most Popular Pages',
    'visitor_stats' => 'Visitor Statistics',
    'detailed_stats' => 'Detailed Statistics',
    'comparison' => 'Comparison with Yesterday',
    'global_summary' => 'Global Summary',
    'configuration' => 'Module Configuration',
    
    // Actions
    'export_csv' => 'Export CSV',
    'clear_data' => 'Clear Data',
    'refresh' => 'Refresh',
    
    // Messages
    'no_data_today' => 'No data available for today.',
    'no_page_data' => 'No page data available.',
    'no_comparison_data' => 'No comparison data available.',
    'data_cleared' => 'Statistics data cleared successfully!',
    'export_success' => 'CSV export generated successfully!',
    
    // Permissions
    'permission_view' => 'View public statistics',
    'permission_admin' => 'Access statistics administration',
    'permission_export' => 'Export statistics data',
    'permission_delete' => 'Delete statistics data',
    
    // Configuration
    'setting_track_bots' => 'Track robots and crawlers',
    'setting_track_user_agents' => 'Record User-Agents',
    'setting_retention_days' => 'Data retention period',
    'setting_auto_cleanup' => 'Automatic cleanup',
    'setting_privacy_mode' => 'Privacy mode',
    
    // Setting descriptions
    'setting_track_bots_desc' => 'Enable to include robot visits in statistics',
    'setting_track_user_agents_desc' => 'Stores information about browsers and operating systems',
    'setting_retention_days_desc' => 'Number of days to keep visit data',
    'setting_auto_cleanup_desc' => 'Automatically removes old data according to retention',
    'setting_privacy_mode_desc' => 'Anonymizes IP addresses to comply with GDPR',
    
    // Values
    'enabled' => 'Enabled',
    'disabled' => 'Disabled',
    'unknown' => 'Unknown',
    'page_unknown' => 'Unknown page',
    
    // Information
    'about_stats' => 'These statistics are updated in real-time and reflect the actual activity of our website. Data is anonymized to respect your privacy.',
    'tracking_period' => 'Tracking period',
    'pages_tracked' => 'Pages tracked',
    'evolution' => 'Evolution',
    
    // Confirmations
    'confirm_clear_data' => 'Are you sure you want to clear old data?',
    'confirm_export' => 'Do you want to export statistics data?',
    
    // Errors
    'access_denied' => 'Access denied to statistics.',
    'access_denied_admin' => 'Access denied to statistics administration.',
    'permission_required' => 'Permission required to perform this action.',
    
    // Charts
    'chart_weekly_activity' => 'Weekly Activity',
    'chart_visitors' => 'Visitors',
    'chart_page_views' => 'Page Views',
    
    // Tables
    'table_page' => 'Page',
    'table_title' => 'Title',
    'table_visits' => 'Visits',
    'table_unique_visits' => 'Unique Visits',
    'table_last_visit' => 'Last Visit',
    'table_metric' => 'Metric',
    'table_evolution' => 'Evolution',
    
    // Badges and statuses
    'badge_rank' => 'Rank',
    'badge_visits' => 'visits',
    'badge_unique' => 'unique',
    'badge_today' => 'Today',
    'badge_week' => 'This Week',
    'badge_month' => 'This Month',
    
    // Time
    'time_format' => 'm/d/Y H:i',
    'time_ago' => 'ago',
    'time_seconds' => 'seconds',
    'time_minutes' => 'minutes',
    'time_hours' => 'hours',
    'time_days' => 'days',
    
    // Special statistics
    'stats_home' => 'Home',
    'stats_login' => 'Login',
    'stats_register' => 'Register',
    'stats_forums' => 'Forums',
    'stats_gallery' => 'Gallery',
    'stats_downloads' => 'Downloads',
    
    // Notifications
    'notice_module_activated' => 'Statistics module activated successfully!',
    'notice_module_deactivated' => 'Statistics module deactivated!',
    'notice_data_updated' => 'Statistics data updated!',
    
    // Widget
    'widget_title' => 'Statistics',
    'widget_total_visits' => 'Total: {count} visits',
    'widget_visitors_today' => 'Today: {count} visitors',
    'widget_pages_today' => 'Pages viewed: {count}',
];
