ARG FROM_IMAGE
FROM ${FROM_IMAGE}
MAINTAINER SuperFlyXXI <superflyxxi@yahoo.com>

WORKDIR /home/ripvideo/

RUN yum install -y php java-1.8.0-openjdk \
# VobSub2SRT Dependencies
	libtiff-devel tesseract-devel tesseract-lanuagepack-eng tesseract-ocr-eng \
	&& yum clean all

RUN DIR=$(mktemp -d) && cd ${DIR} && \
	git clone --depth 1 https://github.com/ruediger/VobSub2SRT.git && cd VobSub2SRT && \
	./configure --libdir=/usr/lib64 --prefix=/usr && \
	make && \
	make install && \
	rm -rf ${DIR}

ADD "https://raw.githubusercontent.com/wiki/mjuhasz/BDSup2Sub/downloads/BDSup2Sub.jar" /home/ripvideo/
ADD php/* /home/ripvideo/

ENTRYPOINT /home/ripvideo/main.php

