#!/bin/bash

# Assuming FFMPEG_DOCKER set

set -ex

THIS_FULL_IMAGE=${THIS_FULL_IMAGE:?Missing THIS_FULL_IMAGE}
TEST_SAMPLE_DOMAIN=${TEST_SAMPLE_DOMAIN:?Missing TEST_SAMPLE_DOMAIN}

cd tests/
export TMP_DIR=$(mktemp -d)
export LOG_LEVEL=VERBOSE

find . -name 'test*.php' | sort | xargs -L1 php

