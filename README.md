# YOUPIP APP
<hr/>

## THANKS
- from github > https://github.com/Athlon1600/youtube-downloader


## RUN PROJECT
- ```cd docker && docker-compose build```
- ```docker-compose up -d```
- ```docker exec -it php_pip bash```
- ```composer i```
- ```php artisan jwt:secret```
- ```php artisan key:generate```
- ```php artisan storage:link```

```sudo ssh -i /Users/dangtinh/Documents/code2023/cert/LightsailDefaultKey-ap-southeast-1.pem ubuntu@13.214.5.227```
- ``` sudo service supervisor stop ```
- ```sudo service supervisor start ```
- ```sudo supervisorctl restart all ```
### certbot

https://www.digitalocean.com/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-20-04
- use reverse proxy
https://www.inmotionhosting.com/support/website/ssl/lets-encrypt-ssl-ubuntu-with-certbot/
fix nginx: https://stackoverflow.com/questions/35868976/nginx-service-failed-because-the-control-process-exited
-ubuntu 22.04
https://www.digitalocean.com/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-22-04


```- Congratulations! Your certificate and chain have been saved at:
   /etc/letsencrypt/live/youpip.net/fullchain.pem
   Your key file has been saved at:
   /etc/letsencrypt/live/youpip.net/privkey.pem
   Your cert will expire on 2023-09-11. To obtain a new or tweaked
   version of this certificate in the future, simply run certbot again
   with the "certonly" option. To non-interactively renew *all* of
   your certificates, run "certbot renew"
 - Your account credentials have been saved in your Certbot
   configuration directory at /etc/letsencrypt. You should make a
   secure backup of this folder now. This configuration directory will
   also contain certificates and private keys obtained by Certbot so
   making regular backups of this folder is ideal.
 - If you like Certbot, please consider supporting our work by:

   Donating to ISRG / Let's Encrypt:   https://letsencrypt.org/donate
   Donating to EFF:                    https://eff.org/donate-le```
