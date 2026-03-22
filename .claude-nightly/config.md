# Nightly Configuration for MyExpenses

## Enabled Tasks
- unit_tests: true
- playwright: true
- cleanup: true

## Unit Tests
Command: php vendor/bin/phpunit --testdox
On failure: email

## Playwright
Command: npx playwright test
On failure: email

## Cleanup
- Clear storage/logs/*.log files older than 30 days
- Clear storage/cache/*
