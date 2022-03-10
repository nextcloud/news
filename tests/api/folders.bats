#!/usr/bin/env bats

setup() {
  load "../test_helper/bats-support/load"
  load "../test_helper/bats-assert/load"
  load "helpers/settings"
}

TESTSUITE="Folders"


@test "[$TESTSUITE] Create new" {
  run http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/folders name=news-${BATS_SUITE_TEST_NUMBER}
  
  assert_output --partial "\"name\":\"news-${BATS_SUITE_TEST_NUMBER}\","
}
