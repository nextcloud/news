#!/usr/bin/env bats

setup() {
  load "../test_helper/bats-support/load"
  load "../test_helper/bats-assert/load"
  load "helpers/settings"
  
  if test -f "tests/api/helpers/settings-override.bash"; then
    load "helpers/settings-override"
  fi
}

TESTSUITE="Items"

teardown() {
  # delete all feeds
  FEED_IDS=($(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/feeds | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))
  for i in $FEED_IDS; do
    http --ignore-stdin -b -a ${user}:${user} DELETE ${BASE_URLv1}/feeds/$i
  done

  # delete all folders
  FOLDER_IDS=($(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/folders | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))
  for i in $FOLDER_IDS; do
    http --ignore-stdin -b -a ${user}:${user} DELETE ${BASE_URLv1}/folders/$i
  done
}

@test "[$TESTSUITE] Read empty" {
  run http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/items
  
  assert_output --partial "\"items\":[]"
}

@test "[$TESTSUITE] Read 5" {
  http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/feeds url=$NC_FEED

  ID_LIST=($(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/items batchSize=5 | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  output=${#ID_LIST[@]}
  
  assert_output --partial "5"
}