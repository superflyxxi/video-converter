#!/bin/bash

# Assuming FFMPEG_DOCKER set

set -ex

. common.sh

cd ../tests/

if [[ ! -f test.mpg ]]; then
	curl -L -o test.mpg https://alcorn.com/wp-content/downloads/test-files/AC3AlcornTest_HD.mpg
fi

# Test basic scneario
docker run --rm -it --user=${UID} -v `pwd`:/data -e INPUT=test.mpg -e TITLE="Test default" -e YEAR=2019 ${THIS_REGISTRY}/${THIS_REPO}/${THIS_IMAGE}:${THIS_LABEL} 
if [[ ! -f "./Test default (2019).ffmpeg.mkv" ]]; then
	echo "File not created"
	exit 1
fi

# Test copy audio
docker run --rm -it --user=${UID} -v `pwd`:/data -e INPUT=test.mpg -e AUDIO_FORMAT=copy -e TITLE="Test Audio Copy" -e YEAR=2019 ${THIS_REGISTRY}/${THIS_REPO}/${THIS_IMAGE}:${THIS_LABEL} 
if [[ ! -f "./Test Audio Copy (2019).ffmpeg.mkv" ]]; then
	echo "File not created"
	exit 1
fi

# Test copy video
docker run --rm -it --user=${UID} -v `pwd`:/data -e INPUT=test.mpg -e VIDEO_FORMAT=copy -e TITLE="Test Video Copy" -e YEAR=2019 ${THIS_REGISTRY}/${THIS_REPO}/${THIS_IMAGE}:${THIS_LABEL} 
if [[ ! -f "./Test Video Copy (2019).ffmpeg.mkv" ]]; then
	echo "File not created"
	exit 1
fi

find . -name *.php | xargs -L1 php

