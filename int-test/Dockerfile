FROM composer:2.6.6 AS composer
FROM video-converter AS build
FROM video-converter-parent
LABEL org.opencontainers.image.authors="SuperFlyXXI <superflyxxi@yahoo.com>"

ARG DEBIAN_FRONTEND=noninteractive
ENV DATA_DIR=/data
RUN apt-get update && \
	apt-get install -y --no-install-recommends php7.4-xml php7.4-mbstring curl php-xdebug && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/* && \
	mkdir -m 777 "${DATA_DIR}"
WORKDIR ${DATA_DIR}

ARG TEST_SAMPLE_DOMAIN
ENV TEST_SAMPLE_BASE_URL="https://${TEST_SAMPLE_DOMAIN}/samples"
RUN curl --fail -k -L "${TEST_SAMPLE_BASE_URL}/DVD_Sample.mkv" -o "${DATA_DIR}/dvd.mkv"
RUN curl --fail -k -L "${TEST_SAMPLE_BASE_URL}/Bluray_Sample.mkv" -o "${DATA_DIR}/bluray.mkv"

ENTRYPOINT ["/opt/video-converter/vendor/bin/phpunit", "-c", "/opt/video-converter/int-test/phpunit.xml", "--verbose"]
CMD [ "/opt/video-converter/int-test" ]

COPY --from=composer /usr/bin/composer /usr/bin/
COPY --from=build /opt/video-converter /opt/video-converter

RUN mv -v /opt/video-converter/int-test/csvs ${DATA_DIR} && \
	mkdir -m 777 /opt/video-converter/testResults || true && \
	apt-get update && \
	COMPOSER_DEPS="git zip unzip php-zip" && \
	apt-get install -y --no-install-recommends ${COMPOSER_DEPS} && \
	cd /opt/video-converter && \
	composer install && \
	composer clear-cache && \
	apt-get purge -y ${COMPOSER_DEPS} && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/*
RUN cd /opt/video-converter && \
	npm install

# FROM video-converter AS build
# FROM video-converter-parent
# LABEL org.opencontainers.image.authors="SuperFlyXXI <superflyxxi@yahoo.com>"
# ARG DEBIAN_FRONTEND=noninteractive
# RUN apt-get update && \
#	apt-get install -y --no-install-recommends curl && \
#	apt-get clean -y && rm -rf /var/lib/apt/lists/*
# ARG TEST_SAMPLE_DOMAIN
# ENV TEST_SAMPLE_BASE_URL="https://${TEST_SAMPLE_DOMAIN}/samples"
# RUN curl --fail -k -L "${TEST_SAMPLE_BASE_URL}/DVD_Sample.mkv" -o "${DATA_DIR}/dvd.mkv"
# RUN curl --fail -k -L "${TEST_SAMPLE_BASE_URL}/Bluray_Sample.mkv" -o "${DATA_DIR}/bluray.mkv"
# COPY --from=build /opt/video-converter /opt/video-converter
# ENV NODE_ENV=test
# RUN npm install
# ENTRYPOINT ["npm"]
# CMD ["run", "int-test"]
