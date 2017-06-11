#!/usr/bin/env bash

# Very handy script to see the log of webpack execution

cd ./tests/functional/Fixtures

node_modules/.bin/webpack --config ./app/cache/test/webpack.config.js
