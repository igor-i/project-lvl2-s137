install:
	composer install

lint:
	composer update
	composer run-script phpcs -- --standard=PSR2 src bin

test:
	composer run-script phpunit tests