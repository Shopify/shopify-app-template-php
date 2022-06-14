# Hosting on Heroku

> Note: this deployment to Heroku relies on `git` so your app will need to be committed to a `git` repository. If you haven't done so yet, run the following commands to initialize and commit your source code to a `git` repository:

```shell
# be at the top-level of your app directory
git init
git add .
git commit -m "Initial version"
```

## Create and login to a Heroku account

1. Go to [heroku.com](https://heroku.com) and click on _Sign up_
1. [Download and install](https://devcenter.heroku.com/articles/heroku-cli#install-the-heroku-cli) the Heroku CLI
1. Login to the Heroku CLI using `heroku login`

## Build and deploy from Git repo

1. Create an app in Heroku using `heroku create -a my-app-name`. This will create a git remote named `heroku` for deploying the app to Heroku. It will also return the URL to where the app will run when deployed, in the form of:

    ```text
    https://my-app-name.herokuapp.com
    ```

1. In your app's root directory, create a `heroku.yml` file that contains instructions for building your Heroku app:

    ```shell
    build:
        docker:
            web: web/Dockerfile
        config:
            SHOPIFY_API_KEY: <Your API key from `yarn run info --web-env`>
    ```

1. Set up the necessary environment variables to run in your app using the `heroku config:set` command. All the variables below should be set using a command like

    ```shell
    heroku config:set <VARIABLE>="<value>"
    ```

    Shopify app values:
    |Variable|Description/value|
    |-|-|
    |`SHOPIFY_API_KEY`|Obtainable by running `yarn run info --web-env`|
    |`SHOPIFY_API_SECRET`|Obtainable by running `yarn run info --web-env`|
    |`SCOPES`|Obtainable by running `yarn run info --web-env`|
    |`HOST`|`my-app-name.herokuapp.com`|

    Laravel values (note you can change the `DB_*` values if using a different database):
    |Variable|Description/value|
    |-|-|
    |`APP_NAME`|App name for Laravel|
    |`APP_ENV`|`production`|
    |`APP_KEY`|Obtainable by running `php web/artisan key:generate --show`|
    |`DB_CONNECTION`|`sqlite`|
    |`DB_FOREIGN_KEYS`|`true`|
    |`DB_DATABASE`|`/app/storage/db.sqlite`|

1. Add the Heroku config file to your git repo:

    ```shell
    git add heroku.yml
    git commit -m "Adding Heroku config file"
    ```

1. Set the stack of your app to `container`:

    ```shell
    heroku stack:set container
    ```

1. Push the app to Heroku. This will automatically deploy the app.

    ```shell
    git push heroku main
    ```

## Update URLs in Partner Dashboard

1. Update main and callback URLs in Partner Dashboard to point to new app. The main app URL should point to

    ```text
    https://my-app-name.herokuapp.com
    ```

    and the callback URL should be

    ```text
    https://my-app-name.herokuapp.com/api/auth/callback
    ```

## Test the app

Open the deployed app by browsing to:

```text
https://my-app-name.herokuapp.com/api/auth?shop=my-dev-shop-name.myshopify.com
```

## Deploy a new version of the app

1. Update code and commit to git. If updates were made on a branch, merge branch with `main`.
1. Push `main` to Heroku: `git push heroku main` - this will automatically deploy the new version of your app.

> Heroku's dynos should restart automatically after setting the environment variables or pushing a new update from git. If you need to restart the dynos manually, use `heroku ps:restart`.
