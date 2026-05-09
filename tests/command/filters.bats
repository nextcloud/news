#!/usr/bin/env bats

setup(){
  load "../test_helper/bats-support/load"
  load "../test_helper/bats-assert/load"
  load "helpers/settings"
}

TESTSUITE="Filters"

teardown() {
  ID_LIST=($(./occ news:feed:list "$user" | grep -Po '"id": \K([0-9]+)' | tr '\n' ' '))
  for ID in $ID_LIST; do
    ./occ news:feed:delete "$user" "$ID" -v
  done
}

@test "[$TESTSUITE] Get empty filter" {
  ID=$(./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')

  run ./occ news:feed:filter:get "$user" "$ID"

  assert_success
  assert_output --partial '"id": null'
  assert_output --partial '"feedId": '$ID
}

@test "[$TESTSUITE] Set and get filter" {
  ID=$(./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')

  run ./occ news:feed:filter:set "$user" "$ID" --title "sponsored, ads" --body "tracking" --url "/utm_"

  assert_success
  assert_output --partial '"titleKeywords": "sponsored, ads"'

  run ./occ news:feed:filter:get "$user" "$ID"

  assert_success
  assert_output --partial '"titleKeywords": "sponsored, ads"'
  assert_output --partial '"bodyKeywords": "tracking"'
  assert_output --partial '"urlKeywords": "\/utm_"'
}

@test "[$TESTSUITE] Delete filter" {
  ID=$(./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')

  ./occ news:feed:filter:set "$user" "$ID" --title "sponsored"

  run ./occ news:feed:filter:delete "$user" "$ID"

  assert_success

  run ./occ news:feed:filter:get "$user" "$ID"

  assert_success
  assert_output --partial '"id": null'
  assert_output --partial '"titleKeywords": ""'
}

@test "[$TESTSUITE] Set rejects overlong keyword payload" {
  ID=$(./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')
  LONG=$(head -c 129 < /dev/zero | tr '\0' 'a')

  run ./occ news:feed:filter:set "$user" "$ID" --title "$LONG"

  assert_failure
  assert_output --partial 'exceeds max length'
}
