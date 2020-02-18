CONTAINER_APP= docker-compose exec app

composer-install:
	${CONTAINER_APP} composer install

yarn-install:
	${CONTAINER_APP} yarn install

create-db:
	${CONTAINER_APP} bin/console doctrine:database:create

create-tables:
	${CONTAINER_APP} bin/console doctrine:schema:update --force

create-fixtures:
	${CONTAINER_APP} bin/console doctrine:fixtures:load

print-routes:
	${CONTAINER_APP} bin/console debug:router

assets-build:
	${CONTAINER_APP} npm run build

assets-watch:
	${CONTAINER_APP} npm run watch

doctrine-migration:
	${CONTAINER_APP} bin/console make:migration

doctrine-migrate:
	${CONTAINER_APP} bin/console doctrine:migration:migrate

phpunit:
	${CONTAINER_APP} ./vendor/bin/simple-phpunit --testdox --testsuite tests-api
