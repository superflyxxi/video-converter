#!/bin/bash

#docker build --tag test --build-arg BUILD_IMAGE=${THIS_FULL_IMAGE:?Missing THIS_FULL_IMAGE} tests/
TESTSUITES="basic,deinterlace,audio"
if [[ "${BUILD_SUBTITLE_SUPPORT}" = "true" ]]; then
	TESTSUITES="${TESTSUITES},subtitles"
fi
set -ex
#docker run --name test --user $(id -u):$(id -g) -v "$(pwd)/tests/:/tests/" -e LOG_LEVEL=VERBOSE -e TEST_SAMPLE_DOMAIN=${TEST_SAMPLE_DOMAIN} test --testsuite ${TESTSUITES}
docker run --rm --user $(id -u):$(id -g) -e LOG_LEVEL=VERBOSE -e TEST_SAMPLE_DOMAIN=${TEST_SAMPLE_DOMAIN} test --testsuite ${TESTSUITES}
