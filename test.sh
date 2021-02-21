#!/bin/bash

# Assuming FFMPEG_DOCKER set

set -e

THIS_FULL_IMAGE=${THIS_FULL_IMAGE:?Missing THIS_FULL_IMAGE}

cd tests/
docker build --tag test --build-arg BUILD_IMAGE=${THIS_FULL_IMAGE} .
docker run --rm -e LOG_LEVEL=VERBOSE -e BUILD_SUBTITLE_SUPPORT=${BUILD_SUBTITLE_SUPPORT} -e TEST_SAMPLE_DOMAIN=${TEST_SAMPLE_DOMAIN} test
