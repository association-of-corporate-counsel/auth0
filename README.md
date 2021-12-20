Drupal 9 Module for Auth0
====

This project is a manual fork of the [auth0-drupal](https://github.com/auth0-community/auth0-drupal) module. It has been
made compatible with Drupal 9 for use within ACC sites. It may, at some point, contain code that will make it
incompatible with sites outside our domain. Please refer to main repo on how to use (or not use) this module. 

This plugin replaces standard Drupal 9 login forms with one powered by Auth0 that enables social, passwordless, and enterprise connection login as well as additional security, multifactor auth, and user statistics.

## Table of Contents

- [Installation](#installation)
- [Getting Started](#getting-started)
- [Contribution](#contribution)
- [Support + Feedback](#support--feedback)
- [Vulnerability Reporting](#vulnerability-reporting)
- [What is Auth0](#what-is-auth0)
- [License](#license)

## Installation

Before you start, **make sure the admin user has a valid email that you own**. This module delegates the site authentication to Auth0. That means that you won't be using the Drupal database to authenticate users (user records will still be created) and the default login box will not be shown. 

There are 2 ways to install this module detailed below. **Please note:** the Auth0 login form will not appear until the module has been configured (see [Getting Started](#getting-started) below).

### Install from Github

Installing from Github requires Composer ([installation instructions](https://getcomposer.org/doc/00-intro.md)).

1. Navigate to your site's modules directory and clone this repo:

```bash
$ cd PATH/TO/DRUPAL/ROOT/modules
$ git clone https://github.com/association-of-corporate-counsel/auth0.git auth0
```

2. Move to the newly-created directory and install the Composer dependencies:

```bash
$ cd auth0
$ composer install
```

3. In Manage > Extend, scroll down to the Auth0 module, click the checkbox, then click **Install**

### Install from Drupal.org with Composer

1. Add the following to the "repositories" portion of your composer.json file

```json
{
    "type": "vcs",
    "url": "https://github.com/association-of-corporate-counsel/auth0"
}
```

2. From the root of your Drupal project run:

```bash
$ composer require acc/auth0_drupal
```

3. In Manage > Extend, scroll down to the Auth0 module, click the checkbox, then click **Install**

## Getting Started

### 1. Configure your Auth0 Application

Once the module is installed, you'll need to create an Application for your Drupal site in the Auth0 dashboard. 

1. If you haven't already, [sign up for a free Auth0 account here](https://auth0.com/signup).
2. Go to Applications and click **Create Application** on the top right. 
3. Give your Application a name, click **Regular Web Application**, the **Create**.
4. Click the **Settings** tab at the top.
5. In the "Allowed Callback URLs" field, add your site's homepage with a path of `/auth0/callback` like:

```
https://yourdomain.com/auth0/callback
```

6. In the "Allowed Web Origins," "Allowed Logout URLs," and "Allowed Origins (CORS)" fields, add the domain of your Drupal site including the protocol but without a trailing slash like:

```
https://yourdomain.com
```

7. Scroll down and click **Save Changes**.

Leave this tab open to copy the configuration needed in the next section. 

### 2. Configure the Auth0 module

1. Go to Manage > Configuration > System > Auth0
2. Under the **Settings** tab, copy and paste the values for the 3 required fields from the Application settings screen in the Auth0 dashboard. You can also save dummy values and override them using Drupal's built in config override system ([explained here](https://www.drupal.org/docs/8/api/configuration-api/configuration-override-system)) in your `settings.php` file:

```php
$config['auth0.settings']['auth0_client_id'] = getenv('AUTH0_CLIENT_ID');
$config['auth0.settings']['auth0_client_secret'] = getenv('AUTH0_CLIENT_SECRET');
$config['auth0.settings']['auth0_domain'] = getenv('AUTH0_DOMAIN');
```

4. Click **Save** and your Auth0 login form should now be showing on the login page. To test this, open a new browser (or private/incognito window) and navigate to your login page at `/user/login`.

### 3. Advanced configuration

Under the **Advanced** tab in the same settings screen, you can configure the following:

- **Form title:** Change the title on the Auth0 login form.
- **Allow user signup:** Include the Sign Up tab on the Auth0 login form.
- **Send a Refresh Token:** Include a refresh token in the returned profile data from Auth0 when logging in.
- **Redirect login for SSO:** Use the Universal Login Page to enable SSO. You'll need to add your Drupal site home page to the Tenant Settings > Advanced > "Allowed Logout URLs" field if this is enabled. 
- **Widget CDN:** URL to the Lock JS file in the Auth0 CDN. In general, this should not be changed unless upgrading to a new version or if instructed by Auth0 support. 
- **Requires verified email:** Enable this setting to require Auth0 users to have a verified email in order to login. Please note that this will prevent users without email addresses (e.g. Twitter users) from logging in.
- **Link Auth0 logins to Drupal users:** Enabling this setting will match incoming successful logins to a Drupal user using their email address.
- **Map Auth0 claim to Drupal user name:** This will use the specified ID token claim to set the Drupal username. 
- **Login widget css:** Additional CSS to output on the login page. 
- **Lock extra settings:** Additional settings to change the Auth0 login form's behavior and appearance. [See here for more details and examples](https://auth0.com/docs/libraries/lock/v11/configuration). 
- **Auto Register Auth0 users:** This will register new Drupal users from incoming Auth0 ones, even if registration on your site is off. This makes it possible to create a user in Auth0 but not in Drupal and have a new Drupal user created when they log in.
- **Mapping of Claims to Profile Fields:** Follow the directions below this field to save incoming Auth0 ID token claims as Drupal profile fields.
- **Claim for Role Mapping:** Name of the ID token claim to map incoming data from Auth0 to Drupal roles.  
- **Mapping of Claim Role Values:** Follow the directions below this field to set Drupal roles for users based on incoming Auth0 data.

## Contribution, Support, Feedback, and Vulnerability Reporting

Please refer to the parent repo ([auth0-drupal](https://github.com/auth0-community/auth0-drupal)).

## What is Auth0?

Auth0 helps you to easily:

- implement authentication with multiple identity providers, including social (e.g., Google, Facebook, Microsoft, LinkedIn, GitHub, Twitter, etc), or enterprise (e.g., Windows Azure AD, Google Apps, Active Directory, ADFS, SAML, etc.)
- log in users with username/password databases, passwordless, or multi-factor authentication
- link multiple user accounts together
- generate signed JSON Web Tokens to authorize your API calls and flow the user identity securely
- access demographics and analytics detailing how, when, and where users are logging in
- enrich user profiles from other data sources using customizable JavaScript rules

[Why Auth0?](https://auth0.com/why-auth0)

## License

The Drupal Module for Auth0 is licensed under MIT - [LICENSE](LICENSE)