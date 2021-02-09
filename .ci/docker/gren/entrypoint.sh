#!/usr/bin/env bash
set -eo pipefail

TYPE=${1:-release}

if [ "${TYPE}" == "release" ] ; then
    /usr/local/bin/gren release \
        --token="${GITHUB_TOKEN}" \
        --tags="${TAG_NAME}" \
        --config .ci/.grenrc.js
else
    /usr/local/bin/gren changelog \
        --generate \
        --override \
        --token="${GITHUB_TOKEN}" \
        --tags="${TAG_NAME}" \
        --config .ci/.grenrc.js
fi
