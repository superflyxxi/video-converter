ARG BUILD_IMAGE
FROM ${BUILD_IMAGE}
ADD ./ /tests
ENTRYPOINT ["/tests/entrypoint.sh"]
