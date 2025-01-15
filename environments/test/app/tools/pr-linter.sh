#!/bin/sh

# 1-report link
# 2-github pr head sha
# 3-token

php ./vendor/bin/phpcs --report-code=/var/tmp/linter

if [ ${?} -eq 0 ]
then
    curl -X POST \
	 -H "Content-type: application/json" \
	 -d "{\"state\": \"success\", \"target_url\": \"${1}\", \"description\": \"PHP Linter Pass\", \"context\": \"linter\"}" \
	 https://${3}@api.github.com/repos/meetsoci/soci-services/statuses/${2} > /dev/null
    exit 0
else
    curl -X POST \
	 -H "Content-type: application/json" \
	 -d "{\"state\": \"failure\", \"target_url\": \"${1}\", \"description\": \"PHP Linter Fail\", \"context\": \"linter\"}" \
	 https://${3}@api.github.com/repos/meetsoci/soci-services/statuses/${2} > /dev/null
    exit 1
fi
