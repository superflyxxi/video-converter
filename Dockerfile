FROM jrottenberg/ffmpeg:4.4-vaapi1804
MAINTAINER SuperFlyXXI <superflyxxi@yahoo.com>

WORKDIR /home/ripvideo/

ENV TMP_DIR=/tmp/wip
RUN mkdir -p ${TMP_DIR}/data && chmod -R ugo+rw ${TMP_DIR}

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update -y && \
	apt-get install -y apt-utils && \
	apt-get install -y php-cli php-json mkvtoolnix && \
	apt autoremove -y --purge && apt-get clean -y
RUN apt-get update -y && \
	apt-get install -y curl && \
	curl -s "https://getcomposer.org/installer" | php -- --install-dir=/bin --filename=composer && \
	apt-get purge -y curl && \
	apt autoremove -y --purge && apt-get clean -y

ARG BUILD_SUBTITLE_SUPPORT=true

# Support bash as the deafult shell
RUN rm /bin/sh && ln -s /bin/bash /bin/sh

# install tesseract, language packs, and java
RUN if [[ "${BUILD_SUBTITLE_SUPPORT}" == "true" ]]; then \
	apt-get update && \
	apt-get install -y libtesseract4 openjdk-11-jre-headless && \
	apt-cache search tesseract-ocr | awk '{ print $1; }' | grep "^tesseract" | grep -v "\-old" | xargs apt-get install -y && \
	apt autoremove -y --purge && apt-get clean -y ; \
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
	apt autoremove -y --purge && apt-get clean -y && \
	rm -rf ${DIR} ; \
   fi

# Install DBSup2Sub
ADD "https://raw.githubusercontent.com/wiki/mjuhasz/BDSup2Sub/downloads/BDSup2Sub.jar" /home/ripvideo/

ENTRYPOINT /home/ripvideo/rip-video.php
COPY php/ /home/ripvideo/

# Introduce AI Upscaler
ARG BUILD_UPSCALER=false
RUN if [[ "${BUILD_UPSCALER}" == "true" ]]; then \
	BUILD_DEPS="python3-pip gfortran git wget" && \
	apt-get update && \
	apt-get install -y python3 ${BUILD_DEPS} && \
	pip3 install --upgrade pip && \
	pip3 install numpy opencv-python torch && \
	git clone --depth 1 https://github.com/xinntao/ESRGAN && \
	rm -v ./ESRGAN/LR/* && \
	wget --progress=dot:mega "https://drive.google.com/uc?export=download&id=1TPrz5QKd8DHHt1k8SRtm6tMiPjz_Qene" -O ./ESRGAN/models/RRDB_ESRGAN_x4.pth && \
	wget --progress=dot:mega "https://drive.google.com/uc?export=download&id=1pJ_T-V1dpb1ewoEra1TGSWl5e6H7M4NN" -O ./ESRGAN/models/RRDB_PSNR_x4.pth && \
	apt-get purge -y ${BUILD_DEPS} && \
	apt-get clean -y ; \
    fi
ADD esrgan/convert.py /home/ripvideo/ESRGAN/upscale.py

RUN apt-get update && \
	apt-get install -y git && \
	composer install --no-dev && \
	apt-get purge -y git && \
	apt autoremove -y --purge && apt-get clean -y && \
	chmod -R ugo+r /home/ripvideo

