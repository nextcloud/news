#!/usr/bin/env bats

setup() {
  load "../test_helper/bats-support/load"
  load "../test_helper/bats-assert/load"
  load "helpers/settings"
}

TESTSUITE="Feeds"

teardown() {
  # delete all feeds
  ID_LIST=($(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/feeds | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))
  for i in $ID_LIST; do
    http --ignore-stdin -b -a ${user}:${user} DELETE ${BASE_URLv1}/feeds/$i
  done

  # delete all folders
  ID_LIST=($(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/folders | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))
  for i in $ID_LIST; do
    http --ignore-stdin -b -a ${user}:${user} DELETE ${BASE_URLv1}/folders/$i
  done
}

@test "[$TESTSUITE] Read empty" {
  run http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/feeds
  
  assert_output --partial "\"feeds\":[]"
  assert_output --partial "\"starredCount\":0"
}

@test "[$TESTSUITE] Create new" {
  # run is not working here.
  output=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/feeds url=$NC_FEED | jq '.feeds | .[0].url')
  
  # self reference of feed is used here
  assert_output '"https://nextcloud.com/blog/feed/"'
}

@test "[$TESTSUITE] Create new inside folder" {
  # create folder and store id
  ID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/folders name=news-${BATS_SUITE_TEST_NUMBER} | grep -Po '"id":\K([0-9]+)')

  # run is not working here.
  output=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/feeds url=$NC_FEED folderId=$ID | jq '.feeds | .[0].folderId')
  
  # check if ID matches
  assert_output "$ID"
}

@test "[$TESTSUITE] Delete one" {
  ID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/feeds url=$NC_FEED | grep -Po '"id":\K([0-9]+)')
  
  run http --ignore-stdin -b -a ${user}:${user} DELETE ${BASE_URLv1}/feeds/$ID
  
  assert_output --partial "[]"
}

@test "[$TESTSUITE] Move feed to different folder" {
  # create folders and store ids
  FIRST_FOLDER_ID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/folders name=news-${BATS_SUITE_TEST_NUMBER} | grep -Po '"id":\K([0-9]+)')
  SECCOND_FOLDER_ID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/folders name=news-${BATS_SUITE_TEST_NUMBER} | grep -Po '"id":\K([0-9]+)')
  
  FEEDID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/feeds url=$NC_FEED folderId=$FIRST_FOLDER_ID | grep -Po '"id":\K([0-9]+)')
  
  # move feed, returns nothing
  http --ignore-stdin -b -a ${user}:${user} PUT ${BASE_URLv1}/feeds/$FEEDID/move folderId=$SECCOND_FOLDER_ID

  # run is not working here.
  output=$(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/feeds | jq '.feeds | .[0].folderId')
  
  # look for second folder id
  assert_output "$SECCOND_FOLDER_ID"
}

@test "[$TESTSUITE] Move feed to root" {
  # create folder and store id
  FOLDER_ID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/folders name=news-${BATS_SUITE_TEST_NUMBER} | grep -Po '"id":\K([0-9]+)')

  FEEDID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/feeds url=$NC_FEED folderId=$FOLDER_ID | grep -Po '"id":\K([0-9]+)')
  
  # move feed, returns nothing
  http --ignore-stdin -b -a ${user}:${user} PUT ${BASE_URLv1}/feeds/$FEEDID/move folderId=null
  # run is not working here.
  output=$(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/feeds | jq '.feeds | .[0].folderId')
  
  # new "folder" should be null
  assert_output null
}

@test "[$TESTSUITE] Rename feed" {
  # create feed and store id
  FEEDID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/feeds url=$NC_FEED | grep -Po '"id":\K([0-9]+)')
  
  # rename feed, returns nothing
  http --ignore-stdin -b -a ${user}:${user} PUT ${BASE_URLv1}/feeds/$FEEDID/rename feedTitle="Great Title"
  # run is not working here.
  output=$(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/feeds | jq '.feeds | .[0].title')
  
  # Check if title matches
  assert_output '"Great Title"'
}

@test "[$TESTSUITE] Mark all items as read" {
  # create feed and store id
  FEEDID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/feeds url=$NC_FEED | grep -Po '"id":\K([0-9]+)')
  
  ID_LIST=($(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/items id=$FEEDID | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  # get biggest item ID
  max=${ID_LIST[0]}
  for n in "${ID_LIST[@]}" ; do
      ((n > max)) && max=$n
  done
  
  # mark all items of feed as read, returns nothing
  http --ignore-stdin -b -a ${user}:${user} PUT ${BASE_URLv1}/feeds/$FEEDID/read newestItemId="$max"

  # collect unread status
  unread=$(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/items id=$FEEDID | grep -Po '"unread":\K((true)|(false))' | tr '\n' ' ')
  
  for n in "${unread[@]}" ; do
      if $n
      then
        echo "Item was not marked as read"
        false
      fi
  done
}

