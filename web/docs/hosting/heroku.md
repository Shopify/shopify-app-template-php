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

1. Inside the `web` directory of your app's source code, create a `Procfile` that includes the instruction Heroku needs to start your app and commit it to your git repository.

    ```shell
    cd web
    echo "web: vendor/bin/heroku-php-nginx -C public/nginx.conf public/
    release: bash ./heroku-release.sh" > Procfile
    git add Procfile
    git commit -m "Add start command for Heroku"
    ```

1. From the [Heroku apps dashboard](https://dashboard.heroku.com/apps), select the app, select _Settings_ and add the environment variables `SHOPIFY_API_KEY`, `SHOPIFY_API_SECRET`, `HOST` and `SCOPES` to Heroku in _Config Vars_.
   You'll also need to set the `PROJECT_PATH` variable so that your app runs on `web`, and your preferred database.
   You'll set your database URI to `DB_DATABASE`.
   These can also be set using the CLI, for example:

    ```shell
    heroku config:set SHOPIFY_API_KEY=ReplaceWithKEYFromPartnerDashboard
    heroku config:set SHOPIFY_API_SECRET=ReplaceWithSECRETFromPartnerDashboard
    heroku config:set HOST=https://my-app-name.herokuapp.com
    heroku config:set SCOPES=write_products
    ```

    Note that these commands can be combined into a single command:

    ```shell
    heroku config:set SHOPIFY_API_KEY=... SHOPIFY_API_SECRET=... HOST=... SCOPES=...
    ```

1. Push the app to Heroku: `git push heroku master`. This will automatically deploy the app.

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
