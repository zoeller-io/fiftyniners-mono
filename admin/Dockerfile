FROM php:8.3.8-fpm

RUN apt update && \
    apt install -y libzip-dev zip && \
    apt install -y --no-install-recommends \
    acl apt-transport-https apt-utils build-essential curl debconf git gnupg gnupg1 gnupg2 \
    libjpeg-dev libldb-dev libldap2-dev libonig-dev libpq-dev libpng-dev \
    libssl-dev libxml2 libxml2-dev libxpm-dev libzip-dev libzip4 locales \
    mailutils nano openssl sudo unzip wget zip zlib1g-dev && \
    docker-php-ext-install bcmath exif gd intl ldap opcache pdo_mysql pdo_pgsql sockets zip && \
    pecl install redis && docker-php-ext-enable redis && \
    pecl install xdebug && docker-php-ext-enable xdebug && \
    apt autoremove && \
    apt clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* && \
    echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && locale-gen

WORKDIR /var/packages/admin-app

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && mv composer.phar /usr/local/bin/composer
