#!/bin/bash
set -ex

. common.sh

cd ../

find . -name *.php | xargs -L1 php -l

CACHE_FROM_ARGS=${CACHE_FROM_ARGS:-"--cache-from ${THIS_REGISTRY}/${THIS_REPO}/${THIS_IMAGE}:latest"}

docker build ${CACHE_FROM_ARGS} --build-arg FROM_IMAGE=${FROM_IMAGE} --tag ${THIS_REGISTRY}/${THIS_REPO}/${THIS_IMAGE}:${THIS_LABEL} -f Dockerfile.${FFMPEG_DOCKER_LABEL}

