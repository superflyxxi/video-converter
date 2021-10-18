#!/bin/bash

# Build beforehand
# docker build --tag test --build-arg BUILD_IMAGE=${THIS_FULL_IMAGE:?Missing THIS_FULL_IMAGE} tests/

set -e

TEST_IMAGE=${TEST_IMAGE:-test}
TESTSUITES=${TESTSUITES:-unit-tests,integration-tests}
if [[ "${USE_VAAPI:-false}" = "true" ]]; then
	DEVICES="--device /dev/dri"
fi
mkdir testResults || true
docker run --name test -d \
	--user $(id -u):$(id -g) \
	${DEVICES} \
	-v "$(pwd)/testResults:/opt/video-converter/testResults" \
	-e LOG_LEVEL=100 \
	-e TEST_SAMPLE_DOMAIN=${TEST_SAMPLE_DOMAIN?Missing TEST_SAMPLE_DOMAIN} \
	${TEST_IMAGE} --testsuite ${TESTSUITES} ${ADDITIONAL_PHPUNIT_ARGS}
PID=$(docker inspect test | grep "Pid\"" | sed 's/.*: \([0-9]\+\).*/\1/g')
while kill -0 ${PID} 2> /dev/null; do
	sleep ${SLEEPTIME:-30s}
	printf "%s Executing... " "$(date)"
	tail -n1 testResults/testdox.txt || docker logs test
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
