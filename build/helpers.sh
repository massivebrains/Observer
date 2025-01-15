#!/bin/sh

export AWS_ACCOUNT="058372639833"

read_aws_credentials_from_file() {
  export AWS_ACCESS_KEY_ID=$( cat "$1" | jq -r ".services.default_settings.params.key" )
  export AWS_SECRET_ACCESS_KEY=$( cat "$1" | jq -r ".services.default_settings.params.secret" )
  export AWS_DEFAULT_REGION="us-east-1"
  export AWS_SESSION_TOKEN=""
}

echo_hints() {
  echo "Set your AWS credentials by running 'aws configure' or setting AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY env vars or providing full path to a valid auth_config.json settings file as an argument to this script."
}

check_prerequisites() {
  which aws >/dev/null

  if [ $? -gt 0 ]; then
    echo "AWS CLI tool not found, please install it and try again."
    echo "It should be available in your local package manager or you can download it from https://aws.amazon.com/cli/"
    exit 1
  fi

  which docker >/dev/null

  if [ $? -gt 0 ]; then
    echo "docker binary not found, please install Docker and try again."
    echo "You can download it from https://www.docker.com/"
    exit 1
  fi
}

get_arch() {
  ARCH=$( uname -m )

  case $ARCH in
    amd64) ARCH="amd64" ;;
    x86_64) ARCH="amd64" ;;
    arm64) ARCH="arm64" ;;
    aarch64) ARCH="arm64" ;;
    *) echo "Unsupported architecture: ${ARCH}" && exit 1 ;;
  esac

  echo "$ARCH"
}

get_aws_identity() {
  aws --output json sts get-caller-identity 2>&1
}

check_aws_identity() {
  AUTH_RESULT=$( get_aws_identity )

  if [ $? -gt 0 ]; then
    echo "Unable to determine your AWS identity, run 'aws configure' or set AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY env vars."
    echo "Error: ${AUTH_RESULT}"
    exit 2
  fi
}

attempt_ecr_login() {
  IDENTITY=$( aws --output json sts get-caller-identity 2>&1 )

  if [ $? -gt 0 ]; then
    return 1
  fi

  IDENTITY_ACCOUNT=$( echo "${IDENTITY}" | jq -r '.Account' )

  if [ "${AWS_ACCOUNT}" != "${IDENTITY_ACCOUNT}" ]; then
    echo "The current AWS account ${IDENTITY_ACCOUNT} does not match the expected ${AWS_ACCOUNT}"
    return 1
  fi

  aws ecr get-login-password --region=us-east-1 >/dev/null 2>&1
}

check_aws_identity_with_discovery() {
  if [ "$1" ]; then
    if [ ! -f "$1" ]; then
      echo "The $1 file doesn't exist"
      exit 1
    fi

    echo "Setting AWS credentials from file $1"
    read_aws_credentials_from_file "$1"
  fi

  attempt_ecr_login

  if [ $? -gt 0 ]; then
    echo "Unauthorized response, trying to find your AWS settings file..."

    FILES=$( find ../../.. -type f -size +0 -name auth_config.json -print )
    VALID_FILE=""

    if [ -z "${FILES}" ]; then
      echo "No AWS settings file found!"
      echo_hints
      exit 1
    fi

    for file in $FILES; do
      echo "Trying ${file}..."

      read_aws_credentials_from_file "${file}"
      attempt_ecr_login

      if [ $? -eq 0 ]; then
        echo "Success!"
        VALID_FILE=$file
        break;
      else
        echo "Unable to authenticate with ECR, probably wrong file found."
        continue;
      fi
    done

    if [ -z "${VALID_FILE}" ]; then
      echo "No file with valid AWS credentials found."
      echo_hints
      exit 2
    fi
  fi
}

docker_login() {
    aws ecr get-login-password --region us-east-1 | docker login --username AWS --password-stdin 058372639833.dkr.ecr.us-east-1.amazonaws.com

    if [ $? -ne 0 ]; then
      echo "Unable to login to Docker"
      exit 3
    fi
}

remove_image() {
  echo "Removing image $1..."
  docker images -q "$1" | uniq | xargs -r docker rmi -f
}

echo_green() {
  echo -e "\e[1;32m${1}\e[0m"
}

echo_red() {
  echo -e "\e[1;31m${1}\e[0m"
}
