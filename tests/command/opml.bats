#!/usr/bin/env bats

setup(){
  load "../test_helper/bats-support/load"
  load "../test_helper/bats-assert/load"
  load "helpers/settings"
}

TESTSUITE="OPML"

teardown() {
  ID_LIST=($(./occ news:feed:list 'admin' | grep -Po '"id": \K([0-9]+)' | tr '\n' ' '))
  for ID in $ID_LIST; do
    ./occ news:feed:delete "$user" "$ID"
  done
}

@test "[$TESTSUITE] Import" {
  run ./occ news:opml:import "$user" apps/news/tests/test_helper/feeds/Nextcloud.opml
  assert_success

  run ./occ news:feed:list "$user"
  assert_success

  if ! echo "$output" | grep "title.*Nextcloud"; then
    assert_output --partial "Feed not imported"
  fi
}

@test "[$TESTSUITE] Export" {
  run ./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"
  assert_success

  run ./occ news:opml:export "$user"
  assert_success

  if ! echo "$output" | grep "Something-${BATS_SUITE_TEST_NUMBER}"; then
    assert_output --partial "Feed not exported"
  fi
}
