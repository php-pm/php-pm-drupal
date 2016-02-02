# PHP-PM HttpKernel Adapter

This is a fork of PHP-PM's HttpKernel adapter for integrating Drupal with PHP-PM (therefore, also with ReactPHP).

See https://github.com/php-pm/php-pm, https://github.com/php-pm/php-pm-httpkernel.

The code is in alpha -- very experimental.  Last tested against `drupal-8.0.2`.

View / report issues at https://github.com/kentr/php-pm-drupal/issues.

### Setup

  1. Install Drupal.

  2. From the Drupal web root, install this project with composer: `composer require kentr/php-pm-drupal-adapter`.  This will also install PHP-PM and the default React <-> Symfony bridge (php-pm/httpkernel-adapter).

  3. Apply these patches to Drupal core:
    * `vendor/kentr/php-pm-drupal-adapter/patches/kentr-allow-repeated-setSitePath-in-DrupalKernel.patch`
    * `vendor/kentr/php-pm-drupal-adapter/patches/stop_using-2505339-24.patch`

  4. Start php-pm with `sudo <absolute path to web root>/vendor/bin/ppm start <absolute path to web root> --bridge=httpKernel --bootstrap=PHPPM\\Bootstraps\\Drupal`.  Example: `sudo vendor/bin/ppm start /var/www/html/ --bridge=httpKernel --bootstrap=PHPPM\\Bootstraps\\Drupal`
