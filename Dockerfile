FROM {FROM_IMAGE}
MAINTAINER SuperFlyXXI <superflyxxi@yahoo.com>

ADD ripFile.sh /home/ripvideo

ENTRYPOINT /home/ripvideo/ripFile.sh

