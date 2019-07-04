FROM {FROM_IMAGE}
MAINTAINER SuperFlyXXI <superflyxxi@yahoo.com>

ADD ripFile.sh /script

ENTRYPOINT /script/ripFile.sh

