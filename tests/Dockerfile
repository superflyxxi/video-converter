FROM composer:2.8.12 AS composer
FROM video-converter-parent
LABEL org.opencontainers.image.authors="SuperFlyXXI <superflyxxi@yahoo.com>"

ARG DEBIAN_FRONTEND=noninteractive
ENV DATA_DIR=/data
WORKDIR ${DATA_DIR}
RUN apt-get update && \
	apt-get install -y --no-install-recommends php8.3-xml php8.3-mbstring curl php-xdebug && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/* 

ENTRYPOINT ["/opt/video-converter/vendor/bin/phpunit", "-c", "/opt/video-converter/tests/phpunit.xml"]
CMD [ "/opt/video-converter/tests" ]

ARG TEST_SAMPLE_DOMAIN
ENV	TEST_SAMPLE_BASE_URL="https://${TEST_SAMPLE_DOMAIN}/samples" \
	XDEBUG_MODE=coverage
RUN chmod 777 "${DATA_DIR}"
RUN curl --fail -k -L "${TEST_SAMPLE_BASE_URL}/DVD_Sample.mkv" -o "${DATA_DIR}/dvd.mkv"
RUN curl --fail -k -L "${TEST_SAMPLE_BASE_URL}/Bluray_Sample.mkv" -o "${DATA_DIR}/bluray.mkv"

COPY --from=composer /usr/bin/composer /usr/bin/
COPY . /opt/video-converter

RUN mv -v /opt/video-converter/tests/csvs ${DATA_DIR} && \
	mkdir -m 777 /opt/video-converter/testResults || true && \
	apt-get update && \
	COMPOSER_DEPS="git zip unzip php-zip" && \
	apt-get install -y --no-install-recommends ${COMPOSER_DEPS} && \
	cd /opt/video-converter && \
	composer install && \
	composer clear-cache && \
	apt-get purge -y ${COMPOSER_DEPS} && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/*
	
