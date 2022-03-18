#!/usr/bin/env bats

setup() {
  load "../test_helper/bats-support/load"
  load "../test_helper/bats-assert/load"
  load "helpers/settings"

  if test -f "tests/api/helpers/settings-override.bash"; then
    load "helpers/settings-override"
  fi
  
}

TESTSUITE="Folders"

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

@test "[$TESTSUITE] Mark all items as read" {
  # create folder and feed in folder
  FOLDER_ID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/folders name=news-${BATS_SUITE_TEST_NUMBER} | grep -Po '"id":\K([0-9]+)')
  FEED_ID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/feeds url=$NC_FEED folderId=$FOLDER_ID | grep -Po '"id":\K([0-9]+)')
  
  ID_LIST=($(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/items id=$FEEDID | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  # get biggest item ID
  max=${ID_LIST[0]}
  for n in "${ID_LIST[@]}" ; do
      ((n > max)) && max=$n
  done
  
  # mark all items of feed as read, returns nothing
  STATUS_CODE=$(http --ignore-stdin -hdo /tmp/body -a ${user}:${user} PUT ${BASE_URLv1}/folders/$FOLDER_ID/read newestItemId="$max" 2>&1| grep HTTP/)

  # collect unread status
  unread=$(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/items id=$FEEDID | grep -Po '"unread":\K((true)|(false))' | tr '\n' ' ')
  
  for n in "${unread[@]}" ; do
      if $n
      then
        echo "Item was not marked as read"
        echo $STATUS_CODE
        false
      fi
  done
}

