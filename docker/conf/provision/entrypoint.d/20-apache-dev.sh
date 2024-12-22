#!/usr/bin/env bash

if [[ -z "$WEB_NO_CACHE_PATTERN" ]]; then
    echo "Error: WEB_NO_CACHE_PATTERN is not set or empty"
    exit 1
fi

go-replace \
    -s "<WEB_NO_CACHE_PATTERN>" -r "$WEB_NO_CACHE_PATTERN" \
    --path=/opt/docker/etc/httpd/ \
    --path-pattern='*.conf' \
    --ignore-empty