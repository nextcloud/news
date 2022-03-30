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

# TODO GET /items has more options that could be tested.

@test "[$TESTSUITE] Check updated" {
  FEEDID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/feeds url=$NC_FEED | grep -Po '"id":\K([0-9]+)')
  ID_LIST=($(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/items id=$FEEDID | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  # get biggest item ID
  max=${ID_LIST[0]}
  for n in "${ID_LIST[@]}" ; do
      ((n > max)) && max=$n
  done
  
  SYNC_TIME=$(date +%s)

  # mark all items of feed as read, returns nothing (other client marks items as read)
  STATUS_CODE=$(http --ignore-stdin -hdo /tmp/body -a ${user}:${user} PUT ${BASE_URLv1}/feeds/$FEEDID/read newestItemId="$max" 2>&1| grep HTTP/)

  # client 2 checks for updates since last sync
  UPDATED_ITEMS=($(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/items/updated id=$FEEDID lastModified=$SYNC_TIME | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  assert_equal ${#ID_LIST[@]} ${#UPDATED_ITEMS[@]}
}