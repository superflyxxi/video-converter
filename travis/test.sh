#!/bin/bash

# Assuming FFMPEG_DOCKER set

set -ex

. common.sh

cd ../tests/
export TMP_DIR=$(mktemp -d)

if [[ ! -f "${TMP_DIR}/test.mpg" ]]; then
	curl -L -o "${TMP_DIR}/test.mpg" "https://alcorn.com/wp-content/downloads/test-files/AC3AlcornTest_HD.mpg"
fi

find . -name 'test*.php' | xargs -L1 php

