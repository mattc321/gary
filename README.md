# gary
evergreen certified mgmt sys D11/Symf/node

## Download the repo

## Requirements
- node v10.15.3
- composer 2
- php 8.4 & php-fpm
- nginx
- mysql 8

## Install composer dependencies
```shell
cd drupal
composer install
```

## Install node dependencies and build front end files
```
cd ../
npm install
npm run build
```

## setup your mysql

## Setup configuration files
- drupal/web/sites/default/settings.php
- drupal/web/sites/default/settings.local.php


## Setup Nginx Config
- .nginx.conf.sample
