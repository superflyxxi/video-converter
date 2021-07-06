#!/bin/bash
set -e

if [[ ! -z "${CACHE_FROM_IMAGE}" ]]; then
  CACHE_FROM_ARGS="--cache-from ${CACHE_FROM_IMAGE}"
fi

docker build ${CACHE_FROM_ARGS} --build-arg BUILD_SUBTITLE_SUPPORT=${BUILD_SUBTITLE_SUPPORT:-true} --build-arg FROM_IMAGE=${FROM_IMAGE:?FROM_IMAGE missing} --tag ${THIS_FULL_IMAGE:?THIS_FULL_IMAGE missing} -f Dockerfile.${DOCKERFILE:?DOCKERFILE missing} .
