gendiff:
	./bin/gendiff

validate:
	composer validate

lint:
	composer run-script phpcs -- --standard=PSR12 src bin

docker-lint:
	docker run --rm -v $(PWD):/app -w /app composer:latest make lint

docker-install:
	docker run --rm -v $(PWD):/app -w /app -u $(id -u) composer:latest composer install

install:
	composer install

test-coverage:
	composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml
