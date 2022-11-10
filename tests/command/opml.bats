#!/usr/bin/env bats

load "helpers/settings"

TESTSUITE="OPML"

teardown() {
  ID_LIST=($(./occ news:feed:list 'admin' | grep -Po '"id": \K([0-9]+)' | tr '\n' ' '))
  for ID in $ID_LIST; do
    ./occ news:feed:delete "$user" "$ID"
  done
}

@test "[$TESTSUITE] Export" {
  run ./occ news:feed:add "$user" "https://nextcloud.com/blog/static-feed/"  --title "Something-${BATS_SUITE_TEST_NUMBER}"
  [ "$status" -eq 0 ]

  run ./occ news:opml:export "$user"
  [ "$status" -eq 0 ]

  if ! echo "$output" | grep "https://nextcloud.com/"; then
    ret_status=$?
    echo "Feed not exported"
    return $ret_status
  fi
}