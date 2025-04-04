<?php

// Load Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/*
 * Guide for configuring Laravel Herd for multi-tenancy
 */

// Get base domain from config
$baseDomain = config('app.domain');

echo "=== Laravel Herd Multi-tenancy Configuration Guide ===\n\n";
echo "Your base domain is: $baseDomain\n\n";

echo "To configure Laravel Herd for multi-tenancy, follow these steps:\n\n";

echo "1. Open Laravel Herd from your system tray\n";
echo "2. Right-click on your site ('$baseDomain')\n";
echo "3. Select 'Edit nginx Configuration'\n";
echo "4. Add the following server block to enable wildcard subdomains:\n\n";

$nginxConfig = <<<CONFIG
server {
    listen 80;
    server_name ~^(?<subdomain>.+).$baseDomain\$;
    
    root /path/to/your/project/public;
    
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
 
    index index.php;
 
    charset utf-8;
 
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
 
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
 
    error_page 404 /index.php;
 
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
 
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
CONFIG;

echo $nginxConfig;
echo "\n\n";

echo "5. Replace '/path/to/your/project/public' with the actual path to your project's public folder\n";
echo "6. Save the configuration and restart Herd\n\n";

echo "After configuring Herd, run the add-tenant-host.php script to add tenant domains to your hosts file.\n";
echo "You'll need to run the generated .bat file as Administrator.\n\n";

echo "If you have any issues, you may need to:\n";
echo "- Flush your DNS cache: 'ipconfig /flushdns'\n";
echo "- Restart your browser\n";
echo "- Ensure Herd's Nginx service is running\n"; 