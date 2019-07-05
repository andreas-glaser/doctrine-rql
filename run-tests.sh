#!/usr/bin/env bash

cd "$(dirname "$(readlink -f "$0")")"

[[ ! -d "./bin" ]] || [[ ! -d "./vendor" ]]; {
  composer install
}

./bin/phpunit --stop-on-error --stop-on-failure $@