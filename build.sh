#!/bin/bash

# Assuming FFMPEG_DOCKER set
IMAGE=${IMAGE:-ripfile}

set -ex
cp Dockerfile .dockerfile.tmp
sed -i "s#{FROM_IMAGE}#${FFMPEG_DOCKER}#g" .dockerfile.tmp
cat .dockerfile.tmp
docker build --tag ${IMAGE} -f .dockerfile.tmp .


