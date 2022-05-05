gendiff:
	./bin/gendiff

validate:
	composer validate

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src tests

stan:
	composer exec --verbose phpstan -- --level=4 analyse src tests

lint-fix:
	composer exec --verbose phpcbf -- --standard=PSR12 src tests

docker-lint:
	docker run --rm -v $(PWD):/app -w /app composer:latest make lint

docker-lint-fix:
	docker run --rm -v $(PWD):/app -w /app composer:latest make lint-fix

docker-install:
	docker run --rm -v $(PWD):/app -w /app -u $(id -u) composer:latest composer install

install:
	composer install

test-coverage:
	composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml
