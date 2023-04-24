watch :
	git ls-files app skin | entr sh -c '$(MAKE) install && $(MAKE) clear-cache'
.PHONY : watch

install :
	rsync -rlci app skin www/1944
.PHONY : install

clear-cache :
	find www/1944/var/cache -type f -delete
.PHONY : clear-cache
