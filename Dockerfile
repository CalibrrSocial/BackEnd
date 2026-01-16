# using in LOCAL

FROM php:7.4-fpm-alpine3.13

ARG DEBIAN_FRONTEND=noninteractive

RUN apk add --no-cache \
        acl \
        fcgi \
        file \
        gettext \
        git \
        bash \
        jq \
        nano \
        zip \
        unzip \
    ;

RUN set -eux; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
        zlib-dev \
        coreutils \
    ; \
    \
    # docker-php-ext-configure zip --with-libzip; \
    docker-php-ext-install -j$(nproc) \
        exif \
        intl \
        pdo_mysql \
        zip \
        bcmath \
        json \
    ;

# GD Extension
RUN set -eux; \
    apk add --no-cache --virtual .build-gd-ext \
        freetype-dev \
        libtool \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
    && docker-php-ext-configure gd \
        # NEW config https://github.com/docker-library/php/issues/912
        --with-freetype=/usr/include/ \
        # No longer necessary as of 7.4; https://github.com/docker-library/php/pull/910#issuecomment-559383597
        # --with-png=/usr/include/ \ 
        --with-webp=/usr/include/ \
        --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd \
    # && apk del --no-cache .build-gd-ext \
    && rm -rf /tmp/*

# Redis, APCu & OPcache Extensions
RUN docker-php-ext-install -j$(nproc) \
        opcache \
    ; \
    pecl install \
        redis \
        apcu-5.1.17 \
    ; \
    pecl clear-cache; \
    docker-php-ext-enable \
        redis \
        apcu \
        opcache \
    ; \
    rm -rf /tmp/*

RUN runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
            | tr ',' '\n' \
            | sort -u \
            | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )"; \
    apk add --no-cache --virtual .sylius-phpexts-rundeps $runDeps; \
    \
    apk del .build-deps \
    && rm -rf /tmp/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.lock /var/www/html/

# Set working directory
WORKDIR /var/www/html


COPY --chown=www-data:www-data ./ /var/www/html/

COPY docker/config/php/docker-laravel-entrypoint.sh /usr/local/bin/docker-laravel-entrypoint

RUN chmod +x /usr/local/bin/docker-laravel-entrypoint

# Using docker on Mac => comment row below
ENTRYPOINT ["docker-laravel-entrypoint"]

CMD ["php-fpm"]