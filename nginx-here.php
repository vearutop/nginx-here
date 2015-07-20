#!/usr/bin/env php
<?php

$action = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'help';

if (!in_array($action, array('show', 'self-install', 'install', 'uninstall', 'help'))) {
    $action = 'help';
}

if ('help' === $action) {
    echo 'Usage: ' . PHP_EOL
        . ' ' . CLIColoredString::get(basename(__FILE__) . ' show', CLIColoredString::FG_GREEN)
        . '  for showing conf to stdout' . PHP_EOL
        . CLIColoredString::get(' sudo ' . basename(__FILE__) . ' install', CLIColoredString::FG_GREEN)
        . '  to install conf to /etc/nginx/sites-enabled and reload nginx' . PHP_EOL
        . CLIColoredString::get(' sudo ' . basename(__FILE__) . ' remove', CLIColoredString::FG_RED)
        . '  to remove conf from /etc/nginx/sites-enabled and reload nginx' . PHP_EOL
        . CLIColoredString::get(' sudo ' . basename(__FILE__) . ' self-install', CLIColoredString::FG_BLUE)
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

if ('remove' === $action) {
    $confFileName = '/etc/nginx/sites-available/' . $dirName . '.conf';
    $confEnabledFileName = '/etc/nginx/sites-enabled/' . $dirName . '.conf';
    _system("rm $confFileName $confEnabledFileName");
    _system("nginx -s reload");
}

if ('install' === $action) {
    $confFileName = '/etc/nginx/sites-available/' . $dirName . '.conf';
    echo "Storing nginx conf file to $confFileName\n";
    file_put_contents($confFileName, $conf);
    $confEnabledFileName = '/etc/nginx/sites-enabled/' . $dirName . '.conf';
    echo "Creating symlink at $confEnabledFileName\n";
    _system("ln -s $confFileName $confEnabledFileName");
    _system("nginx -s reload");

    exec("ifconfig | sed -En 's/127.0.0.1//;s/.*inet (addr:)?(([0-9]*\.){3}[0-9]*).*/\2/p'", $hosts);
    if ($hosts) {
        echo 'Try to find your vhosts at: ' . PHP_EOL;
        foreach ($hosts as $host) {
            echo CLIColoredString::get(' http://' . $dirName . '.' . $host, CLIColoredString::FG_BROWN) . PHP_EOL;
        }
    }
}


function _system($command) {
    echo CLIColoredString::get($command, CLIColoredString::FG_BLUE) . PHP_EOL;
    system($command);
}


class CLIColoredString
{
    const FG_BLACK = '0;30';
    const FG_DARK_GRAY = '1;30';
    const FG_BLUE = '0;34';
    const FG_LIGHT_BLUE = '1;34';
    const FG_GREEN = '0;32';
    const FG_LIGHT_GREEN = '1;32';
    const FG_CYAN = '0;36';
    const FG_LIGHT_CYAN = '1;36';
    const FG_RED = '0;31';
    const FG_LIGHT_RED = '1;31';
    const FG_PURPLE = '0;35';
    const FG_LIGHT_PURPLE = '1;35';
    const FG_BROWN = '0;33';
    const FG_YELLOW = '1;33';
    const FG_LIGHT_GRAY = '0;37';
    const FG_WHITE = '1;37';

    const BG_BLACK = '40';
    const BG_RED = '41';
    const BG_GREEN = '42';
    const BG_YELLOW = '43';
    const BG_BLUE = '44';
    const BG_MAGENTA = '45';
    const BG_CYAN = '46';
    const BG_LIGHT_GRAY = '47';

    public static function get($string, $fgColor = null, $bgColor = null)
    {
        $colored_string = "";
        if ($fgColor) $colored_string .= "\033[" . $fgColor . "m";
        if ($bgColor) $colored_string .= "\033[" . $bgColor . "m";
        $colored_string .= $string . "\033[0m";
        return $colored_string;
    }
}
