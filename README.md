# YOUPIP APP
<hr/>

## THANKS
- from github > https://github.com/Athlon1600/youtube-downloader


## RUN PROJECT
- ```cd docker && docker-compose build```
- ```docker-compose up -d```
- ```docker exec -it php_pip```
- ```php artisan jwt:secret```
- ```php artisan key:generate```
- ```php artisan storage:link```

```sudo ssh -i /Users/dangtinh/Documents/code2023/cert/LightsailDefaultKey-ap-southeast-1.pem ubuntu@13.214.5.227```

### certbot
- use reverse proxy
https://www.inmotionhosting.com/support/website/ssl/lets-encrypt-ssl-ubuntu-with-certbot/
fix nginx: https://stackoverflow.com/questions/35868976/nginx-service-failed-because-the-control-process-exited
-ubuntu 22.04
https://www.digitalocean.com/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-22-04
