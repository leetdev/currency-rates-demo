# CurrencyRates Demo App

A Laravel application that demonstrates the [CurrencyRates](https://github.com/ultraleettech/currency-rates) library functionality.

## Features

- Google Sign-In registration/login using [Laravel Socialite](https://github.com/laravel/socialite)
- Management of favourite currency pairs for historical exchange rate analysis
- Historical data displayed as a table and a Google Chart using [Lavacharts](http://lavacharts.com/)

## Installation

To get started, clone this repository, change to its root directory using your favourite command prompt, and run

    composer install

### Web Server

After installation, you need to set up your web server to serve this app from its `public` directory. Alternatively, you can use PHP's built-in development server to serve the application, by issuing the `php artisan serve` command. This will start a server at `http://localhost:8000`.

### .env

Next, make a copy of `.env.example` and name it `.env`. Then open it in your favourite editor. Feel free to edit the first section to your liking (namely the APP_NAME, APP_ENV, APP_DEBUG, and APP_LOG_LEVEL variables). You will also want to set the APP_URL to reflect the base path of the virtual host you set up in the previous step.

Also, you should set the APP_KEY variable to ensure securely encrypted user sessions. You can generate one by issuing the `php artisan key:generate --show` command.

### Directory Permissions

Depending on your OS, its security setup, and your installation directory, you might need to configure some permissions.  Directories within the `storage` and the `bootstrap/cache` directories should be writable by your web server or this app will not run.

### Configuration

Application specific configuration can be found in `config/app.php`. Scroll down to the *Custom Configuration* section. Reasonable defaults have been selected for you, but you might want to tweak some of the settings.

### Database

Create a new database for the app. We have tested with MySQL and Postgres, but technically you should be able to use anything that is supported by the Laravel framework. After creating the database, update the `DB_*` variables in your `.env` file to reflect the connection type and credentials.

Next, run the `php artisan migrate` command from the command prompt. This will generate all the tables needed to house application data. If this completes successfully, you can run `php artisan currency:cache`. This will fetch the currency rates for the maximum configured period (default is 52 weeks) of historical data and cache the results in the database.

### Authentication API

To enable Google Sign-In authentication, you will need to create the necessary API credentials. Read [this guide](https://developers.google.com/+/web/api/rest/oauth) for detailed instructions. When creating credentials, you will need to select *Oauth client ID*, and then *Web application* as the type. When entering restrictions, add the base URL of your application under *Authorised JavaScript origins*, and `[base URL]/login/google/callback` under *Authorised redirect URIs*.

When this is done, update your `.env` file to reflect your client ID, secret, and callback URL.

### All Done!

The application should now be fully set up and ready to use! Simply point your browser to its base URL. Its operation should be fairly straight forward, so we will not get into any additional details here.

## Extending

### Authentication Providers

This application uses Laravel Socialite for managing authentication. It provides some of the most common adapters (Facebook, Twitter, Google, LinkedIn, GitHub and Bitbucket) out of the box. In addition, there is a community driven [Socialite Providers](https://socialiteproviders.github.io/) website that provides many more. Follow the instructions there to install any adapter you need, or in fact create a custom one.

To set up the app to support other providers, follow these simple steps:

1. Edit `config/services.php` and duplicate the 'google' array. Replace the word 'google' with the appropriate name.
2. Do the same in your `.env` file after setting up your API credentials at the provider's website. Set these variable to the appropriate values.
3. Edit `resources/views/login.blade.php`. Duplicate the `<li>` element in there and edit as needed.

That's it! You should now be able to register/login using the new provider.

### Currency Exchange Rate Providers

To add custom providers, follow [these instructions](https://github.com/ultraleettech/currency-rates#custom-providers).

To set the API currently being used by this application, simply update the 'currency_api' variable in `config/app.php`.
