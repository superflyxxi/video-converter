#!/bin/bash

# Build beforehand
# docker build --tag test --build-arg BUILD_IMAGE=${THIS_FULL_IMAGE:?Missing THIS_FULL_IMAGE} tests/

set -e
TEST_IMAGE=${TEST_IMAGE:-test}
TESTSUITES=${TESTSUITES:-basic,deinterlace,audio,sutitles}
mkdir testResults || true
docker run --name test -d -v "${SAMPLES_DIR?Missing SAMPLES_DIR}:/data" -v "$(pwd)/testResults:/testResults" --user $(id -u):$(id -g) ${TEST_IMAGE} --testsuite ${TESTSUITES}
PID=$(docker inspect test | grep "Pid\"" | sed 's/.*: \([0-9]\+\).*/\1/g')
while kill -0 ${PID} 2> /dev/null; do
	sleep ${SLEEPTIME:-30s}
	printf "%s Executing... " "$(date)"
	tail -n1 testResults/testdox.txt
done
EXIT_CODE=$(docker inspect test | grep "ExitCode\"" | sed 's/.*: \([0-9]\+\).*/\1/g')
if [[ ${EXIT_CODE} -ne 0 ]]; then
	docker logs test
fi
docker rm test
printf "Test results\n"
printf "============\n\n"
cat testResults/testdox.txt
exit ${EXIT_CODE}
