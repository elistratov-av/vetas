FROM php:7.3.33-buster as build

WORKDIR /opt/app
COPY yii ./
COPY runtime runtime
COPY config config
COPY migrations migrations
COPY components components
COPY commands commands
COPY models models
COPY modules modules
COPY common common
COPY web web
COPY views views
COPY controllers controllers
COPY cicd/vendor.tar.gz ./
COPY cicd/envs/test-lifeit.env .env
RUN tar -xzf vendor.tar.gz vendor && rm vendor.tar.gz
RUN echo 'RewriteEngine on \n\
RewriteCond %{REQUEST_FILENAME} !-f \n\
RewriteCond %{REQUEST_FILENAME} !-d \n\
RewriteRule . index.php \n' > web/.htaccess

FROM 192.168.10.37:8083/back_base:1.0 as base
ARG APT_MIRROR
ENV APACHE_DOCUMENT_ROOT /var/www/html/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN echo "ServerName localhost" >> /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite
RUN mkdir /opt/keys
RUN chmod 777 -R /opt
COPY cert/jwt_rsa /opt/keys/
COPY cert/jwt_rsa.cer /opt/keys/
COPY cert/jwt_rsa.pem /opt/keys/
COPY cicd/php.ini "$PHP_INI_DIR/php.ini"
COPY --from=build --chown=www-data:www-data opt/app/ .
COPY cicd/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY cicd/scripts/ /opt/scripts/
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN chmod -R +x /opt/scripts
RUN unlink /etc/localtime && ln -s /usr/share/zoneinfo/Europe/Moscow /etc/localtime
COPY cicd/apache-config/mpm_prefork.conf /etc/apache2/mods-available/mpm_prefork.conf
RUN service apache2 restart

EXPOSE 80
