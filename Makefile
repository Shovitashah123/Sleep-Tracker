PORT ?= 8000
HOST ?= localhost

ifeq ($(OS),Windows_NT)
  ifneq ($(wildcard C:/xampp/php/php.exe),)
    PHP ?= C:/xampp/php/php.exe
  else
    PHP ?= php
  endif
else
  PHP ?= php
endif

.PHONY: dev help

dev:
	@echo "SleepTrack on http://$(HOST):$(PORT)"
	@echo "Make sure Apache + MySQL are running in XAMPP."
	$(PHP) -S $(HOST):$(PORT) -t .

help:
	@echo "make dev   - start dev server on http://$(HOST):$(PORT)"
	@echo "Overrides : PORT=8080 HOST=0.0.0.0 PHP=C:/xampp/php/php.exe"
	@echo "Env vars  : DB_HOST DB_PORT DB_USER DB_PASS DB_NAME"
