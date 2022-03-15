#!/usr/bin/env bats

setup() {
  load "../test_helper/bats-support/load"
  load "../test_helper/bats-assert/load"
  load "helpers/settings"
}

TESTSUITE="Folders"

teardown() {
  ID=$(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/folders | grep -Po '"id":\K([0-9]+)' | tr '\n' ' ')
  for i in $ID; do
    http --ignore-stdin -b -a ${user}:${user} DELETE ${BASE_URLv1}/folders/$i
  done
}

@test "[$TESTSUITE] Read empty" {
  run http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/folders
  
  assert_output --partial "\"folders\":[]"
}

@test "[$TESTSUITE] Create new" {
  run http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/folders name=news-${BATS_SUITE_TEST_NUMBER}
  
  assert_output --partial "\"name\":\"news-${BATS_SUITE_TEST_NUMBER}\","
}

@test "[$TESTSUITE] Delete folder" {
  ID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/folders name=news-${BATS_SUITE_TEST_NUMBER} | grep -Po '"id":\K([0-9]+)')

  run http --ignore-stdin -b -a ${user}:${user} DELETE ${BASE_URLv1}/folders/$ID
  
  assert_output "[]"
}

@test "[$TESTSUITE] Rename folder" {
  ID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/folders name=news-${BATS_SUITE_TEST_NUMBER} | grep -Po '"id":\K([0-9]+)')

  # Rename folder
  http --ignore-stdin -b -a ${user}:${user} PUT ${BASE_URLv1}/folders/$ID name=rename-${BATS_SUITE_TEST_NUMBER}
  
  run http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/folders

  assert_output --partial "\"name\":\"rename-${BATS_SUITE_TEST_NUMBER}\","
}