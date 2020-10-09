#!/bin/bash

feeds=$(cat ./lib/Explore/feeds/feeds.en.json | jq -r .[][0].feed)

for feed in ${feeds} ; do
  if [[ $feed == "http://"* ]]; then
    echo "Insecure feed $feed"
    exit 1;
  fi
  if ! curl --fail --silent "$feed" > /dev/null; then
    echo "Failed to fetch $feed"
    exit 1;
  fi
done