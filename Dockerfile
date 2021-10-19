FROM jrottenberg/ffmpeg:4.4-vaapi1804
LABEL org.opencontainers.image.authors="SuperFlyXXI <superflyxxi@yahoo.com>"

# Support bash as the deafult shell
SHELL ["/bin/bash", "-o", "pipefail", "-c"]

ARG DEBIAN_FRONTEND=noninteractive
ENV TMP_DIR=/tmp/wip

# Install DBSup2Sub
ADD "https://raw.githubusercontent.com/wiki/mjuhasz/BDSup2Sub/downloads/BDSup2Sub.jar" /opt/

RUN mkdir -p "${TMP_DIR}" && \
	chmod -R ugo+rw "${TMP_DIR}" && \
	chmod ugo+r /opt/BDSup2Sub.jar && \
	apt-get update -y && \
	apt-get install -y --no-install-recommends apt-utils curl php7.2-cli php7.2-json mkvtoolnix && \
	curl -s "https://getcomposer.org/installer" | php -- --install-dir=/bin --filename=composer && \
	apt-get purge -y curl && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/*

# install tesseract, language packs, and java
RUN apt-get update && \
	apt-get install -y --no-install-recommends libtesseract4 openjdk-11-jre-headless && \
	apt-cache search tesseract-ocr | awk '{ print $1; }' | grep "^tesseract" | grep -v "\-old" | xargs apt-get install -y --no-install-recommends && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/*

RUN DIR=$(mktemp -d) && cd "${DIR}" && \
    BUILD_DEPS="git libtesseract-dev libleptonica-dev libtiff5-dev build-essential cmake pkg-config" && \
	apt-get update && \
	apt-get install -y --no-install-recommends ${BUILD_DEPS} &&  \
	git clone --depth 1 "https://github.com/bubonic/VobSub2SRT.git" && \
	cd VobSub2SRT && \
	./configure && \
	make && \
	make install && \
	apt-get purge -y ${BUILD_DEPS} && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/* && \
	rm -rf "${DIR}"

ENTRYPOINT ["/usr/bin/video-converter"]
COPY video-converter.phar /usr/bin/video-converter
