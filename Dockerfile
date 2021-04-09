FROM composer:2.0 as composer

WORKDIR /app/
COPY composer.* ./
# --ignore-platform-reqs as composer image uses PHP 8
RUN composer install --ignore-platform-reqs


FROM php:7.4

WORKDIR /app/
COPY . ./
COPY --from=composer /app/vendor /appvendor

ENTRYPOINT [ "php" ]
CMD [ "run.php" ]