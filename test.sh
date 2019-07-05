#!/bin/bash

# Assuming FFMPEG_DOCKER set

set -ex

. common.sh

if [[ ! -f test.mpg ]]; then
	curl -L -o test.mpg https://alcorn.com/wp-content/downloads/test-files/AC3AlcornTest_HD.mpg
fi


docker run --rm -it -v `pwd`:/data -e INPUT=test.mpg -e SLEEP=1s -e HWACCEL=n -e TITLE="Test Title" -e YEAR=2019 ${THIS_REGISTRY}/${THIS_REPO}/${THIS_IMAGE}:${THIS_LABEL} 
#SLEEP=1s HWACCEL=n INPUT=test.mpg TITLE="Test Title" YEAR=2019 DOCKER_DAEMON=n ./ripFile.sh 
#docker rm test.mpg

#SLEEP=1s HWACCEL=n INPUT=test.mpg TITLE="Test Title" YEAR=2019 DOCKER_DAEMON=n AUDIO_FORMAT=copy ./ripFile.sh 
#docker rm test.mpg

