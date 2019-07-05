ARG FROM_IMAGE
FROM ${FROM_IMAGE}
MAINTAINER SuperFlyXXI <superflyxxi@yahoo.com>

RUN yum install -y php && yum clean all

#RUN curl -sL "https://raw.githubusercontent.com/wiki/mjuhasz/BDSup2Sub/downloads/BDSup2Sub.jar" --output "/home/ripvideo/BDSup2Sub.jar"
ADD "https://raw.githubusercontent.com/wiki/mjuhasz/BDSup2Sub/downloads/BDSup2Sub.jar" /home/ripvideo/

RUN yum install -y libtiff5-dev libtesseract-dev tesseract-ocr-eng build-essential cmake && yum clean all
RUN DIR=$(mktemp -d) && cd ${DIR} && \
	git clone --depth 1 https://github.com/ruediger/VobSub2SRT.git && cd VobSub2SRT && \
	./configure --libdir=/usr/lib64 --prefix=/usr && \
	make && \
	make install && \
	rm -rf ${DIR}

ADD scripts/* /home/ripvideo/

ENTRYPOINT /home/ripvideo/ripFile.php

