ARG DOCKERHUB_URL


FROM ${DOCKERHUB_URL}/php:7.3.33-buster as build

ARG DEPLOY_ENV
WORKDIR /opt/app
COPY cicd/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions
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
COPY controllers controllers
COPY cicd/vendor.tar.gz ./
COPY cicd/envs/${DEPLOY_ENV}.env .env
RUN tar -xzf vendor.tar.gz vendor && rm vendor.tar.gz
RUN echo 'RewriteEngine on \n\
RewriteCond %{REQUEST_FILENAME} !-f \n\
RewriteCond %{REQUEST_FILENAME} !-d \n\
RewriteRule . index.php \n' > web/.htaccess

FROM ${DOCKERHUB_URL}/php:7.3.33-apache-buster as base
ARG APT_MIRROR
RUN echo "deb ${APT_MIRROR} buster main" $'\n' "deb ${APT_MIRROR} buster-proposed-updates main non-free contrib" > /etc/apt/sources.list && apt update -y && apt-get install -y cron && apt-get install libpq-dev -y
COPY --from=build /usr/local/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    pdo_pgsql \
    soap \
    bcmath \
    wddx \
    xsl \
    zip \
    sockets \
    simplexml \
    phar \
    gettext \
    gd \
    exif \
    calendar \
    bz2 \
    intl 
ENV APACHE_DOCUMENT_ROOT /var/www/html/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN echo "ServerName localhost" >> /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite
COPY cicd/php.ini "$PHP_INI_DIR/php.ini"
COPY --from=build --chown=www-data:www-data opt/app/ .
COPY cicd/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY cicd/scripts/ /opt/scripts/
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN chmod -R +x /opt/scripts
RUN apt-get update && apt-get install libpq-dev -y  && docker-php-ext-install pgsql && docker-php-ext-install pdo_pgsql && service apache2 restart
RUN unlink /etc/localtime && ln -s /usr/share/zoneinfo/Europe/Moscow /etc/localtime
COPY cicd/apache-config/mpm_prefork.conf /etc/apache2/mods-available/mpm_prefork.conf
RUN service apache2 restart

EXPOSE 80