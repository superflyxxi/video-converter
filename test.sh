#!/bin/bash

set -e
#docker build --tag test --build-arg BUILD_IMAGE=${THIS_FULL_IMAGE:?Missing THIS_FULL_IMAGE} tests/

TESTSUITES="basic,deinterlace,audio"
if [[ "${BUILD_SUBTITLE_SUPPORT}" = "true" ]]; then
	TESTSUITES="${TESTSUITES},subtitles"
fi
mkdir testResults || true
docker run --name test -d -v "$(pwd)/testResults:/testResults" --user $(id -u):$(id -g) -e TEST_SAMPLE_DOMAIN=${TEST_SAMPLE_DOMAIN?Missing TEST_SAMPLE_DOMAIN} test --testsuite ${TESTSUITES}
echo "Running tests..."
sleep 10s
PID=$(docker inspect test | grep "Pid\"" | sed 's/.*: \([0-9]\+\).*/\1/g')
tail --pid=${PID} -f testResults/testdox.txt
EXIT_CODE=$(docker inspect test | grep "ExitCode\"" | sed 's/.*: \([0-9]\+\).*/\1/g')
if [[ ${EXIT_CODE} -ne 0 ]]; then
	docker logs test
fi
docker rm test
exit ${EXIT_CODE}
