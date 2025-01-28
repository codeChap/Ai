#!/bin/bash

# Disable all deprecation notices
export COMPOSER_DISABLE_DEPRECATION_NOTICES=1
export PHP_DEPRECATION_ERRORS=0

# Run PHPUnit with error reporting configured
php -d error_reporting='E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED' vendor/bin/phpunit "$@" 