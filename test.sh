#!/bin/bash

# Assuming FFMPEG_DOCKER set
IMAGE=${IMAGE:-ripfile}

set -ex
curl -L -o test.mpg https://alcorn.com/wp-content/downloads/test-files/AC3AlcornTest_HD.mpg
HWACCEL=n INPUT="test.mpg" TITLE="Test" YEAR="2019" LOG=n ./ripFile.sh 

