#!/usr/bin/env bats

setup() {
  load "../test_helper/bats-support/load"
  load "../test_helper/bats-assert/load"
  load "helpers/settings"
}

TESTSUITE="Feeds"

teardown() {
  # delete all feeds
  ID=$(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/feeds | grep -Po '"id":\K([0-9]+)' | tr '\n' ' ')
  for i in $ID; do
    http --ignore-stdin -b -a ${user}:${user} DELETE ${BASE_URLv1}/feeds/$i
  done

  # delete all folders
  ID=$(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/folders | grep -Po '"id":\K([0-9]+)' | tr '\n' ' ')
  for i in $ID; do
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
  
  # self reference of feed is used here
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
  
  # self reference of feed is used here
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
  
  # self reference of feed is used here
  assert_output null
}

@test "[$TESTSUITE] Rename feed" {
  # create folder and store id
  FEEDID=$(http --ignore-stdin -b -a ${user}:${user} POST ${BASE_URLv1}/feeds url=$NC_FEED | grep -Po '"id":\K([0-9]+)')
  
  # rename feed, returns nothing
  http --ignore-stdin -b -a ${user}:${user} PUT ${BASE_URLv1}/feeds/$FEEDID/rename feedTitle="Great Title"
  # run is not working here.
  output=$(http --ignore-stdin -b -a ${user}:${user} GET ${BASE_URLv1}/feeds | jq '.feeds | .[0].title')
  
  # self reference of feed is used here
  assert_output '"Great Title"'
}