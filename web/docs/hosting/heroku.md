# Hosting on Heroku

> Note: this deployment to Heroku relies on `git` so your app will need to be committed to a `git` repository. If you haven't done so yet, run the following commands to initialize and commit your source code to a `git` repository:

```shell
# be at the top-level of your app directory
git init
git add .
git commit -m "Initial version"
```

## Create and login to a Heroku account

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

## Build and deploy from Git repo

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

## Update URLs in Partner Dashboard

<table>
  <tr>
    <td>8.</td>
    <td>Update main and callback URLs in Partner Dashboard to point to new app.  The main app URL should point to <br><code>https://my-app-name.herokuapp.com</code><br> and the callback URL should be<br><code>https://my-app-name.herokuapp.com/api/auth/callback</code></td>
  </tr>
</table>

## Test the app

<table>
  <tr>
    <td>9.</td>
    <td>Open the deployed app by browsing to<br><code>https://my-app-name.herokuapp.com/api/auth?shop=my-dev-shop-name.myshopify.com</code></td>
  </tr>
</table>

## Deploy a new version of the app

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
