# PHP-PM DrupalKernel Adapter

## Overview

This is a fork of PHP-PM's HttpKernel adapter for integrating Drupal with PHP-PM (therefore, also with ReactPHP).

The primary components are a bootstrap and bridge.

See:
* https://github.com/php-pm/php-pm
* https://github.com/php-pm/php-pm-httpkernel
* http://marcjschmidt.de/blog/2014/02/08/php-high-performance.html

The code is in alpha -- very experimental.  Last tested against `drupal-8.0.2`.

View / report issues at https://github.com/php-pm/php-pm-drupal/issues.

### Setup / Usage

  1. Install Drupal.

  2. From the Drupal web root, install this project with composer: `composer require kentr/php-pm-drupal-adapter`.

  This will also install PHP-PM and the default React <-> Symfony bridge (php-pm/httpkernel-adapter).

  3. Apply these patches to Drupal core:
    * `vendor/kentr/php-pm-drupal-adapter/patches/kentr-allow-repeated-setSitePath-in-DrupalKernel.patch`
    * `vendor/kentr/php-pm-drupal-adapter/patches/stop_using-2505339-24.patch`

  4. Start php-pm with

```bash
<absolute path to web root>/vendor/bin/ppm \
start \
<absolute path to web root> \
--bridge=httpKernel \
--bootstrap=PHPPM\\Bootstraps\\Drupal
```

Example:
```bash
/var/www/html/vendor/bin/ppm \
start \
/var/www/html/ \
--bridge=httpKernel \
--bootstrap=PHPPM\\Bootstraps\\Drupal
```

## DrupalKernel bridge
By default, PHP-PM uses the `\PHPPM\Bridges\HttpKernel` bridge to convert a ReactPHP request into a Symfony request and the Symfony response into a ReactPHP response.

The included `\PHPPM\Bridges\DrupalKernel` bridge extends `\PHPPM\Bridges\HttpKernel` to populate various request meta-variables specified by [CGI/1.1 (RFC 3875)](http://www.faqs.org/rfcs/rfc3875.html).

### Setup / Usage

  1. Install as described above.

  2. Include the environment variables and the `--bridge` option in the php-pm start command.

    Supported environment variables:
    * **SCRIPT_NAME:** '/index.php' to emulate a standard setup where web requests execute Drupal's `index.php` script.
    * **SERVER_NAME:** Your site's server / domain name.  If you're using trusted host settings (`$settings['trusted_host_patterns']` in `settings.php`), this must match one of the trusted hosts.
    * **SERVER_ADDRESS:** IP address of the server.
    * **DOCUMENT_ROOT:** Absolute filepath of the web root directory.

Example:

```bash
SCRIPT_NAME=/index.php \
SERVER_NAME=localhost \
SERVER_ADDRESS=127.0.0.1 \
DOCUMENT_ROOT=/var/www/html \
/var/www/html/vendor/bin/ppm start /var/www/html \
--bridge=PHPPM\\Bridges\\DrupalKernel \
--bootstrap=PHPPM\\Bootstraps\\Drupal
```
