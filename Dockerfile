FROM composer:2.1.11 as composer

WORKDIR /app/
COPY composer.* ./
# --ignore-platform-reqs as composer image uses PHP 8
RUN composer install --ignore-platform-reqs


FROM php:8.0.12

WORKDIR /app/
COPY . /app
COPY --from=composer /app/vendor /app/vendor

ENTRYPOINT [ "php" ]
CMD [ "run.php" ]
