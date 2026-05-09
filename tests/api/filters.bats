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

TESTSUITE="Filters"
BASE_URLv3="${NC_HOST}/index.php/apps/news/api/v1-3"

teardown() {
  FEED_IDS=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv3}/feeds | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))
  for i in $FEED_IDS; do
    http --ignore-stdin -b -a ${user}:${APP_PASSWORD} DELETE ${BASE_URLv3}/feeds/$i > /dev/null
  done

  FOLDER_IDS=($(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv3}/folders | grep -Po '"id":\K([0-9]+)' | tr '\n' ' '))
  for i in $FOLDER_IDS; do
    http --ignore-stdin -b -a ${user}:${APP_PASSWORD} DELETE ${BASE_URLv3}/folders/$i > /dev/null
  done
}

@test "[$TESTSUITE] Read empty filter" {
  FEEDID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv3}/feeds url=$NC_FEED | grep -Po '"id":\K([0-9]+)')

  run http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv3}/feeds/$FEEDID/filter

  assert_output --partial '"filter":{"feedId":'$FEEDID
  assert_output --partial '"titleKeywords":""'
}

@test "[$TESTSUITE] Save and read filter" {
  FEEDID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv3}/feeds url=$NC_FEED | grep -Po '"id":\K([0-9]+)')

  run http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv3}/feeds/$FEEDID/filter titleKeywords='sponsored, ads' bodyKeywords='tracking' urlKeywords='/utm_'

  assert_output --partial '"feedId":'$FEEDID
  assert_output --partial '"titleKeywords":"sponsored, ads"'

  run http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv3}/feeds/$FEEDID/filter

  assert_output --partial '"titleKeywords":"sponsored, ads"'
  assert_output --partial '"bodyKeywords":"tracking"'
  assert_output --partial '"urlKeywords":"\/utm_"'
}

@test "[$TESTSUITE] Delete filter" {
  FEEDID=$(http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv3}/feeds url=$NC_FEED | grep -Po '"id":\K([0-9]+)')

  http --ignore-stdin -b -a ${user}:${APP_PASSWORD} POST ${BASE_URLv3}/feeds/$FEEDID/filter titleKeywords='sponsored' > /dev/null

  run http --ignore-stdin -b -a ${user}:${APP_PASSWORD} DELETE ${BASE_URLv3}/feeds/$FEEDID/filter

  assert_output '[]'

  run http --ignore-stdin -b -a ${user}:${APP_PASSWORD} GET ${BASE_URLv3}/feeds/$FEEDID/filter

  assert_output --partial '"filter":{"feedId":'$FEEDID
  assert_output --partial '"titleKeywords":""'
}
