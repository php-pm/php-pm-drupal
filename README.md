# PHP-PM HttpKernel Adapter

This is a fork of the HttpKernel adapter for use of Symfony and Laravel frameworks with PHP-PM. See https://github.com/php-pm/php-pm to provide a bootstrap & bridge for Drupal.

The code is in pre-alpha -- very experimental.

### Setup

  1. Install PHP-PM as described in the project docs.

  2. Replace the `vendor/php-pm/httpkernel-adapter` directory with this code.

  3. Apply `kentr-allow-repeated-setSitePath-in-DrupalKernel.patch` to Drupal core.

  4. Start php-pm with `sudo vendor/bin/ppm start /var/www/html/ --bridge=httpKernel --bootstrap=PHPPM\\Bootstraps\\Drupal`.
