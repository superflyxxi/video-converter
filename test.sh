#!/bin/bash

#docker build --tag test --build-arg BUILD_IMAGE=${THIS_FULL_IMAGE:?Missing THIS_FULL_IMAGE} tests/

TESTSUITES="basic,deinterlace,audio"
if [[ "${BUILD_SUBTITLE_SUPPORT}" = "true" ]]; then
	TESTSUITES="${TESTSUITES},subtitles"
fi
mkdir testResults
docker run --rm -v "$(pwd)/testResults:/testResults" --user $(id -u):$(id -g) -e TEST_SAMPLE_DOMAIN=${TEST_SAMPLE_DOMAIN} test --testsuite ${TESTSUITES}
cat testResults/testdox.txt
