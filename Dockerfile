FROM jrottenberg/ffmpeg:4.4-vaapi1804
LABEL org.opencontainers.image.authors="SuperFlyXXI <superflyxxi@yahoo.com>"

# Support bash as the deafult shell
SHELL ["/bin/bash", "-o", "pipefail", "-c"]

ARG DEBIAN_FRONTEND=noninteractive
ARG BUILD_SUBTITLE_SUPPORT=true
WORKDIR /data

ENV TMP_DIR=/tmp/wip
RUN mkdir -p ${TMP_DIR}/data && chmod -R ugo+rw ${TMP_DIR}

RUN apt-get update -y && \
	apt-get install -y --no-install-recommends apt-utils && \
	apt-get install -y --no-install-recommends php7.2-cli php7.2-json mkvtoolnix && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/*
RUN apt-get update -y && \
	apt-get install -y --no-install-recommends curl && \
	curl -s "https://getcomposer.org/installer" | php -- --install-dir=/bin --filename=composer && \
	apt-get purge -y curl && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/*

# install tesseract, language packs, and java
RUN if [[ "${BUILD_SUBTITLE_SUPPORT}" == "true" ]]; then \
	apt-get update && \
	apt-get install -y --no-install-recommends libtesseract4 openjdk-11-jre-headless && \
	apt-cache search tesseract-ocr | awk '{ print $1; }' | grep "^tesseract" | grep -v "\-old" | xargs apt-get install -y --no-install-recommends && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/* ; \
    fi

RUN if [[ "${BUILD_SUBTITLE_SUPPORT}" == "true" ]]; then \
	DIR=$(mktemp -d) && cd "${DIR}" && \
        BUILD_DEPS="git libleptonica-dev libtiff5-dev build-essential cmake pkg-config" && \
	if [[ ! "${FROM_IMAGE}" == *1804 ]]; then \
		BUILD_DEPS="${BUILD_DEPS} libtesseract-dev"; \
	else \
		BUILD_DEPS="${BUILD_DEPS} tesseract-ocr-dev"; \
	fi && \
	printf "FROM_IMAGE=%s\nBUILD_DEPS=%s\n" "${FROM_IMAGE}" "${BUILD_DEPS}"&& \
	apt-get update && \
	apt-get install -y --no-install-recommends "${BUILD_DEPS}" &&  \
	git clone --depth 1 "https://github.com/bubonic/VobSub2SRT.git" && \
	cd VobSub2SRT && \
	./configure && \
	make && \
	make install && \
	apt-get purge -y "${BUILD_DEPS}" && \
	apt-get clean -y && rm -rf /var/lib/apt/lists/* && \
	rm -rf "${DIR}" ; \
   fi

# Install DBSup2Sub
ADD "https://raw.githubusercontent.com/wiki/mjuhasz/BDSup2Sub/downloads/BDSup2Sub.jar" /app/ripvideo/

ENTRYPOINT ["/usr/bin/video-converter"]
COPY video-converter.phar /usr/bin/video-converter
