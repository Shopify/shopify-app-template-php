FROM php:8.1-fpm-alpine

ARG SHOPIFY_API_KEY
ENV SHOPIFY_API_KEY=$SHOPIFY_API_KEY

# Runtime env vars

# Shopify env vars
ENV SHOPIFY_API_SECRET="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"
ENV SCOPES="write_products"
ENV PORT="8081"
ENV HOST="76ae-142-59-175-144.ngrok.io"

# Laravel env vars
ENV APP_NAME="Shopify PHP App"
ENV APP_ENV="production"
ENV APP_KEY="base64:kmw4OlhKgRa9EfXIcK5TAfwnAesbwJ7Jx/TOMXScHHY="
ENV DB_CONNECTION="sqlite"
ENV DB_FOREIGN_KEYS="true"
ENV DB_DATABASE="/app/storage/db.sqlite"

EXPOSE 8081/tcp

RUN apk update && apk add --update nodejs npm \
  composer php-simplexml php-fileinfo php-dom php-tokenizer php-xml php-xmlwriter \
  openrc \
  nginx

COPY --chown=www-data:www-data . /app
WORKDIR /app

# Overwrite default nginx config
COPY nginx.conf /etc/nginx/nginx.conf

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN composer install
RUN echo "" > storage/db.sqlite

RUN cd frontend && rm -rf node_modules && npm install && rm -rf dist && npm run build
RUN composer build

ENTRYPOINT [ "/app/entrypoint.sh" ]
