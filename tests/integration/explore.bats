#!/usr/bin/env bats

load "helpers/settings"

TESTSUITE="Explore"

@test "[$TESTSUITE] Create new" {
  curl --fail "$NC_FEED"

  run ./occ news:generate-explore --votes 100 "$NC_FEED"
  [ "$status" -eq 0 ]
}