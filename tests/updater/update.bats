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
  php ${BATS_TEST_DIRNAME}/../test_helper/php-feed-generator/feed-generator.php -a 10 -f ${BATS_TEST_DIRNAME}/../test_helper/feeds/test.xml
}

TESTSUITE="Update"

#
# This testsuite is not intended to test the api but rather the update and purge functions.
#

teardown() {
  # delete all feeds
  FEED_IDS=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/feeds | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))
  for i in $FEED_IDS; do
    http --ignore-stdin -b -a ${user}:${APP_PASSWORD} DELETE ${BASE_URLv1}/feeds/$i > /dev/null
  done

  # delete all folders
  FOLDER_IDS=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/folders | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))
  for i in $FOLDER_IDS; do
    http --ignore-stdin -b -a ${user}:${APP_PASSWORD} DELETE ${BASE_URLv1}/folders/$i > /dev/null
  done
}

@test "[$TESTSUITE] Test simple update" {
  # Create Feed
  FEEDID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$TEST_FEED | grep -Po '"id":\K([0-9]+)')
  
  sleep 2
  
  # Get Items
  ID_LIST1=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))
  # Trigger Update
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/feeds/update userId=${user} feedId=$FEEDID

  sleep 2

  # Get Items again
  ID_LIST2=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  assert_equal "${ID_LIST1[*]}" "${ID_LIST2[*]}"
}

@test "[$TESTSUITE] Test simple update with new content" {
  # Create Feed
  FEEDID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$TEST_FEED | grep -Po '"id":\K([0-9]+)')
  
  sleep 2
  
  # Get Items
  ID_LIST1=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  php ${BATS_TEST_DIRNAME}/../test_helper/php-feed-generator/feed-generator.php -a 15 -s 9 -f ${BATS_TEST_DIRNAME}/../test_helper/feeds/test.xml

  # Trigger Update
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/feeds/update userId=${user} feedId=$FEEDID
  
  sleep 2

  # Get Items again
  ID_LIST2=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  output="${ID_LIST2[*]}"

  # Check that they are not equal but that they match partially.
  assert_not_equal "${ID_LIST1[*]}" "${ID_LIST2[*]}"
  assert_output --partial "${ID_LIST1[*]}"
}

@test "[$TESTSUITE] Test purge with small feed" {
  # Generate Feed with 210 items.
  php ${BATS_TEST_DIRNAME}/../test_helper/php-feed-generator/feed-generator.php -a 50 -s 0 -f ${BATS_TEST_DIRNAME}/../test_helper/feeds/test.xml
  # Create Feed
  FEEDID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$TEST_FEED | grep -Po '"id":\K([0-9]+)')

  sleep 2

  # Trigger Update
  php ${BATS_TEST_DIRNAME}/../test_helper/php-feed-generator/feed-generator.php -a 50 -s 50 -f ${BATS_TEST_DIRNAME}/../test_helper/feeds/test.xml
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/feeds/update userId=${user} feedId=$FEEDID

  sleep 2

  # Trigger Update
  php ${BATS_TEST_DIRNAME}/../test_helper/php-feed-generator/feed-generator.php -a 50 -s 100 -f ${BATS_TEST_DIRNAME}/../test_helper/feeds/test.xml
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/feeds/update userId=${user} feedId=$FEEDID

  sleep 2

  # Trigger Update
  php ${BATS_TEST_DIRNAME}/../test_helper/php-feed-generator/feed-generator.php -a 50 -s 150 -f ${BATS_TEST_DIRNAME}/../test_helper/feeds/test.xml
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/feeds/update userId=${user} feedId=$FEEDID

  sleep 2

  # Trigger Update
  php ${BATS_TEST_DIRNAME}/../test_helper/php-feed-generator/feed-generator.php -a 50 -s 200 -f ${BATS_TEST_DIRNAME}/../test_helper/feeds/test.xml
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/feeds/update userId=${user} feedId=$FEEDID

  sleep 2

  # Get Items
  ID_LIST=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  # get biggest item ID
  max=${ID_LIST[0]}
  for n in "${ID_LIST[@]}" ; do
      ((n > max)) && max=$n
  done
  
  # mark all items of feed as read, returns nothing
  STATUS_CODE=$(http --ignore-stdin -hdo /tmp/body -a ${user}:${APP_PASSWORD} PUT ${BASE_URLv1}/feeds/$FEEDID/read newestItemId="$max" 2>&1| grep -Po '(?<=HTTP\/1\.1 )[0-9]{3}(?= OK)')
  
  # cleanup, purge items
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/cleanup/after-update

  # Get unread Items, should be empty
  output="$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items getRead=false | grep -Po '"id":\K([0-9]+)' | tr '\n' ' ')"

  # Get all items, also read items
  ID_LIST2=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  assert_equal $STATUS_CODE 200
  # check if amount is as expected
  assert_equal "${#ID_LIST2[@]}" 200

  # unread items should be empty
  assert_output ""
}

