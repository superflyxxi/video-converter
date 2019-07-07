#!/bin/bash

# Assuming FFMPEG_DOCKER set

set -ex

. common.sh

cd ../tests/

if [[ ! -f test.mpg ]]; then
	curl -L -o test.mpg https://alcorn.com/wp-content/downloads/test-files/AC3AlcornTest_HD.mpg
fi

find . -name 'test*.php' | xargs -L1 php

