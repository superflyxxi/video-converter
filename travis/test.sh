#!/bin/bash

# Assuming FFMPEG_DOCKER set

set -ex

. common.sh

cd ../tests/
export TMP_DIR=$(mktemp -d)
export LOG_LEVEL=VERBOSE

find . -name 'test*.php' | sort | xargs -L1 php

