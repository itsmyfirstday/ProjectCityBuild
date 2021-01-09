.DEFAULT_GOAL := help

SHELL=/bin/bash

.PHONY: help
help:
	@echo ""
	@echo "Available tasks:"
	@echo "    artisan              Runs an artisan command via the php-fpm container"
	@echo "    bootstrap            Prepares your environment for first-time use"
	@echo "    cert                 Generates a SSL certificate for use with local dev"
	@echo "    shell                Enters the php-fpm container (with Sh shell)"
	@echo "    stripe payment       Creates a (test) Stripe payment and forwards the event to our webhook API"
	@echo "    test                 Runs phpunit tests in the php-fpm container"
	@echo "    watch                Runs browsersync with hotloading and file watching"
	@echo ""

.PHONY: artisan
artisan:
	@./scripts/artisan.sh $(filter-out $@,$(MAKECMDGOALS))

.PHONY: bootstrap
bootstrap:
	@./scripts/bootstrap.sh

.PHONY: cert
cert:
	@./scripts/generate-cert.sh

.PHONY: shell
shell:
	@./scripts/shell.sh

.PHONY: stripe
stripe:
	@./scripts/stripe.sh $(filter-out $@,$(MAKECMDGOALS))

.PHONY: test
test:
	@./scripts/test.sh

.PHONY: watch
watch:
	@./scripts/browsersync.sh

# Filter out any unrecognised commands or arguments
%:
	@:
