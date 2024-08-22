#!/usr/bin/env bats

setup_file(){
  load "helpers/settings"

  if test -f "tests/api/helpers/settings-override.bash"; then
    load "helpers/settings-override"
  fi

  export APP_PASSWORD=$(NC_PASS=${user} ./occ user:add-app-password ${user} --password-from-env | grep -Po '([A-Z|a-z|0-9]{72})')
}

teardown_file(){
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} DELETE ${NC_HOST}/ocs/v2.php/core/apppassword OCS-APIRequest:true
}

setup() {
  load "../test_helper/bats-support/load"
  load "../test_helper/bats-assert/load"
}

TESTSUITE="Feeds"

teardown() {
  # delete all feeds
  ID_LIST=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/feeds | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))
  for i in $ID_LIST; do
    http --ignore-stdin -b -a ${user}:${APP_PASSWORD} DELETE ${BASE_URLv1}/feeds/$i > /dev/null
  done

  # delete all folders
  ID_LIST=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/folders | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))
  for i in $ID_LIST; do
    http --ignore-stdin -b -a ${user}:${APP_PASSWORD} DELETE ${BASE_URLv1}/folders/$i > /dev/null
  done
}

@test "[$TESTSUITE] Read empty" {
  run http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/feeds

  assert_output --partial "\"feeds\":[]"
  assert_output --partial "\"starredCount\":0"
}

@test "[$TESTSUITE] Create new" {
  # run is not working here.
  output=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$NC_FEED | jq '.feeds | .[0].url')

  assert_output '"http://localhost:8090/Nextcloud.rss"'
}

@test "[$TESTSUITE] Create new inside folder" {
  # create folder and store id
  ID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/folders name=news-${BATS_SUITE_TEST_NUMBER} | grep -Po '"id":\K([0-9]+)')

  # run is not working here.
  output=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$NC_FEED folderId=$ID | jq '.feeds | .[0].folderId')

  # check if ID matches
  assert_output "$ID"
}

@test "[$TESTSUITE] Delete one" {
  ID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$NC_FEED | grep -Po '"id":\K([0-9]+)')

  run http --ignore-stdin -b -a ${user}:${APP_PASSWORD} DELETE ${BASE_URLv1}/feeds/$ID

  assert_output "[]"
}

@test "[$TESTSUITE] Move feed to different folder" {
  # create folders and store ids
  FIRST_FOLDER_ID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/folders name=news-${BATS_SUITE_TEST_NUMBER} | grep -Po '"id":\K([0-9]+)')
  SECOND_FOLDER_ID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/folders name=news-${BATS_SUITE_TEST_NUMBER} | grep -Po '"id":\K([0-9]+)')

  FEEDID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$NC_FEED folderId=$FIRST_FOLDER_ID | grep -Po '"id":\K([0-9]+)')

  # move feed, returns nothing
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} PUT ${BASE_URLv1}/feeds/$FEEDID/move folderId=$SECOND_FOLDER_ID

  # run is not working here.
  output=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/feeds | jq '.feeds | .[0].folderId')

  # look for second folder id
  assert_output "$SECOND_FOLDER_ID"
}

@test "[$TESTSUITE] Move feed to root" {
  # create folder and store id
  FOLDER_ID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/folders name=news-${BATS_SUITE_TEST_NUMBER} | grep -Po '"id":\K([0-9]+)')

  FEEDID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$NC_FEED folderId=$FOLDER_ID | grep -Po '"id":\K([0-9]+)')

  # move feed to "null", returns nothing
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} PUT ${BASE_URLv1}/feeds/$FEEDID/move folderId=null

  # run is not working here.
  output=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/feeds | jq '.feeds | .[0].folderId')

  # new "folder" should be null
  assert_output null
}

@test "[$TESTSUITE] Rename feed" {
  # create feed and store id
  FEEDID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$NC_FEED | grep -Po '"id":\K([0-9]+)')

  # rename feed, returns nothing
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} PUT ${BASE_URLv1}/feeds/$FEEDID/rename feedTitle="Great Title"

  # run is not working here.
  output=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/feeds | jq '.feeds | .[0].title')

  # Check if title matches
  assert_output '"Great Title"'
}

@test "[$TESTSUITE] Mark all items as read" {
  # create feed and store id
  FEEDID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$NC_FEED | grep -Po '"id":\K([0-9]+)')

  ID_LIST=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items id=$FEEDID | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  # get biggest item ID
  max=${ID_LIST[0]}
  for n in "${ID_LIST[@]}" ; do
      ((n > max)) && max=$n
  done

  # mark all items of feed as read, returns nothing
  STATUS_CODE=$(http --ignore-stdin -hdo /tmp/body -a ${user}:${APP_PASSWORD} PUT ${BASE_URLv1}/feeds/$FEEDID/read newestItemId="$max" 2>&1| grep HTTP/)

  # collect unread status
  unread=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items id=$FEEDID | grep -Po '"unread":\K((true)|(false))' | tr '\n' ' ')

  for n in "${unread[@]}" ; do
      if $n
      then
        echo "Item was not marked as read"
        echo $STATUS_CODE
        false
      fi
  done
}

