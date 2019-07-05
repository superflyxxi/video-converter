#!/bin/bash
set -ex

. common.sh

CACHE_FROM_ARGS=${CACHE_FROM_ARGS:-"--cache-from ${THIS_REGISTRY}/${THIS_REPO}/${THIS_IMAGE}:latest"}

#sed -i "s#{FROM_IMAGE}#${FFMPEG_DOCKER}#g" Dockerfile
docker build ${CACHE_FROM_ARGS} --build-arg FROM_IMAGE=${FFMPEG_DOCKER} --tag ${THIS_REGISTRY}/${THIS_REPO}/${THIS_IMAGE}:${THIS_LABEL} .

