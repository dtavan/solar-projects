#!/bin/bash

set -e # exit when subcommands fail

# Install if first time running
if [[ ! -d vendor ]]; then
    echo "\n=====================\nInstalling Composer dependencies\n\n"
    # This fixes some issues when downloading packages.
    # See https://github.com/composer/composer/issues/7503#issuecomment-458173411
    composer config -g repo.packagist composer https://repo.packagist.org

    composer install --no-scripts --no-suggest
fi

if [[ ! -f database/dev.sqlite ]]; then
    echo "\n=====================\nPerforming first-time database setup\n\n"
    # This file must exist for Laravel to start interacting with it
    touch database/dev.sqlite
    # This is needed for the app to serve requests
    php artisan key:generate
    php artisan config:cache
    # Create all database tables
    php artisan migrate
    # Fill the tables with example data
    php artisan db:seed
fi

# Run local server
php artisan serve --host 0.0.0.0 --port 11111
