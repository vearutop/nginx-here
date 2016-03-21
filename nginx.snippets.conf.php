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
        fastcgi_pass <?=$fastcgiPass?>;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        include snippets/fastcgi-php.conf;
    }
}
