FROM jrottenberg/ffmpeg:4.4-vaapi1804
LABEL org.opencontainers.image.authors="SuperFlyXXI <superflyxxi@yahoo.com>"

ARG DEBIAN_FRONTEND=noninteractive
ARG BUILD_SUBTITLE_SUPPORT=true
WORKDIR /data

ENV TMP_DIR=/tmp/wip
RUN mkdir -p ${TMP_DIR}/data && chmod -R ugo+rw ${TMP_DIR}

RUN apt-get update -y && \
	apt-get install -y apt-utils && \
	apt-get install -y php7.2-cli php7.2-json mkvtoolnix && \
	apt-get clean -y
RUN apt-get update -y && \
	apt-get install -y curl && \
	curl -s "https://getcomposer.org/installer" | php -- --install-dir=/bin --filename=composer && \
	apt-get purge -y curl && \
	apt-get clean -y

# Support bash as the deafult shell
RUN rm /bin/sh && ln -s /bin/bash /bin/sh

# install tesseract, language packs, and java
RUN if [[ "${BUILD_SUBTITLE_SUPPORT}" == "true" ]]; then \
	apt-get update && \
	apt-get install -y libtesseract4 openjdk-11-jre-headless && \
	apt-cache search tesseract-ocr | awk '{ print $1; }' | grep "^tesseract" | grep -v "\-old" | xargs apt-get install -y && \
	apt-get clean -y ; \
    fi

RUN if [[ "${BUILD_SUBTITLE_SUPPORT}" == "true" ]]; then \
	DIR=$(mktemp -d) && cd ${DIR} && \
        BUILD_DEPS="git libleptonica-dev libtiff5-dev build-essential cmake pkg-config" && \
	if [[ ! "${FROM_IMAGE}" == *1804 ]]; then \
		BUILD_DEPS="${BUILD_DEPS} libtesseract-dev"; \
	else \
		BUILD_DEPS="${BUILD_DEPS} tesseract-ocr-dev"; \
	fi && \
	printf "FROM_IMAGE=${FROM_IMAGE}\nBUILD_DEPS=${BUILD_DEPS}\n" && \
	apt-get update && \
	apt-get install -y ${BUILD_DEPS} &&  \
	git clone --depth 1 https://github.com/bubonic/VobSub2SRT.git && \
	cd VobSub2SRT && \
	./configure && \
	make && \
	make install && \
	apt-get purge -y ${BUILD_DEPS} && \
	apt-get clean -y && \
	rm -rf ${DIR} ; \
   fi

# Install DBSup2Sub
COPY "https://raw.githubusercontent.com/wiki/mjuhasz/BDSup2Sub/downloads/BDSup2Sub.jar" /app/ripvideo/

ENTRYPOINT ["/usr/bin/video-converter.phar"]
COPY video-converter.phar /usr/bin/
