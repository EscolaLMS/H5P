#!/bin/sh
docker compose up -d
docker compose exec escola_lms_app composer update
docker compose exec escola_lms_app vendor/bin/testbench config:clear
docker compose exec escola_lms_app vendor/bin/testbench migrate:fresh 
docker compose exec escola_lms_app vendor/bin/phpunit
docker compose down