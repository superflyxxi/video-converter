#!/bin/bash

# Assuming FFMPEG_DOCKER set

set -ex
if [[ ! -f test.mpg ]]; then
	curl -L -o test.mpg https://alcorn.com/wp-content/downloads/test-files/AC3AlcornTest_HD.mpg
fi
HWACCEL=n INPUT=test.mpg TITLE="Test Title" YEAR=2019 DOCKER_DAEMON=n ./ripFile.sh 

