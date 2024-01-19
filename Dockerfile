FROM video-converter-parent
# ENTRYPOINT ["/usr/bin/video-converter"]
ENTRYPOINT ["npm", "start"]
WORKDIR /opt/video-converter
#COPY video-converter.phar /usr/bin/video-converter
COPY . /opt/video-converter
RUN npm i
