#!/usr/bin/env bash
set -euo pipefail

echo "[1/2] Running PHP test suite via PHPUnit..."
composer install --no-interaction --no-progress
vendor/bin/phpunit

echo "[2/2] Running JavaScript test suite via Jest..."
npm install --no-progress
npm run test:jest
