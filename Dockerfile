ARG FROM_IMAGE
FROM ${FROM_IMAGE}
MAINTAINER SuperFlyXXI <superflyxxi@yahoo.com>

ADD ripFile.php /home/ripvideo/

ENTRYPOINT /home/ripvideo/ripFile.php

