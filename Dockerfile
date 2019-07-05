ARG FROM_IMAGE
FROM ${FROM_IMAGE}
MAINTAINER SuperFlyXXI <superflyxxi@yahoo.com>

RUN yum install -y php && yum clean all

RUN curl -sL "https://raw.githubusercontent.com/wiki/mjuhasz/BDSup2Sub/downloads/BDSup2Sub.jar" --output "/home/ripvideo/BDSup2Sub.jar"

ADD scripts/* /home/ripvideo/

ENTRYPOINT /home/ripvideo/ripFile.php

