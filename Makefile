test:
	export XDEBUG_MODE=coverage && vendor/bin/phpunit --coverage-clover coverage.xml

coverage: test
	./codecov -t ${CODECOV_TOKEN}