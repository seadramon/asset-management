FROM fpm-asset

COPY composer.lock composer.json /var/www/asset/

COPY database /var/www/asset/database

WORKDIR /var/www/asset

# RUN php composer.phar install --no-dev --no-scripts
# COPY asset-worker.conf /etc/supervisor/conf.d/

COPY . /var/www/asset

RUN echo '* * * * * root cd /var/www/asset && php artisan schedule:run >> /root/schedule.log' >> /etc/crontab

RUN chown -R www-data:www-data \
        /var/www/asset/storage \
        /var/www/asset/bootstrap/cache

RUN chmod -R 777 \
        /var/www/asset/storage \
        /var/www/asset/bootstrap/cache

RUN mv develop.env .env
# RUN composer install; php artisan optimize
# RUN supervisord
