ARG FROM_IMAGE
FROM ${FROM_IMAGE}
MAINTAINER SuperFlyXXI <superflyxxi@yahoo.com>

RUN yum install -y php && yum clean all

ADD ripFile.php /home/ripvideo/

ENTRYPOINT /home/ripvideo/ripFile.php

