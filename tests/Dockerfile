ARG BUILD_IMAGE
FROM ${BUILD_IMAGE}
WORKDIR /tests
RUN DEBIAN_FRONTEND=noninteractive \
	apt-get install -y php-xml php-mbstring curl && \
	apt-get clean -y
ADD "https://phar.phpunit.de/phpunit-8.phar" /usr/bin/phpunit
RUN chmod ugo+xr /usr/bin/phpunit
ENTRYPOINT [ "/usr/bin/phpunit", "-c", "/tests/phpunit.xml" ]
CMD [ "/tests" ]
ENV DATA_DIR=/data
RUN mkdir -m 777 -p ${DATA_DIR}
ARG TEST_SAMPLE_DOMAIN
ADD "https://${TEST_SAMPLE_DOMAIN}/samples/DVD_Sample.mkv" "/data/dvd.mkv"
ADD "https://${TEST_SAMPLE_DOMAIN}/samples/Bluray_Sample.mkv" "/data/bluray.mkv"
ADD ./ /tests
