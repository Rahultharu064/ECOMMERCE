<?php
/**
 * PharmaCare Email Configuration
 * 
 * Store this file outside your web root (e.g., in /etc/pharmacare/mail_config.php)
 * Set file permissions to 640 (rw-r-----)
 */

return [
    // SMTP Server Configuration
    'smtp' => [
        'host' => 'smtp.elasticemail.com', // Recommended SMTP services for Nepal:
                                            // - smtp.elasticemail.com (Free tier available)
                                            // - smtp.zoho.com.np (Zoho Mail Nepal)
                                            // - smtp.hostinger.com (If using Hostinger)
        'port' => 587,                      // 587 for TLS, 465 for SSL
        'secure' => 'tls',                  // 'tls' or 'ssl'
        'auth' => true,                      // Enable authentication
        'username' => 'notifications@pharmacare.com.np',
        'password' => 'your_secure_password_here', // Use app-specific password if available
        'timeout' => 30                      // Connection timeout in seconds
    ],

    // Email Address Configuration
    'addresses' => [
        'from' => [
            'email' => 'notifications@pharmacare.com.np',
            'name' => 'PharmaCare Itahari'
        ],
        'reply_to' => [
            'email' => 'support@pharmacare.com.np',
            'name' => 'PharmaCare Support'
        ],
        'admin' => [
            'email' => 'admin@pharmacare.com.np',
            'name' => 'Pharmacy Administrator'
        ],
        'cc' => [
            [
                'email' => 'manager@pharmacare.com.np',
                'name' => 'Pharmacy Manager'
            ],
            [
                'email' => 'sales@pharmacare.com.np',
                'name' => 'Sales Department'
            ]
        ],
        'bcc' => [
            'archive@pharmacare.com.np' // For keeping records
        ]
    ],

    // Email Content Settings
    'content' => [
        'charset' => 'UTF-8',               // For Nepali character support
        'encoding' => 'base64',              // 'base64', '7bit', 'quoted-printable'
        'word_wrap' => 70,                   // Character wrap length
        'priority' => 3                      // 1 = High, 3 = Normal, 5 = Low
    ],

    // Error Handling
    'error_logging' => [
        'enabled' => true,
        'path' => '/var/log/pharmacare/mail_errors.log', // Outside web root
        'level' => 'error'                   // 'error', 'debug', 'info'
    ],

    // DKIM Configuration (Recommended for email deliverability)
    'dkim' => [
        'enabled' => true,
        'domain' => 'pharmacare.com.np',
        'selector' => 'pharmacare',          // DNS TXT record selector
        'private_key' => '/etc/ssl/pharmacare/dkim.private.key', // Path to private key
        'passphrase' => ''                   // Key passphrase if used
    ],

    // Rate Limiting (Prevent abuse)
    'rate_limiting' => [
        'enabled' => true,
        'max_emails_per_hour' => 50,         // Maximum emails per hour
        'max_recipients_per_email' => 5      // Maximum recipients per email
    ],

    // Debugging
    'debug' => [
        'enabled' => false,                  // Only enable during development
        'level' => 2                         // 0 = off, 1 = client, 2 = client+server
    ]
];

/**
 * Security Recommendations:
 * 1. Never commit this file to version control
 * 2. Store outside web root directory
 * 3. Use environment variables for sensitive data
 * 4. Regularly rotate SMTP passwords
 * 5. Monitor error logs
 */