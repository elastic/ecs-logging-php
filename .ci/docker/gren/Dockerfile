FROM node:20-bullseye-slim

RUN apt-get update -qq -y \
  && apt-get install -qq -y --no-install-recommends git \
  && rm -rf /var/lib/apt/lists/*

RUN npm install github-release-notes@0.17.3 -g
WORKDIR /app

ENTRYPOINT [ "/app/.ci/docker/gren/entrypoint.sh" ]
CMD [ "release" ]