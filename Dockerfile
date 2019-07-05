ARG FROM_IMAGE
FROM ${FROM_IMAGE}
MAINTAINER SuperFlyXXI <superflyxxi@yahoo.com>

RUN yum install -y php openjdk && yum clean all

ADD "https://raw.githubusercontent.com/wiki/mjuhasz/BDSup2Sub/downloads/BDSup2Sub.jar" /home/ripvideo/

# VobSub2SRT Depenendencies
#RUN yum install -y libtiff-devevl tesseract-devel tesseract-lanuagepack-eng tesseract-ocr-eng && yum clean all
RUN yum install -y libtiff-devevl && yum clean all
RUN DIR=$(mktemp -d) && cd ${DIR} && \
	git clone --depth 1 https://github.com/tesseract-ocr/tesseract.git && cd tesseract && \
	./autogen.sh && \
	./configure --prefix=/usr --libdir=/usr/lib64 && \
	make && \
	make install && \
	rm -rf ${DIR}

RUN DIR=$(mktemp -d) && cd ${DIR} && \
	git clone --depth 1 https://github.com/ruediger/VobSub2SRT.git && cd VobSub2SRT && \
	./configure --libdir=/usr/lib64 --prefix=/usr && \
	make && \
	make install && \
	rm -rf ${DIR}

ADD scripts/* /home/ripvideo/

ENTRYPOINT /home/ripvideo/ripFile.php

