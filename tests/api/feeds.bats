#!/usr/bin/env bats

setup() {
  load "../test_helper/bats-support/load"
  load "../test_helper/bats-assert/load"
  load "helpers/settings"
}

TESTSUITE="Feeds"

teardown() {
  ID=$(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/feeds | grep -Po '"id":\K([0-9]+)' | tr '\n' ' ')
  for i in $ID; do
    http --ignore-stdin -b -a ${user}:${user} DELETE ${BASE_URLv1}/feeds/$i
  done
}

@test "[$TESTSUITE] Read empty" {
  run http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/feeds
  
  assert_output --partial "\"feeds\":[]"
  assert_output --partial "\"starredCount\":0"
}

@test "[$TESTSUITE] Create new" {
  # $() is needed in this case otherwise bats fails to fetch the output...
  run $(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/feeds url=$NC_FEED | jq '.feeds | .[0].url')
  
  # self reference of feed is used here
  assert_output --partial "https://nextcloud.com/blog/feed/"
}

@test "[$TESTSUITE] Delete one" {
  ID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/feeds url=$NC_FEED | jq '.feeds | .[0].id')
  
  run http --ignore-stdin -b -a ${user}:${user} DELETE ${BASE_URLv1}/feeds/$ID
  
  assert_output --partial "[]"
}