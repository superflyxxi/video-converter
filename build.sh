#!/bin/bash
set -ex

RIPFILE_IMAGE=${RIPFILE_IMAGE:-ripfile}
LABEL=${LABEL:-latest}

sed "s#{FROM_IMAGE}#${FFMPEG_DOCKER}#g" Dockerfile
docker build --tag ${RIPFILE_IMAGE}:${LABEL} .

