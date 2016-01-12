# PHP-PM HttpKernel Adapter

This is a fork of PHP-PM's HttpKernel adapter for integrating Drupal with PHP-PM (therefore, also with ReactPHP). See https://github.com/php-pm/php-pm, https://github.com/php-pm/php-pm-httpkernel.

The code is in pre-alpha -- very experimental.  Last tested against `drupal-8.0.0-rc1`.

### Setup

  1. Install PHP-PM as described in the project docs.

  2. Replace the `vendor/php-pm/httpkernel-adapter` directory with this code.

  3. Apply `kentr-allow-repeated-setSitePath-in-DrupalKernel.patch` to Drupal core.

  4. Start php-pm with `sudo vendor/bin/ppm start /var/www/html/ --bridge=httpKernel --bootstrap=PHPPM\\Bootstraps\\Drupal`.
