FROM composer:2.3.5 as composer

WORKDIR /app/
COPY composer.* ./
# --ignore-platform-reqs as composer image uses PHP 8
RUN composer install --ignore-platform-reqs


FROM php:8.1.6

WORKDIR /app/
COPY . /app
COPY --from=composer /app/vendor /app/vendor

ENTRYPOINT [ "php" ]
CMD [ "run.php" ]
