ARG BUILD_IMAGE
FROM ${BUILD_IMAGE}
RUN DEBIAN_FRONTEND=noninteractive \
	apt-get install -y php-xml && \
	apt-get clean -y
ADD "https://phar.phpunit.de/phpunit-9.phar" /usr/bin/phpunit
RUN chmod ugo+xr /usr/bin/phpunit
ADD ./ /tests
#RUN apt-get install -y wget && \
#        wget "https://phar.phpunit.de/phpunit-9.phar" --progress=dot:mega -O /usr/bin/phpunit && \
#        chmod ugo+x /usr/bin/phpunit && \
#        apk del wget

ENTRYPOINT [ "/usr/bin/phpunit", "-c", "/tests/phpunit.xml" ]
CMD [ "./" ]
