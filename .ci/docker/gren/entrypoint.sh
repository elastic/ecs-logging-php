#!/usr/bin/env bash
set -exo pipefail

/usr/local/bin/gren release \
        --token="${GITHUB_TOKEN}" \
        --tags="${TAG_NAME}" \
        --config .ci/.grenrc.js
