.SHELL := /usr/bin/env bash

watch :
	git ls-files app skin | entr sh -c '$(MAKE) install clear-cache setup'
.PHONY : watch

install :
	tar xf $$(./bin/package --dirty) -C www/1944 app skin
.PHONY : install

clear-cache :
	find www/1944/var/cache -type f -delete
.PHONY : clear-cache

setup :
	docker exec -it -w /var/www/localhost/htdocs -u apache magento1-php-1 magerun sys:setup:run
.PHONY : setup
