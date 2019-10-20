#!/bin/bash
set -ex

. common.sh

docker push ${THIS_FULL_IMAGE}
