# Hosting on fly.io

## Create and deploy initial version of the app

### Create a fly.io account

1. Go to [fly.io](https://fly.io) and click on _Get Started_.
1. [Download and install](https://fly.io/docs/flyctl/installing/) the Fly CLI
1. From the command line, sign up for Fly: `flyctl auth signup`. You can sign-up with an email address or with a GitHub account.
1. Fill in credit card information and click _Subscribe_.

### Build and deploy a container

1. Create an app using `flyctl launch`. You can choose your own app name or press enter to let Fly pick an app name. Choose a region for deployment (it should default to the closest one to you). Choose _No_ for DB to use the default SQLite database, or select a different one otherwise. Choose _No_ to deploy now.
1. To create a new app in the Partner Dashboard or to link the app to an existing app, run the following command using your preferred package manager:

      Using yarn:

      ```shell
      yarn run info --web-env
      ```

      Using npm:

      ```shell
      npm run info --web-env
      ```

      Using pnpm:

      ```shell
      pnpm run info --web-env
      ```

      Take note of the `SCOPES`, `SHOPIFY_API_KEY` and the `SHOPIFY_API_SECRET` values, as you'll need them in the next steps.

1. Make the following changes to the `fly.toml` file.

    - In the `[env]` section, add the following environment variables (in a `"` delimited string):

        Shopify app values:
        |Variable|Description/value|
        |-|-|
        |`SHOPIFY_API_KEY`|Can be obtained from the `run info --web-env` command in the previous step|
        |`SCOPES`|Can be obtained from the `run info --web-env` command in the previous step|
        |`PORT`|The port on which to run the app|
        |`HOST`|Set this to the URL of the new app, which can be constructed by taking the `app` variable at the very top of the `fly.toml` file, prepending it with `https://` and adding `.fly.dev` to the end, e.g, if `app` is `"fancy-cloud-1234"`, then `HOST` should be set to `https://fancy-cloud-1234.fly.dev`|

        Laravel values (note you can change the `DB_*` values if using a different database):
        |Variable|Description/value|
        |-|-|
        |`APP_NAME`|App name for Laravel|
        |`APP_ENV`|`production`|
        |`DB_CONNECTION`|`sqlite`|
        |`DB_FOREIGN_KEYS`|`true`|
        |`DB_DATABASE`|`/app/storage/db.sqlite`|

    - In the `[[services]]` section, change the value of `internal_port` to match the `PORT` value.

    - Example:

        ```ini
        :
        :
        [env]
          PORT = "8080"
          SHOPIFY_API_KEY="ReplaceWithKEYFromEnvCommand"
          SCOPES="write_products"
          HOST="withered-dew-1234.fly.dev"
          APP_NAME="Test app"
          APP_ENV="production"
          DB_CONNECTION="sqlite"
          DB_FOREIGN_KEYS="true"
          DB_DATABASE="/app/storage/db.sqlite"

        :
        :

        [[services]]
          internal_port = 8080
        :
        :
        ```

1. Set the API secret and APP_KEY environment variables for your app:

    ```shell
    flyctl secrets set SHOPIFY_API_SECRET=ReplaceWithSECRETFromEnvCommand
    php web/artisan key:generate --show
    flyctl secrets set APP_KEY=ReplaceWithTheValueFromThePreviousCommand
    ```

1. Build and deploy the app - note that you'll need the `SHOPIFY_API_KEY` to pass to the command

    ```shell
    flyctl deploy --build-arg SHOPIFY_API_KEY=ReplaceWithKEYFromEnvCommand --remote-only
    ```

### Update URLs in Partner Dashboard

In the Partner Dashboard, update the main URL for your app to the url from the [fly.io dashboard](https://fly.io/dashboard) and set a callback URL to the same url with `/api/auth/callback` appended to it. Note: this is the same as the `HOST` environment variable set above.

## Deploy a new version of the app

After updating your code with new features and fixes, rebuild and redeploy using:

```shell
flyctl deploy --build-arg SHOPIFY_API_KEY=ReplaceWithKEYFromEnvCommand --remote-only
```

## To scale to multiple regions

1. Add a new region using `flyctl regions add CODE`, where `CODE` is the three-letter code for the region. To obtain a list of regions and code, run `flyctl platform regions`.
1. Scale to two instances - `flyctl scale count 2`
