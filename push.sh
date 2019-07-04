#!/bin/bash
set -ex

. common.sh

docker push ${THIS_REGISTRY}/${THIS_REPO}/${THIS_IMAGE}:${THIS_LABEL}

