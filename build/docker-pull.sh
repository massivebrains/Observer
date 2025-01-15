
# Pulls the latest pre-built dev image from the ECR registry. You will receive an image for
# the appropriate architecture (amd64 or arm64) automatically.

. ./helpers.sh

check_prerequisites

AWS_SETTINGS_FILE=""

for i in "$@"; do
  case $i in
    --no-delete) DELETE_EXISTING="" && shift ;;
    *) AWS_SETTINGS_FILE=$i ;;
  esac
done

echo "Checking your AWS identity"

check_aws_identity_with_discovery "${AWS_SETTINGS_FILE}"

echo "Logging in to Docker"

docker_login

echo "Pulling latest images"

set -x

docker pull 058372639833.dkr.ecr.us-east-1.amazonaws.com/soci-images/php:8.3-fpm-alpine3.19