#!/usr/bin/env bash

composer validate && \
composer --ignore-platform-reqs install \
    --no-ansi --no-progress --no-scripts \
    --classmap-authoritative --no-interaction \
    --quiet