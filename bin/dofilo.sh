#!/bin/bash

php bin/console doctrine:schema:drop --force
php bin/console doctrine:query:sql -q "TRUNCATE doctrine_migration_versions"
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction