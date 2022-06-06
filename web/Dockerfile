FROM php:7.4-fpm-alpine

ARG SHOPIFY_API_KEY
ENV SHOPIFY_API_KEY=$SHOPIFY_API_KEY

EXPOSE 8081/tcp
WORKDIR /app

COPY . .

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apt update && apt install composer node

RUN cd web && composer install
RUN touch web/storage/db.sqlite
ENV DB_DATABASE=/app/web/storage/db.sqlite
RUN php web/artisan key:generate
RUN php web/artisan migrate --force

RUN rm -rf node_modules && npm install
RUN cd web/frontend && rm -rf node_modules && npm install
RUN cd web/frontend && rm -rf dist && npm run build
CMD composer serve
