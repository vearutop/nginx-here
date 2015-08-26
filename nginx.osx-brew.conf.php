server {
    listen 8080;
    root <?=$dir?>;
    index index.php index.html index.htm;
    error_log <?=$dir?>/nginx-error.log error;

    charset        utf-8;

    server_name <?=$vhost?>.*;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_intercept_errors on;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include /usr/local/etc/nginx/fastcgi_params;
    }
}