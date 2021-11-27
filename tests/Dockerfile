FROM composer:2.1.12 AS composer
FROM video-converter AS video-converter
FROM video-converter-parent
LABEL org.opencontainers.image.authors="SuperFlyXXI <superflyxxi@yahoo.com>"

ARG DEBIAN_FRONTEND=noninteractive
ARG TEST_SAMPLE_DOMAIN
ENV DATA_DIR=/data \
	TEST_SAMPLE_BASE_URL="https://${TEST_SAMPLE_DOMAIN}/samples"
RUN apt-get update && \
	apt-get install -y php7.4-xml php7.4-mbstring curl && \
	apt-get clean -y && \
	mkdir -m 777 "${DATA_DIR}"

ENTRYPOINT ["/opt/video-converter/vendor/bin/phpunit", "-c", "/opt/video-converter/tests/phpunit.xml"]
CMD [ "/opt/video-converter/tests" ]
WORKDIR /opt/video-converter

RUN curl -k -L "${TEST_SAMPLE_BASE_URL}/DVD_Sample.mkv" -o "${DATA_DIR}/dvd.mkv"
RUN curl -k -L "${TEST_SAMPLE_BASE_URL}/Bluray_Sample.mkv" -o "${DATA_DIR}/bluray.mkv"

COPY --from=composer /usr/bin/composer /usr/bin/
COPY --from=video-converter /usr/bin/video-converter /usr/bin/
COPY . /opt/video-converter

RUN mkdir -m 777 /opt/video-converter/testResults || true && \
	apt-get update && \
	COMPOSER_DEPS="git zip" && \
	apt-get install -y ${COMPOSER_DEPS} && \
	composer install && \
	composer clear-cache && \
	apt-get purge -y ${COMPOSER_DEPS} && \
	apt-get clean -y
	
