# Shopify App Template - PHP

This is a template for building a [Shopify embedded app](https://shopify.dev/apps/getting-started) using PHP and React. It contains the basics for building a Shopify app.

## Benefits

Shopify apps are built on a variety of Shopify tools to create a great merchant experience. The [create an app](https://shopify.dev/apps/getting-started/create) tutorial in our developer documentation will guide you through creating a Shopify app using this template.

The PHP app template comes with the following out-of-the-box functionality:

-   OAuth: Installing the app and granting permissions
-   GraphQL Admin API: Querying or mutating Shopify admin data
-   REST Admin API: Resource classes to interact with the API
-   Shopify-specific tooling:
    -   AppBridge
    -   Polaris
    -   Webhooks

## Tech Stack

This template combines a number of third party open source tools:

-   [Laravel](https://laravel.com/) builds and tests the backend.
-   [Vite](https://vitejs.dev/) builds the [React](https://reactjs.org/) frontend.
-   [React Router](https://reactrouter.com/) is used for routing. We wrap this with file-based routing.
-   [React Query](https://react-query.tanstack.com/) queries the GraphQL Admin API.

These third party tools are complemented by Shopify specific tools to ease app development:

-   [Shopify API library](https://github.com/Shopify/shopify-php-api) adds OAuth to the Laravel backend. This lets users install the app and grant scope permissions.
-   [App Bridge React](https://shopify.dev/tools/app-bridge/react-components) adds authentication to API requests in the frontend and renders components outside of the embedded App’s iFrame.
-   [Polaris React](https://polaris.shopify.com/) is a powerful design system and component library that helps developers build high quality, consistent experiences for Shopify merchants.
-   [Custom hooks](https://github.com/Shopify/shopify-frontend-template-react/tree/main/hooks) make authenticated requests to the GraphQL Admin API.
-   [File-based routing](https://github.com/Shopify/shopify-frontend-template-react/blob/main/Routes.jsx) makes creating new pages easier.

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
npm init @shopify/app --template php
```

Using pnpm:

```shell
pnpm create @shopify/app@latest --template php
```

This will clone the template and install the CLI in that project.

### Setting up your Laravel app

Once the Shopify CLI clones the repo, you will be able to run commands on your app. However, the CLI will not manage your PHP dependencies automatically, so you will need to go through some steps to be able to run your app. These are the typical steps needed to set up a Laravel app once it's cloned:

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
1. Generate an APP_KEY for your app:
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

[The Shopify CLI](https://shopify.dev/apps/tools/cli) connects to an app in your Partners dashboard. It provides environment variables, runs commands in parallel, and updates application URLs for easier development.

You can develop locally using your preferred Node.js package manager. Run one of the following commands from the root of your app:

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

Using yarn:

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

This template uses [Laravel's Eloquent framework](https://laravel.com/docs/9.x/eloquent) to store Shopify session data. It provides migrations to create the necessary tables in your database, and it stores and loads session data from them.

The database that works best for you depends on the data your app needs and how it is queried. You can run your database of choice on a server yourself or host it with a SaaS company. Once you decide which database to use, you can update your Laravel app's `DB_*` environment variables to connect to it, and this template will start using that database for session storage.

### Build

The frontend is a single page React app. It requires the `SHOPIFY_API_KEY` environment variable, which you can find on the page for your app in your partners dashboard. The CLI will set up the necessary environment variables for the build if you run its `build` command from your app's root:

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

You do not need to build the backend.

## Hosting

The following sections document the basic steps to host and deploy your application to a few popular cloud providers.

### fly.io

#### Create and deploy initial version of the app

##### - Create a fly.io account

1. Go to [fly.io](https://fly.io) and click on _Get Started_.
2. [Download and install](https://fly.io/docs/flyctl/installing/) the Fly CLI
3. From the command line, sign up for Fly: `flyctl auth signup`. You can sign-up with an email address or with a GitHub account.
4. Fill in credit card information and click _Subscribe_.

##### - Build and deploy a container

5. Change in to the `web` directory: `cd web`.
6. Create an app using `flyctl launch`. You can choose your own app name or press enter to let Fly pick an app name. Choose a region for deployment (it should default to the closest one to you). Choose _No_ for DB. Choose _No_ to deploy now.
7. Make the following changes to the `fly.toml` file.

    - In the `[env]` section, add the following environment variables (in a `"` delimited string):

        - `BACKEND_PORT` set to the same value as the `EXPOSE` line in the `Dockerfile`. The default value in the `Dockerfile` is `8081`.
        - `HOST` set to the URL of the new app - this can be constructed by taking the `app` variable at the very top of the `fly.toml` file, prepending it with `https://` and adding `.fly.dev` to the end, e.g, if `app` is `"fancy-cloud-1234"`, then `HOST` should be set to `https://fancy-cloud-1234.fly.dev`
        - `SCOPES` with the appropriate scopes for your app, the default for the unmodified template is `write_products`
        - `SHOPIFY_API_KEY` set to the API key for your app, obtained from the partner dashboard.

    - In the `[[services]]` section, change the value of `internal_port` to match the `BACKEND_PORT` value.

    - Example:

        ```ini
        :
        :
        [env]
          BACKEND_PORT = "8081"
          HOST = "https://fancy-cloud-1234.fly.dev"
          SCOPES = "write_products"
          SHOPIFY_API_KEY = "ReplaceWithKEYFromPartnerDashboard"

        :
        :

        [[services]]
          internal_port = 8081
        :
        :
        ```

8. Set the API secret environment variable for your app:

    ```shell
    flyctl secrets set SHOPIFY_API_SECRET=ReplaceWithSECRETFromPartnerDashboard
    ```

9. Build and deploy the app - note that you'll need the `SHOPIFY_API_KEY` to pass to the command

    ```shell
    flyctl deploy --build-arg SHOPIFY_API_KEY=ReplaceWithKEYFromPartnerDashboard
    ```

##### - Update URLs in Partner Dashboard

10. In the Partner Dashboard, update the main URL for your app to the url from the [fly.io dashboard](https://fly.io/dashboard) and set a callback URL to the same url with `/api/auth/callback` appended to it. Note: this is the same as the `HOST` environment variable set above.

#### Deploy a new version of the app

1. After updating your code with new features and fixes, rebuild and redeploy using:

    ```shell
    flyctl deploy --build-arg SHOPIFY_API_KEY=ReplaceWithKeyFromPartnerDashboard
    ```

#### To scale to multiple regions

1. Add a new region using `flyctl regions add CODE`, where `CODE` is the three-letter code for the region. To obtain a list of regions and code, run `flyctl platform regions`.
2. Scale to two instances - `flyctl scale count 2`

### Heroku

> Note: this deployment to Heroku relies on `git` so your app will need to be committed to a `git` repository. If you haven't done so yet, run the following commands to initialize and commit your source code to a `git` repository:

```shell
# be at the top-level of your app directory
git init
git add .
git commit -m "Initial version"
```

#### Create and login to a Heroku account

<table>
  <tr>
    <td>1.</td>
    <td>Go to <a href="https://heroku.com">heroku.com</a> and click on <em>Sign up</em></td>
  </tr>
  <tr>
    <td>2.</td>
    <td><a herf="https://devcenter.heroku.com/articles/heroku-cli#install-the-heroku-cli">Download and install</a> the Heroku CLI</td>
  </tr>
  <tr>
    <td>3.</td>
    <td>Login to the Heroku CLI using <code>heroku login</code></td>
  </tr>
</table>

#### Build and deploy from Git repo

<table>
  <tr>
    <td>4.</td>
    <td>Create an app in Heroku using <code>heroku create -a my-app-name</code>. This will create a git remote named <code>heroku</code> for deploying the app to Heroku.  It will also return the URL to where the app will run when deployed, in the form of<br><code>https://my-app-name.herokuapp.com</code></td>
  </tr>
  <tr>
    <td>5.</td>
    <td>At the top-level directory of your app's source code, create a <code>Procfile</code> that includes the instruction Heroku needs to start your app and commit it to your git repository.
    <pre>
echo "web: cd web && npm run serve" > Procfile
git add Procfile
git commit -m "Add start command for Heroku"</pre>
    </td>
  </tr>
  <tr>
    <td>6.</td>
    <td>From the <a href="https://dashboard.heroku.com/apps">Heroku apps dashboard</a>, select the app, select <em>Settings</em> and add the environment variables <code>SHOPIFY_API_KEY</code>, <code>SHOPIFY_API_SECRET</code>, <code>HOST</code> and <code>SCOPES</code> to Heroku in <em>Config Vars</em>.  These can also be set using the CLI, for example:
    <pre>
heroku config:set SHOPIFY_API_KEY=ReplaceWithKEYFromPartnerDashboard
heroku config:set SHOPIFY_API_SECRET=ReplaceWithSECRETFromPartnerDashboard
heroku config:set HOST=https://my-app-name.herokuapp.com
heroku config:set SCOPES=write_products
</pre>Note that these commands can be combined into a single command:
<pre>
heroku config:set SHOPIFY_API_KEY=... SHOPIFY_API_SECRET=... HOST=... SCOPES=...
</pre>
    </td>
  </tr>
  <tr>
    <td>7.</td>
    <td>Push the app to Heroku: <code>git push heroku main</code>.  This will automatically deploy the app.</td>
  </tr>
</table>

#### Update URLs in Partner Dashboard

<table>
  <tr>
    <td>8.</td>
    <td>Update main and callback URLs in Partner Dashboard to point to new app.  The main app URL should point to <br><code>https://my-app-name.herokuapp.com</code><br> and the callback URL should be<br><code>https://my-app-name.herokuapp.com/api/auth/callback</code></td>
  </tr>
</table>

#### Test the app

<table>
  <tr>
    <td>9.</td>
    <td>Open the deployed app by browsing to<br><code>https://my-app-name.herokuapp.com/api/auth?shop=my-dev-shop-name.myshopify.com</code></td>
  </tr>
</table>

#### Deploy a new version of the app

<table>
  <tr>
    <td>1.</td>
    <td>Update code and commit to git.  If updates were made on a branch, merge branch with <code>main</code>.</td>
  </tr>
  <tr>
    <td>2.</td>
    <td>Push <code>main</code> to Heroku: <code>git push heroku main</code> - this will automatically deploy the new version of your app.</td>
  </tr>
</table>

> Heroku's dynos should restart automatically after setting the environment variables or pushing a new update from git. If you need to restart the dynos manually, use `heroku ps:restart`.

## Developer resources

-   [Introduction to Shopify apps](https://shopify.dev/apps/getting-started)
-   [App authentication](https://shopify.dev/apps/auth)
-   [Shopify CLI](https://shopify.dev/apps/tools/cli)
-   [Shopify API Library documentation](https://github.com/Shopify/shopify-php-api/tree/main/docs)