@test "[$TESTSUITE] Test purge with more items than default limit 200" {
  # Generate Feed with 210 items.
  php ${BATS_TEST_DIRNAME}/../test_helper/php-feed-generator/feed-generator.php -a 210 -f ${BATS_TEST_DIRNAME}/../test_helper/feeds/test.xml
  # Create Feed
  FEEDID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$TEST_FEED | grep -Po '"id":\K([0-9]+)')
  
  sleep 2
  
  # Get Items
  ID_LIST=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  # get biggest item ID
  max=${ID_LIST[0]}
  for n in "${ID_LIST[@]}" ; do
      ((n > max)) && max=$n
  done
  
  # mark all items of feed as read, returns nothing
  STATUS_CODE=$(http --ignore-stdin -hdo /tmp/body -a ${user}:${APP_PASSWORD} PUT ${BASE_URLv1}/feeds/$FEEDID/read newestItemId="$max" 2>&1| grep -Po '(?<=HTTP\/1\.1 )[0-9]{3}(?= OK)')
  
  # cleanup, purge items
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/cleanup/after-update

  # Get unread Items, should be empty
  output="$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items getRead=false | grep -Po '"id":\K([0-9]+)' | tr '\n' ' ')"

  # Get all items, also read items
  ID_LIST2=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  assert_equal $STATUS_CODE 200
  # check if amount is as expected
  assert_equal "${#ID_LIST2[@]}" 210
  assert_output ""
}

