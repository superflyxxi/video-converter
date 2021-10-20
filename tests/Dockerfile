ARG BUILD_IMAGE
FROM ${BUILD_IMAGE}
LABEL org.opencontainers.image.authors="SuperFlyXXI <superflyxxi@yahoo.com>"

ARG DEBIAN_FRONTEND=noninteractive
ARG TEST_SAMPLE_DOMAIN
RUN apt-get update && \
	apt-get install -y php7.2-xml php7.2-mbstring curl && \
	apt-get clean -y
ENV DATA_DIR=/data \
	TEST_SAMPLE_BASE_URL="https://${TEST_SAMPLE_DOMAIN}/samples"

ENTRYPOINT ["/opt/video-converter/vendor/bin/phpunit", "-c", "/opt/video-converter/tests/phpunit.xml"]
CMD [ "/opt/video-converter/tests" ]
WORKDIR /opt/video-converter

COPY . /opt/video-converter

RUN apt-get update && \
	COMPOSER_DEPS="git zip" && \
	apt-get install -y ${COMPOSER_DEPS} && \
	composer install && \
	composer clear-cache && \
	apt-get purge -y ${COMPOSER_DEPS} && \
	apt-get clean -y && \
	mkdir -m 777 /opt/video-converter/testResults && \
	mkdir -m 777 "${DATA_DIR}"
ADD "${TEST_SAMPLE_BASE_URL}/DVD_Sample.mkv" "${DATA_DIR}/dvd.mkv"
ADD "${TEST_SAMPLE_BASE_URL}/Bluray_Sample.mkv" "${DATA_DIR}/bluray.mkv"
