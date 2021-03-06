#!/bin/bash

HTTPDUSER=www-data

cd /srv/fsgt/site && \
    setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var && \
    setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var && \
    git fetch && git reset --hard origin/HEAD && \
    cp /home/bap/fsgt/app/config/parameters.yml /srv/fsgt/site/app/config/parameters.yml && \
    cd /home/bap/fsgt && \
    docker-compose exec php-fpm composer install && \
    docker-compose exec php-fpm php bin/console cache:clear --no-warmup --env=prod && \
    docker-compose exec php-fpm php bin/console cache:warmup --env=prod && \
    docker-compose exec php-fpm php bin/console doctrine:migrations:migrate --allow-no-migration && \
    docker-compose exec php-fpm chown www-data:www-data -R /app/var