@test "[$TESTSUITE] Test Update and pruge with feed item>200; items<200" {
  # Generate Feed with 210 items.
  php ${BATS_TEST_DIRNAME}/../test_helper/php-feed-generator/feed-generator.php -a 210 -f ${BATS_TEST_DIRNAME}/../test_helper/feeds/test.xml
  # Create Feed
  FEEDID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$TEST_FEED | grep -Po '"id":\K([0-9]+)')
  # Get Items
  ID_LIST=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  # get biggest item ID
  max=${ID_LIST[0]}
  for n in "${ID_LIST[@]}" ; do
      ((n > max)) && max=$n
  done
  
  # mark all items of feed as read, returns nothing
  STATUS_CODE=$(http --ignore-stdin -hdo /tmp/body -a ${user}:${APP_PASSWORD} PUT ${BASE_URLv1}/feeds/$FEEDID/read newestItemId="$max" 2>&1| grep -Po '(?<=HTTP\/1\.1 )[0-9]{3}(?= OK)')
  # cleanup, purge items
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/cleanup/after-update

  FIRST_UPDATE="$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items getRead=false | grep -Po '"id":\K([0-9]+)' | tr '\n' ' ')"

  assert_equal "${FIRST_UPDATE}" ""

  # Generate feed "update" items id 190 + 40 items = id 230
  php ${BATS_TEST_DIRNAME}/../test_helper/php-feed-generator/feed-generator.php -a 40 -s 190 -f ${BATS_TEST_DIRNAME}/../test_helper/feeds/test.xml
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/feeds/update userId=${user} feedId=$FEEDID

  # Get Items
  ID_LIST=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  # get biggest item ID
  max=${ID_LIST[0]}
  for n in "${ID_LIST[@]}" ; do
      ((n > max)) && max=$n
  done
  
  # mark all items of feed as read, returns nothing
  STATUS_CODE=$(http --ignore-stdin -hdo /tmp/body -a ${user}:${APP_PASSWORD} PUT ${BASE_URLv1}/feeds/$FEEDID/read newestItemId="$max" 2>&1| grep -Po '(?<=HTTP\/1\.1 )[0-9]{3}(?= OK)')

  SECOND_UPDATE="$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items getRead=false | grep -Po '"id":\K([0-9]+)' | tr '\n' ' ')"

  assert_equal "${SECOND_UPDATE}" ""

  # cleanup, purge items
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/cleanup/after-update

  # Get all items, also read items
  READ_ITEMS=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  # stays at the 210 limit https://github.com/nextcloud/news/blob/ec74c1b5f3712594c7ea2139c8dfdff15d1ef826/lib/Service/FeedServiceV2.php#L287
  assert_equal "${#READ_ITEMS[@]}" 210
}

@test "[$TESTSUITE] Test purge with two feeds with different item count limit" {
  # Generate Feed with 260 items.
  php ${BATS_TEST_DIRNAME}/../test_helper/php-feed-generator/feed-generator.php -a 260 -f ${BATS_TEST_DIRNAME}/../test_helper/feeds/feed1.xml
  # Generate Feed with 210 items.
  php ${BATS_TEST_DIRNAME}/../test_helper/php-feed-generator/feed-generator.php -a 210 -f ${BATS_TEST_DIRNAME}/../test_helper/feeds/feed2.xml
  
  # Create Feeds
  FEED1ID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$FEED1 | grep -Po '"id":\K([0-9]+)')
  FEED2ID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv1}/feeds url=$FEED2 | grep -Po '"id":\K([0-9]+)')
  # Get Items
  ID_LIST=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))

  # get biggest item ID, it is enough to do this one time
  max=${ID_LIST[0]}
  for n in "${ID_LIST[@]}" ; do
      ((n > max)) && max=$n
  done
  
  # mark all items of both feeds as read, returns nothing
  STATUS_CODE1=$(http --ignore-stdin -hdo /tmp/body -a ${user}:${APP_PASSWORD} PUT ${BASE_URLv1}/feeds/$FEED1ID/read newestItemId="$max" 2>&1| grep -Po '(?<=HTTP\/1\.1 )[0-9]{3}(?= OK)')
  STATUS_CODE2=$(http --ignore-stdin -hdo /tmp/body -a ${user}:${APP_PASSWORD} PUT ${BASE_URLv1}/feeds/$FEED2ID/read newestItemId="$max" 2>&1| grep -Po '(?<=HTTP\/1\.1 )[0-9]{3}(?= OK)')
  
  # cleanup, purge items
  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/cleanup/after-update

  # Get unread Items, should be empty
  output="$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items getRead=false | grep -Po '"id":\K([0-9]+)' | tr '\n' ' ')"

  # Get all items of Feed Nr. 1 and 2, including read items
  ID_LIST_FEED1=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items id="$FEED1ID" type=0 | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))
  ID_LIST_FEED2=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv1}/items id="$FEED2ID" type=0 | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))


  assert_equal $STATUS_CODE1 200
  assert_equal $STATUS_CODE2 200
  # check if amount is as expected
  assert_equal "${#ID_LIST_FEED1[@]}" 260
  assert_equal "${#ID_LIST_FEED2[@]}" 210
  assert_output ""
}