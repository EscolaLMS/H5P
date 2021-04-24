# Headless H5P Laravel API

[![codecov](https://codecov.io/gh/EscolaLMS/H5P/branch/main/graph/badge.svg?token=ci4VPQbrOI)](https://codecov.io/gh/EscolaLMS/H5P)
[![phpunit](https://github.com/EscolaLMS/H5P/actions/workflows/test.yml/badge.svg)](https://github.com/EscolaLMS/Core/actions/workflows/test.yml)
[![downloads](https://img.shields.io/packagist/dt/escolalms/headless-h5p)](https://packagist.org/packages/escolalms/headless-h5p)
[![downloads](https://img.shields.io/packagist/v/escolalms/headless-h5p)](https://packagist.org/packages/escolalms/headless-h5p)
[![downloads](https://img.shields.io/packagist/l/escolalms/headless-h5p)](https://packagist.org/packages/escolalms/headless-h5p)

## Features

The lib allows to

- play all h5p content - exposed all essential data, yet player is needed
- edit all h5p content - exposed all essential data, yet editor is needed
- CRUD libraries
- CRUD content
- upload library from `.h5p` file
- upload content from `.h5p` file

See swagger [Swagger](https://escolalms.github.io/H5P/) endpoints.

## Install

1. `composer require escolalms/laravel-headless-h5p`
2. `php artisan migrate`

### Storage links

You need to publish many of files to be availabe as public link.

`php artisan h5p:link` which creates a symbolic link from `storage/app/h5` and `vendor/h5p/h5p-core` and `vendor/h5p/h5p-editor` to be accesible to public.

### Cors

All the endpoints need to be accesible from other domains, so [CORS](https://laravel.com/docs/8.x/routing#cors) must be properlly set.

Except of endpoints assets must expose CORS headers as well. You achive that by setting Apache/Nginx/Caddy/Whatever settings - below is example for Nginx for wildcard global access.

```
location ~* \.(eot|ttf|woff|woff2|jpg|jpeg|gif|png|wav|mp3|mp4|mov|ogg|webv)$ {
    add_header Access-Control-Allow-Origin *;
}
```

### Seeder

you can seed library and content with build-in seeders that are accessible with

- `php artisan h5p:library-seed`
- `php artisan h5p:content-seed`
