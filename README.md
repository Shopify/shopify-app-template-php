# Example Shopify PHP app

## Starting the App

### Environment variables

Make sure that you have a `.env` file. You can look at `.env.example` and `.env.testing` for inspiration. `.env file must contain the following environment variables
- SHOPIFY_API_KEY
- SHOPIFY_API_SECRET
- SCOPES
- HOST

### Start laravel

`php artisan serve`

### Serving React
Make sure to run `npm install` before running the application.

`npm run watch`

Check [Laravel Docs: Running Mix](https://laravel.com/docs/8.x/mix#running-mix) for more information

## Running tests

```
composer install
composer test
```
