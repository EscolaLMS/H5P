# Headless H5P Laravel API

[![swagger](https://img.shields.io/badge/documentation-swagger-green)](https://escolalms.github.io/H5P/)
[![codecov](https://codecov.io/gh/EscolaLMS/H5P/branch/main/graph/badge.svg?token=ci4VPQbrOI)](https://codecov.io/gh/EscolaLMS/H5P)
[![phpunit](https://github.com/EscolaLMS/H5P/actions/workflows/test.yml/badge.svg)](https://github.com/EscolaLMS/Core/actions/workflows/test.yml)
[![downloads](https://img.shields.io/packagist/dt/escolalms/headless-h5p)](https://packagist.org/packages/escolalms/headless-h5p)
[![downloads](https://img.shields.io/packagist/v/escolalms/headless-h5p)](https://packagist.org/packages/escolalms/headless-h5p)
[![downloads](https://img.shields.io/packagist/l/escolalms/headless-h5p)](https://packagist.org/packages/escolalms/headless-h5p)

## Features

The lib allows headlessly

- play all h5p content - exposed all essential data, yet player is needed
- edit all h5p content - exposed all essential data, yet editor is needed
- CRUD libraries
- CRUD content
- upload library from `.h5p` file
- upload content from `.h5p` file

See [Swagger](https://escolalms.github.io/H5P/) documented endpoints.

Some [tests](tests) can also be a great point of start.

To play the content you can use [EscolaLMS H5P Player](https://github.com/EscolaLMS/H5P-player)

## Install

1. `composer require escolalms/headless-h5p`
2. `php artisan migrate`

### Storage links

You need to publish many of files to be availabe as public link.

`php artisan h5p:link` which creates a symbolic link from `storage/app/h5` and `vendor/h5p/h5p-core` and `vendor/h5p/h5p-editor` to be accesible to public, as follows

```
public_path('h5p') => storage_path('app/h5p'),
public_path('h5p-core') => base_path().'vendor/h5p/h5p-core',
public_path('h5p-editor') => base_path().'vendor/h5p/h5p-editor',
```

### Cors

All the endpoints need to be accesible from other domains, so [CORS](https://laravel.com/docs/8.x/routing#cors) must be properlly set.

Except of endpoints assets must expose CORS headers as well. You achive that by setting Apache/Nginx/Caddy/Whatever settings - below is example for Nginx for wildcard global access.

```
location ~* \.(eot|ttf|woff|woff2|jpg|jpeg|gif|png|wav|mp3|mp4|mov|ogg|webv)$ {
    add_header Access-Control-Allow-Origin *;
}
```

### Seeder

You can seed library and content with build-in seeders that are accessible with

- `php artisan h5p:seed` to add just libraries
- `php artisan h5p:seed --addContent` to add content with libraries

## Road map

- caching
- content export
- some transaltions are missing
- casading delete
- sql foreign keys indexing
- clearup task - deleting temp files, marked for delete
- in some contents, js libs content is invalid
