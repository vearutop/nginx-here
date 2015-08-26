# nginx-here
Script to create nginx vhost in current working dir

Usage: 
 nginx-here.php show [vhost-prefix] for showing conf to stdout
 sudo nginx-here.php install [vhost-prefix] to install conf to /etc/nginx/sites-enabled and reload nginx
 sudo nginx-here.php remove [vhost-prefix] to remove conf from /etc/nginx/sites-enabled and reload nginx
 sudo nginx-here.php self-install  to install this tool to /usr/local/bin
 
If vhost-prefix is not specified, current directory name is being used.
    
