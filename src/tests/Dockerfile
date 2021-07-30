ARG BUILD_IMAGE
FROM ${BUILD_IMAGE}
ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update && \
	apt-get install -y php-xml php-mbstring curl && \
	apt-get clean -y
WORKDIR /home/ripvideo/tests
ENTRYPOINT [ "/home/ripvideo/tests/vendor/bin/phpunit", "-c", "/home/ripvideo/tests/phpunit.xml" ]
CMD [ "/home/ripvideo/tests" ]
ENV DATA_DIR=/data
ADD ./ /home/ripvideo/tests
RUN apt-get update && \
	COMPOSER_DEPS="git zip" && \
	apt-get install -y ${COMPOSER_DEPS} && \
	cd /home/ripvideo/tests && \
	composer install && \
	composer clear-cache && \
	apt-get purge -y ${COMPOSER_DEPS} && \
	apt-get autoremove -y && \
	apt-get clean -y && \
	mkdir -m 777 -p ${DATA_DIR} && \
	mkdir -m 777 -p /testResults
