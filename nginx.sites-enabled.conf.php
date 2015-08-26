server {
    root <?=$dir?>;
    index index.php index.html index.htm;
    error_log <?=$dir?>/nginx-error.log error;

    charset        utf-8;

    server_name <?=$vhost?>.*;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
