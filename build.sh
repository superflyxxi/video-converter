#!/bin/bash
set -ex

THIS_REPO=${THIS_REPO:-superflyxxi}
THIS_REGISTRY=${THIS_REGISTRY:-build}
THIS_IMAGE=${RIPFILE_IMAGE:-ripfile}
LABEL=${LABEL:-latest}
CACHE_FROM_ARGS=${CACHE_FROM_ARGS:-"--cache-from ${THIS_REGISTRY}/${THIS_REPO}/${THIS_IMAGE}:latest"}

sed -i "s#{FROM_IMAGE}#${FFMPEG_DOCKER}#g" Dockerfile
docker build --tag ${THIS_REGISTRY}/${THIS_REPO}/${THIS_IMAGE}:${LABEL} .

