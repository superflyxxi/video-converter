#!/bin/bash

# Assuming FFMPEG_DOCKER set

set -ex
echo "date.timezone = UTC" >> /etc/php.ini

. common.sh

cd ../tests/
export TMP_DIR=$(mktemp -d)
export LOG_LEVEL=VERBOSE

if [[ ! -f "${TMP_DIR}/test.mpg" ]]; then
	curl -L -o "${TMP_DIR}/test.mpg" "https://alcorn.com/wp-content/downloads/test-files/AC3AlcornTest_HD.mpg"
fi

find . -name 'test*.php' | sort | xargs -L1 php

