#!/bin/sh

# 1-report link
# 2-github pr head sha
# 3-token

# Ensure mysql is ready
sleep 10

php /pr_tools/run_phpunit_coverage_test.php coverage_path=/var/tmp/phpunit.coverage.xml output_path=/var/tmp/output.txt badly_united_files_path=/pr_tools/badly_united_files.txt

if [ ${?} -eq 0 ]
then
	curl -X POST \
		-H "Content-type: application/json" \
		-d "{\"state\": \"success\", \"target_url\": \"${1}\", \"description\": \"Coverage pass\", \"context\": \"coverage\"}" \
		https://${3}@api.github.com/repos/meetsoci/soci-services/statuses/${2} > /dev/null
	exit 0
else
	cat /var/tmp/output.txt
	curl -X POST \
		-H "Content-type: application/json" \
		-d "{\"state\": \"failure\", \"target_url\": \"${1}\", \"description\": \"Coverage Fail\", \"context\": \"coverage\"}" \
		https://${3}@api.github.com/repos/meetsoci/soci-services/statuses/${2} > /dev/null
	exit 1
fi
