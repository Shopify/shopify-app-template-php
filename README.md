# Shopify App Template - PHP

This is a template for building a [Shopify app](https://shopify.dev/apps/getting-started) using PHP and React. It contains the basics for building a Shopify app.

Rather than cloning this repo, you can use your preferred package manager and the Shopify CLI with [these steps](#installing-the-template).

## Benefits

Shopify apps are built on a variety of Shopify tools to create a great merchant experience. The [create an app](https://shopify.dev/apps/getting-started/create) tutorial in our developer documentation will guide you through creating a Shopify app using this template.

The PHP app template comes with the following out-of-the-box functionality:

- OAuth: Installing the app and granting permissions
- GraphQL Admin API: Querying or mutating Shopify admin data
- REST Admin API: Resource classes to interact with the API
- Shopify-specific tooling:
  - AppBridge
  - Polaris
  - Webhooks

## Tech Stack

This template combines a number of third party open source tools:

- [Laravel](https://laravel.com/) builds and tests the backend.
- [Vite](https://vitejs.dev/) builds the [React](https://reactjs.org/) frontend.
- [React Router](https://reactrouter.com/) is used for routing. We wrap this with file-based routing.
- [React Query](https://react-query.tanstack.com/) queries the GraphQL Admin API.

These third party tools are complemented by Shopify specific tools to ease app development:

- [Shopify API library](https://github.com/Shopify/shopify-php-api) adds OAuth to the Laravel backend. This lets users install the app and grant scope permissions.
- [App Bridge React](https://shopify.dev/tools/app-bridge/react-components) adds authentication to API requests in the frontend and renders components outside of the embedded App’s iFrame.
- [Polaris React](https://polaris.shopify.com/) is a powerful design system and component library that helps developers build high quality, consistent experiences for Shopify merchants.
- [Custom hooks](https://github.com/Shopify/shopify-frontend-template-react/tree/main/hooks) make authenticated requests to the GraphQL Admin API.
- [File-based routing](https://github.com/Shopify/shopify-frontend-template-react/blob/main/Routes.jsx) makes creating new pages easier.

## Getting started

### Requirements

1. You must [create a Shopify partner account](https://partners.shopify.com/signup) if you don’t have one.
1. You must [create a development store](https://help.shopify.com/en/partners/dashboard/development-stores#create-a-development-store) if you don’t have one.
1. You must have [PHP](https://www.php.net/) installed.
1. You must have [Composer](https://getcomposer.org/) installed.
1. You must have [Node.js](https://nodejs.org/) installed.

### Installing the template

This template runs on Shopify CLI 3.0, which is a node package that can be included in projects. You can install it using your preferred Node.js package manager:

Using yarn:

```shell
yarn create @shopify/app --template php
```

Using npx:

```shell
npm init @shopify/app@latest --template php
```

Using pnpm:

```shell
pnpm create @shopify/app@latest --template php
```

This will clone the template and install the CLI in that project.

### Setting up your Laravel app

Once the Shopify CLI clones the repo, you will be able to run commands on your app.
However, the CLI will not manage your PHP dependencies automatically, so you will need to go through some steps to be able to run your app.
These are the typical steps needed to set up a Laravel app once it's cloned:

1. Start off by switching to the `web` folder:

    ```shell
    cd web
    ```

1. Install your composer dependencies:

    ```shell
    composer install
    ```

1. Create the `.env` file:

    ```shell
    cp .env.example .env
    ```

1. Bootstrap the default [SQLite](https://www.sqlite.org/index.html) database and add it to your `.env` file:

    ```shell
    touch storage/db.sqlite
    ```

    **NOTE**: Once you create the database file, make sure to update your `DB_DATABASE` variable in `.env` since Laravel requires a full path to the file.

1. Generate an `APP_KEY` for your app:

    ```shell
    php artisan key:generate
    ```

1. Create the necessary Shopify tables in your database:

    ```shell
    php artisan migrate
    ```

And your Laravel app is ready to run! You can now switch back to your app's root folder to continue:

```shell
cd ..
```

### Local Development

[The Shopify CLI](https://shopify.dev/apps/tools/cli) connects to an app in your Partners dashboard.
It provides environment variables, runs commands in parallel, and updates application URLs for easier development.

You can develop locally using your preferred Node.js package manager.
Run one of the following commands from the root of your app:

Using yarn:

```shell
yarn dev
```

Using npm:

```shell
npm run dev
```

Using pnpm:

```shell
pnpm run dev
```

Open the URL generated in your console. Once you grant permission to the app, you can start development.

### Testing backend code

Unit tests exist for the backend. First, build the [frontend](#build) and then run them using composer:

```shell
cd web && composer test
```

### Testing frontend code

Unit tests exist for the frontend. Run these using your preferred package manager:

Using yarn:

```shell
cd web/frontend && yarn test
```

Using npm:

```shell
cd web/frontend && npm run test
```

Using pnpm:

```shell
cd web/frontend && pnpm run test
```

## Deployment

### Application Storage

This template uses [Laravel's Eloquent framework](https://laravel.com/docs/9.x/eloquent) to store Shopify session data.
It provides migrations to create the necessary tables in your database, and it stores and loads session data from them.

The database that works best for you depends on the data your app needs and how it is queried.
You can run your database of choice on a server yourself or host it with a SaaS company.
Once you decide which database to use, you can update your Laravel app's `DB_*` environment variables to connect to it, and this template will start using that database for session storage.

### Build

The frontend is a single page React app. It requires the `SHOPIFY_API_KEY` environment variable, which you can find on the page for your app in your partners dashboard.
The CLI will set up the necessary environment variables for the build if you run its `build` command from your app's root:

Using yarn:

```shell
yarn build --api-key=REPLACE_ME
```

Using npm:

```shell
npm run build --api-key=REPLACE_ME
```

Using pnpm:

```shell
pnpm run build --api-key=REPLACE_ME
```

The app build command will build both the frontend and backend when running as above.
If you're manually building (for instance when deploying the `web` folder to production), you'll need to build both of them:

```shell
cd web/frontend
SHOPIFY_API_KEY=REPLACE_ME yarn build
cd ..
composer build
```

## Hosting

Before you host your app in a production environment, make sure to create the production app in your Partner's Dashboard.
You'll need to set up the API key and API secret for your production environment, as per the instructions below.

The following pages document the basic steps to host and deploy your application to a few popular cloud providers:

- [fly.io](/web/docs/hosting/fly-io.md)
- [Heroku](/web/docs/hosting/heroku.md)

## Known issues

### Hot module replacement and Firefox

When running the app with the CLI in development mode on Firefox, you might see your app constantly reloading when you access it.
That happens because of the way HMR websocket requests work, and the way the CLI is set up to tunnel requests through ngrok.

Until we find a permanent solution that enables HMR on Firefox, this template accepts the `SHOPIFY_VITE_HMR_USE_POLLING` env var to replace HMR with polling.
While not as responsive as HMR, the frontend will still refresh itself every few seconds with your changes.

You can export this variable from your shell profile, or set it when running the `dev` command:

```shell
# using yarn
SHOPIFY_VITE_HMR_USE_POLLING=1 yarn dev
# or using npm
SHOPIFY_VITE_HMR_USE_POLLING=1 npm run dev
# or using pnpm
SHOPIFY_VITE_HMR_USE_POLLING=1 pnpm dev
```

### I can't get past the ngrok "Visit site" page

When you’re previewing your app or extension, you might see an ngrok interstitial page with a warning:

```
You are about to visit <id>.ngrok.io: Visit Site
```

If you click the `Visit Site` button, but continue to see this page, then you should run dev using an alternate tunnel URL that you run using tunneling software.
We've validated that [Cloudflare Tunnel](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/run-tunnel/trycloudflare/) works with this template.

To do that, you can [install the `cloudflared` CLI tool](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/installation/), and run:

```shell
# Note that you can also use a different port
cloudflared tunnel --url http://localhost:3000
```

In a different terminal window, navigate to your app's root and call:

```shell
# Using yarn
yarn dev --tunnel-url https://tunnel-url:3000
# or using npm
npm run dev --tunnel-url https://tunnel-url:3000
# or using pnpm
pnpm dev --tunnel-url https://tunnel-url:3000
```

## Developer resources

- [Introduction to Shopify apps](https://shopify.dev/apps/getting-started)
- [App authentication](https://shopify.dev/apps/auth)
- [Shopify CLI](https://shopify.dev/apps/tools/cli)
- [Shopify API Library documentation](https://github.com/Shopify/shopify-php-api/tree/main/docs)
