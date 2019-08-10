#!/bin/bash
set -ex

. common.sh

docker tag rip-video:build-${THIS_LABEL} ${THIS_REGISTRY}/${THIS_REPO}/${THIS_IMAGE}:${THIS_LABEL}
docker push ${THIS_REGISTRY}/${THIS_REPO}/${THIS_IMAGE}:${THIS_LABEL}

