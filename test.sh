#!/bin/bash

# Build beforehand
# docker build --tag test --build-arg BUILD_IMAGE=${THIS_FULL_IMAGE:?Missing THIS_FULL_IMAGE} tests/

mkdir testResults
set -e
TEST_IMAGE=${TEST_IMAGE:-video-converter-test}

if [[ "" != "${TESTCASES}" ]]; then
	printf "Using test cases: %s\n" "${TESTCASES}"
	# clean up with data set and replace with .*
	TEST_ARG="--filter /$(printf "%s" "${TESTCASES}" | sed 's/\(.*\) with data set "\(.*\)"/\1.*\2/g'| xargs echo | sed 's/ /|/g')/"
elif [[ "" != "${CLASSES}" ]]; then
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

echo "TEST_ARG=${TEST_ARG}"
docker run --name test -d \
	--user $(id -u):$(id -g) \
	${DEVICES} \
	${TEST_IMAGE} ${TEST_ARG} ${ADDITIONAL_PHPUNIT_ARGS}
sleep 2s
until [[ "$( docker container inspect -f '{{.State.Running}}' test )" == "false" ]];
do
	printf "Current Test: %s; %s\n" "$(docker exec test tail -n1 /opt/video-converter/testResults/testdox.txt)" "$(docker logs -n 1 test)"
	sleep ${SLEEPTIME:-30s}
done
EXIT_CODE=$(docker inspect test | grep "ExitCode\"" | sed 's/.*: \([0-9]\+\).*/\1/g')

if [[ ${EXIT_CODE} -ne 0 ]]; then
	docker logs test
fi
docker cp test:/opt/video-converter/testResults ./
docker rm test
printf "Test results\n============\n\n"
cat testResults/testdox.txt
ls testResults
mkdir -p testResults/junitResults
mv -v testResults/clover.xml testResults/clover-${CIRCLE_NODE_INDEX}.xml || true
mv -v testResults/junit.xml testResults/junitResults/junit-${CIRCLE_NODE_INDEX}.xml
tar -cjf testResults/coverage.tar.bz2 testResults/coverage
rm -rfv testResults/coverage
exit ${EXIT_CODE}
