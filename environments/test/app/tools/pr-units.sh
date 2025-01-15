#!/bin/sh

# 1-report link
# 2-github pr head sha
# 3-token

# Ensure mysql is ready
sleep 10

echo "Preparing a fresh instance of the DB"

/application/artisan migrate:fresh

echo "Running tests with coverage"
APP_ENV=testing phpunit --coverage-clover /var/tmp/phpunit.coverage.xml --log-junit /var/tmp/phpunit.report.xml > /var/tmp/units

if [ ${?} -eq 0 ]
then
    curl -X POST \
          -H "Content-type: application/json" \
          -d "{\"state\": \"success\", \"target_url\": \"${1}\", \"description\": \"Tests Pass\", \"context\": \"phpunit\"}" \
          https://${3}@api.github.com/repos/meetsoci/soci-services/statuses/${2} > /dev/null
    exit 0
else
    cat /var/tmp/units /var/tmp/phpunit.coverage.xml /var/tmp/phpunit.report.xml
    curl -X POST \
          -H "Content-type: application/json" \
          -d "{\"state\": \"failure\", \"target_url\": \"${1}\", \"description\": \"Tests Fail\", \"context\": \"phpunit\"}" \
          https://${3}@api.github.com/repos/meetsoci/soci-services/statuses/${2} > /dev/null
    exit 1
fi
