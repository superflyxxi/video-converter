ARG BUILD_IMAGE
FROM ${BUILD_IMAGE}
WORKDIR /tests
RUN DEBIAN_FRONTEND=noninteractive \
	apt-get update && \
	apt-get install -y php-xml php-mbstring curl && \
	apt autoremove -y --purge && apt-get clean -y
ADD "https://phar.phpunit.de/phpunit-8.phar" /usr/bin/phpunit
ENTRYPOINT [ "/usr/bin/phpunit", "-c", "/tests/phpunit.xml" ]
CMD [ "/tests" ]
ENV DATA_DIR=/data
RUN chmod ugo+xr /usr/bin/phpunit && \
	mkdir -m 777 -p ${DATA_DIR} && \
	mkdir -m 777 -p /testResults
ADD ./ /tests
