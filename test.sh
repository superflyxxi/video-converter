#!/bin/bash

#docker build --tag test --build-arg BUILD_IMAGE=${THIS_FULL_IMAGE:?Missing THIS_FULL_IMAGE} tests/
if [[ "${BUILD_SUBTITLE_SUPPORT}" == "true" ]]; then
	TESTSUITES="basic,subtitles"
else
	TESTSUITES="basic"
fi
set -ex
docker run --name test --user $(id -u):$(id -g) -v "$(pwd)/tests/:/tests/" -e LOG_LEVEL=VERBOSE -e TEST_SAMPLE_DOMAIN=${TEST_SAMPLE_DOMAIN} test --testsuite ${TESTSUITES}
#docker run --rm --user $(id -u):$(id -g) -e LOG_LEVEL=VERBOSE -e TEST_SAMPLE_DOMAIN=${TEST_SAMPLE_DOMAIN} test #--testsuite ${TESTSUITES}
