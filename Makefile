##
## Quality assurance
## -----------------
##
tests: ## Launch PHPUnit tests
	docker run -e "PHP_XDEBUG=1" -e "PHP_XDEBUG_DEFAULT_ENABLE" -e "PHP_XDEBUG_REMOTE_HOST=host.docker.internal" -v "$(PWD)":/code --network="bridge" --rm wodby/php:7.2 sh -c "cd /code; composer install --dev; ./vendor/bin/simple-phpunit"

tests-debug: ## Launch PHPUnit tests with XDebug enabled
	docker run -e "PHP_XDEBUG=1" -e "PHP_XDEBUG_DEFAULT_ENABLE" -e "PHP_XDEBUG_REMOTE_HOST=host.docker.internal" -v "$(PWD)":/code --network="bridge" --rm wodby/php:7.2 sh -c "cd /code; composer install --dev; ./vendor/bin/simple-phpunit"

quality-gate: ## Launch PHP code sniffer and PHPMD
	docker run -v "$(PWD)":/code --rm niji/php-quality-tools:latest phpcs --standard=phpcs.xml.dist
	docker run -v "$(PWD)":/code --rm niji/php-quality-tools:latest phpmd --exclude vendor/*,tests/* --suffixes php ./ text ./phpmd.xml.dist

.PHONY: tests quality-gate

.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help