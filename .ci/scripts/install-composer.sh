#!/usr/bin/env sh
set -xueo pipefail

export COMPOSER_ALLOW_SUPERUSER=1
export COMPOSER_HOME=/tmp
export COMPOSER_VERSION=2.0.13

## See https://github.com/composer/docker/blob/0dbcef7c67fcf1ce30c66ff42759f6af58b4960c/1.9/Dockerfile
curl --silent --fail --location --retry 3 --output /tmp/installer.php --url https://raw.githubusercontent.com/composer/getcomposer.org/cb19f2aa3aeaa2006c0cd69a7ef011eb31463067/web/installer; \
php -r " \
\$signature = '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5'; \
\$hash = hash('sha384', file_get_contents('/tmp/installer.php')); \
if (!hash_equals(\$signature, \$hash)) { \
    unlink('/tmp/installer.php'); \
    echo 'Integrity check failed, installer is either corrupt or worse.' . PHP_EOL; \
    exit(1); \
}"; \
php /tmp/installer.php --no-ansi --install-dir=. --filename=composer --version=${COMPOSER_VERSION}; \
composer --ansi --version --no-interaction; \
rm -f /tmp/installer.php

## Install dependencies
composer require --no-progress --no-ansi --dev brianium/paratest
