#!/bin/bash

FROM=${FROM:-superflyxxi.dlinkddns.com:5000/superflyxxi/ffmpeg-vaapi:latest}
IMAGE=${IMAGE:-ripfile}

set -ex
cp Dockerfile .dockerfile.tmp
sed -i "s#{FROM}#${FROM}#g" .dockerfile.tmp
docker build --tag ${IMAGE} -f .dockerfile.tmp .


