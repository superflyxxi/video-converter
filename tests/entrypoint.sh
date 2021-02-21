#!/bin/bash

set -e

TEST_SAMPLE_DOMAIN=${TEST_SAMPLE_DOMAIN:?Missing TEST_SAMPLE_DOMAIN}

cd /tests
export TMP_DIR=$(mktemp -d)
export LOG_LEVEL="VERBOSE"
export GREP_IGNORE="*"
if [[ ! "${BUILD_SUBTITLE_SUPPORT}" == "true" ]]; then
	GREP_IGNORE="\(test_10\|test_11\|test_12\)"
fi

find . -name 'test*.php' | grep -v "${GREP_IGNORE}" | sort | xargs -L1 php
