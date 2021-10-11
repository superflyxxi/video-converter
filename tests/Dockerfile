ARG BUILD_IMAGE
FROM ${BUILD_IMAGE}
ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update && \
	apt-get install -y php-xml php-mbstring curl && \
	apt-get clean -y
ENTRYPOINT [ "composer" ]
CMD [ "run-script", "test" ]
ENV DATA_DIR=/data
ADD ./ /app/ripvideo/tests
RUN apt-get update && \
	COMPOSER_DEPS="git zip" && \
	apt-get install -y ${COMPOSER_DEPS} && \
	composer install && \
	composer clear-cache && \
	apt-get purge -y ${COMPOSER_DEPS} && \
	apt-get clean -y && \
	mkdir -m 777 -p ${DATA_DIR} && \
	mkdir -m 777 -p /testResults
