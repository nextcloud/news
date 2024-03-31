#!/usr/bin/env bats

setup(){
  load "../test_helper/bats-support/load"
  load "../test_helper/bats-assert/load"
  load "helpers/settings"
}

TESTSUITE="Feeds"

teardown() {
  ID_LIST=($(./occ news:feed:list 'admin' | grep -Po '"id": \K([0-9]+)' | tr '\n' ' '))
  for ID in $ID_LIST; do
    ./occ news:feed:delete "$user" "$ID" -v
  done
}

@test "[$TESTSUITE] Create new" {
  run "./occ" news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"
  assert_success

  assert_output --partial "Something-${BATS_SUITE_TEST_NUMBER}"
}

@test "[$TESTSUITE] Add feed without GUIDs" {
  run ./occ news:feed:add "$user" "$NO_GUID_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"
  echo "Attention! Are the dates of the feed older than 'one year ago'? If so this is not a bug, adjust the dates. #2201"
  assert_failure

  assert_output "Malformed feed: item has no GUID"
}

@test "[$TESTSUITE] List all" {
  ./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"

  run ./occ news:feed:list "$user"
  assert_success

  assert_output --partial "Something-${BATS_SUITE_TEST_NUMBER}"
}

# Test if Feed-Logo is used if available (NC_FEED) and if favicon is used if no logo is provided (HEISE_FEED)
@test "[$TESTSUITE] Favicon" {

  ./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"
  ./occ news:feed:add "$user" "$HEISE_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"

  run ./occ news:feed:list "$user"
  assert_success
    
  assert_output --partial '"faviconLink": "http:\/\/localhost:8090\/logo.png",'
  assert_output --partial  '"faviconLink": "http:\/\/localhost:8090\/favicon.ico'
}

@test "[$TESTSUITE] List all items" {
  ID=$(./occ news:feed:add "$user" "https://github.com/nextcloud/news/releases.atom" --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')

  TAG=$(curl --silent "https://api.github.com/repos/nextcloud/news/releases/latest" | grep '"tag_name":' | sed -E 's/.*"([^"]+)".*/\1/')

  run ./occ news:item:list-feed "$user" "$ID" --limit 200
  assert_success

  assert_output --partial $TAG
}

@test "[$TESTSUITE] Read all" {
  ID=$(./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')

  run ./occ news:feed:read "$user" "$ID" -v

  assert_output --partial "items as read"
  
  # Needed for some reason because the teardown doesn't work after this step.
  run ./occ news:feed:delete "$user" "$ID" -v
  assert_success
}

@test "[$TESTSUITE] Delete one" {
  ID=$(./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')

  run ./occ news:feed:list "$user"
  assert_success

  run ./occ news:feed:delete "$user" "$ID"
  assert_success
}
