#!/usr/bin/env php
<?php

$action = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'help';

if (!in_array($action, array('show', 'self-install', 'install', 'help'))) {
    $action = 'help';
}

if ('help' === $action) {
    echo 'Usage: ' . PHP_EOL
        . ' ' . basename(__FILE__) . ' show' . PHP_EOL
        . '  for showing conf to stdout' . PHP_EOL
        . ' sudo ' . basename(__FILE__) . ' install' . PHP_EOL
        . '  to install conf to /etc/nginx/sites-enabled and restart nginx' . PHP_EOL
        . ' sudo ' . basename(__FILE__) . ' self-install' . PHP_EOL
        . '  to install this tool to /usr/local/bin' . PHP_EOL
    ;
    exit();
}

if ('self-install' === $action) {
    echo "Copying script to /usr/local/bin/nginx-here" . PHP_EOL;
    _system('cp ' . __FILE__ . ' /usr/local/bin/nginx-here');
    exit;
}

$dir = getcwd();
$dirName = basename($dir);

$conf = <<<CONF
server {
    root $dir;
    index index.php index.html index.htm;
    #error_log $dir/nginx-error.log error;

    charset        utf-8;

    # Make site accessible from http://localhost/
    server_name $dirName.*;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}

CONF;

if ('show' === $action || 'install' === $action) {
    echo "Nginx config for current directory: \n";
    echo $conf;
}

if ('install' === $action) {
    $confFileName = '/etc/nginx/sites-available/' . $dirName . '.conf';
    echo "Storing nginx conf file to $confFileName\n";
    file_put_contents($confFileName, $conf);
    $confEnabledFileName = '/etc/nginx/sites-enabled/' . $dirName . '.conf';
    echo "Creating symlink at $confEnabledFileName\n";
    _system("ln -s $confFileName $confEnabledFileName");
    _system("nginx -s reload");
}


function _system($command) {
    echo $command . PHP_EOL;
    system($command);
}
