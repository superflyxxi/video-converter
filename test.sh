#!/bin/bash

# Build beforehand
# docker build --tag test --build-arg BUILD_IMAGE=${THIS_FULL_IMAGE:?Missing THIS_FULL_IMAGE} tests/

set -e

TEST_IMAGE=${TEST_IMAGE:-video-converter-test}

if [[ "" != "${CLASSES}" ]]; then
	printf "Using test classes: %s\n" "${CLASSES}"
	TEST_ARG="--filter /$(xargs echo <<< $CLASSES | sed 's/ /|/g')/"
elif [[ "" != "${CIRCLE_NODE_TOTAL}" ]]; then
	printf "Using CircleCI nodes: index=%s; total=%s\n" "${CIRCLE_NODE_INDEX}" "${CIRCLE_NODE_TOTAL}"
	ALL_TESTS=$(docker run --rm -it ${TEST_IMAGE} --list-tests | grep "^\s*-" | sed 's/^\s*-\s*//g' | sed 's/\r\s*/\n/g')
	ALL_TESTS=($ALL_TESTS)
	multiplier=$(( ((10 * ${#ALL_TESTS[@]} / ${CIRCLE_NODE_TOTAL} ) + 5 ) / 10))
	start=$((${CIRCLE_NODE_INDEX} * $multiplier))
	end=$((( ${CIRCLE_NODE_INDEX} + 1 ) * $multiplier))
	printf "Going %s (inclusive) to %s (exclusive) from %s total with multiplier=%s\n" "${start}" "${end}" "${len}" "$multiplier"
	declare -a TESTS_TO_CONSIDER
	for ((i=$start; i<$end; i++)); do
		TESTS_TO_CONSIDER+=(${ALL_TESTS[$i]})
	done
	TESTS=${TESTS_TO_CONSIDER[@]}
	TEST_ARG="--filter /${TESTS// /|}/"
fi

if [[ "${USE_VAAPI:-false}" = "true" && -d /dev/dri ]]; then
	DEVICES="--device /dev/dri"
fi
set -x
docker run --name test -d \
	--user $(id -u):$(id -g) \
	${DEVICES} \
	${TEST_IMAGE} ${TEST_ARG} ${ADDITIONAL_PHPUNIT_ARGS}
sleep ${SLEEPTIME:-10s}
PID=$(docker inspect test | grep "Pid\"" | sed 's/.*: \([0-9]\+\).*/\1/g')
while kill -0 ${PID} 2> /dev/null; do
	printf "Current Test: %s; Log: %s\n" "$(docker exec test tail -n1 /opt/video-converter/testResults/testdox.txt)" "$(docker logs -n 1 test)"
	sleep ${SLEEPTIME:-30s}
done
EXIT_CODE=$(docker inspect test | grep "ExitCode\"" | sed 's/.*: \([0-9]\+\).*/\1/g')
if [[ ${EXIT_CODE} -ne 0 ]]; then
	docker logs test
fi
docker cp test:/opt/video-converter/testResults/testdox.txt ./testdox.txt
docker rm test
printf "Test results\n============\n\n"
cat testdox.txt
exit ${EXIT_CODE}
