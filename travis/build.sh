#!/bin/bash
set -ex

. common.sh

cd ../

find . -name *.php | xargs -L1 php -l

CACHE_FROM_ARGS=${CACHE_FROM_ARGS:-"--cache-from ${CACHE_FROM_IMAGE}"}

docker build ${CACHE_FROM_ARGS} --build-arg BUILD_SUBTITLE_CONVERT=${BUILD_SUBTITLE_CONVERT:-true} --build-arg FROM_IMAGE=${FROM_IMAGE} --tag ${THIS_FULL_IMAGE} -f Dockerfile.${THIS_DOCKERFILE} .

