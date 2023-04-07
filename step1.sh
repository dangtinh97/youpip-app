cp ./docker/nginx/conf.d/default-2.conf /etc/nginx/nginx.conf

fuser -k 80/tcp
fuser -k 443/tcp
systemctl status nginx
service nginx restart
