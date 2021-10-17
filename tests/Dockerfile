ARG BUILD_IMAGE
FROM ${BUILD_IMAGE}
LABEL org.opencontainers.image.authors="SuperFlyXXI <superflyxxi@yahoo.com>"

ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update && \
	apt-get install -y php7.2-xml php7.2-mbstring curl && \
	apt-get clean -y
ENV DATA_DIR=/data

ENTRYPOINT /app/ripvideo/vendor/bin/phpunit -c /app/ripvideo/tests/phpunit.xml
CMD [ "/app/ripvideo/tests" ]
WORKDIR /app/ripvideo

COPY . /app/ripvideo

RUN apt-get update && \
	COMPOSER_DEPS="git zip" && \
	apt-get install -y ${COMPOSER_DEPS} && \
	composer install && \
	composer clear-cache && \
	apt-get purge -y ${COMPOSER_DEPS} && \
	apt-get clean -y && \
	mkdir -m 777 /app/ripvideo/testResults
