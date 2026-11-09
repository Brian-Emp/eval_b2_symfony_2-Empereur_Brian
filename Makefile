tests:
	php bin/console doctrine:database:drop --force --env=test || true
	php bin/console doctrine:migrations:migrate -n --env=test
	php bin/console doctrine:fixtures:load -n --env=test
	php bin/phpunit $(MAKECMDGOALS)

.PHONY: tests
