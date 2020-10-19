install:
	composer install

test: ## launch complete test
	vendor/bin/php-cs-fixer fix --dry-run --stop-on-violation --diff
	vendor/bin/phpunit
	vendor/bin/phpstan analyse src tests --memory-limit 1G

test-list: ## List PhpUnit Test groups
	vendor/bin/phpunit --list-groups

ci-cs: ## Checks for cs-fixer errors locally
	vendor/bin/php-cs-fixer fix --allow-risky yes --dry-run --verbose

ci-analyse: ## Checks for phpstan errors locally
	vendor/bin/phpstan analyse src tests --level max

ci-lint: ## Checks for syntax errors
	find src/ tests/ -name "*.php" -print0 | xargs -0 -n1 php -l

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help
