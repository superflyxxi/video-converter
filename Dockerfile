FROM jrottenberg/ffmpeg:7.1.1-vaapi2404
LABEL org.opencontainers.image.authors="SuperFlyXXI <superflyxxi@yahoo.com>"

# Support bash as the deafult shell
SHELL ["/bin/bash", "-o", "pipefail", "-c"]

ARG DEBIAN_FRONTEND=noninteractive
ENV TMP_DIR=/tmp/wip

RUN mkdir -p "${TMP_DIR}" && chmod -R ugo+rw "${TMP_DIR}"

# Most dependencies
RUN apt-get update && \
	apt-get install -y --no-install-recommends apt-utils php8.3-cli mkvtoolnix openjdk-21-jre-headless && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/*

# Install DBSup2Sub
ADD "https://raw.githubusercontent.com/wiki/mjuhasz/BDSup2Sub/downloads/BDSup2Sub.jar" /opt/
RUN chmod ugo+r /opt/BDSup2Sub.jar

# install tesseract, language packs, and java
RUN apt-get update && \
	apt-cache search tesseract-ocr | awk '{ print $1; }' | grep "^tesseract\-ocr\-" | grep -v "\-old\|\-all" | xargs apt-get install -y --no-install-recommends libtesseract5 && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/*

# VobSub2SRT
RUN DIR=$(mktemp -d) && cd "${DIR}" && \
    BUILD_DEPS="git libtesseract-dev libleptonica-dev libtiff5-dev build-essential cmake pkg-config" && \
	apt-get update && \
	apt-get install -y --no-install-recommends ${BUILD_DEPS} &&  \
	git clone --depth 1 "https://github.com/waterkip/VobSub2SRT.git" && \
	cd VobSub2SRT && \
	./configure && \
	make && \
	make install && \
	apt-get purge -y ${BUILD_DEPS} && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/* && \
	rm -rf "${DIR}"
