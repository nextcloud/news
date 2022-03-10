#!/usr/bin/env bats

load "helpers/settings"

TESTSUITE="OPML"

teardown() {
  ID=$(./occ news:feed:list 'admin' | grep "Something-${BATS_SUITE_TEST_NUMBER}" -1  | head -1 | grep -oE '[0-9]*')
  if [ -n "$ID" ]; then
    ./occ news:feed:delete "$user" "$ID"
  fi
}

@test "[$TESTSUITE] Export" {
  run ./occ news:feed:add "$user" "https://nextcloud.com/blog/static-feed/"
  [ "$status" -eq 0 ]

  run ./occ news:opml:export "$user"
  [ "$status" -eq 0 ]

  if ! echo "$output" | grep "https://nextcloud.com/"; then
    ret_status=$?
    echo "Feed not exported"
    return $ret_status
  fi
}