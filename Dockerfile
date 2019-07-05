FROM {FROM_IMAGE}
MAINTAINER SuperFlyXXI <superflyxxi@yahoo.com>

ADD ripFile.sh /home/ripfile

ENTRYPOINT /home/ripfile/ripFile.sh

