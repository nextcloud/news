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
    ./occ news:feed:delete "$user" "$ID"
  done
}

@test "[$TESTSUITE] Create new" {
  run "./occ" news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"
  assert_success

  assert_output --partial "Something-${BATS_SUITE_TEST_NUMBER}"
}

@test "[$TESTSUITE] Add feed without GUIDs" {
  run ./occ news:feed:add "$user" "$NO_GUID_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"
  assert_failure

  assert_output "Malformed feed: item has no GUID"
}

@test "[$TESTSUITE] List all" {
  ./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"

  run ./occ news:feed:list "$user"
  assert_success

  assert_output --partial "Something-${BATS_SUITE_TEST_NUMBER}"
}

@test "[$TESTSUITE] Favicon" {
  ./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"
  ./occ news:feed:add "$user" "$HEISE_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"

  run ./occ news:feed:list "$user"
  assert_success
    
  assert_output --partial '"faviconLink": "https:\/\/nextcloud.com\/wp-content\/uploads\/2022\/03\/favicon.png",'
  assert_output --partial  '"faviconLink": "https:\/\/www.heise.de\/favicon.ico?v=JykvN0w9Ye",'
}

@test "[$TESTSUITE] List all items" {
  ./occ news:feed:add "$user" "https://github.com/nextcloud/news/releases.atom" --title "Something-${BATS_SUITE_TEST_NUMBER}"

  TAG=$(curl --silent "https://api.github.com/repos/nextcloud/news/releases/latest" | grep '"tag_name":' | sed -E 's/.*"([^"]+)".*/\1/')
  ID=$(./occ news:feed:list 'admin' | grep 'github\.com' -1  | head -1 | grep -oE '[0-9]*')

  run ./occ news:item:list-feed "$user" "$ID" --limit 200
  assert_success

  assert_output --partial $TAG
}

@test "[$TESTSUITE] Read all" {
  run ./occ news:feed:list "$user"
  assert_output "[]"
  ID=$(./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"| grep "Something-${BATS_SUITE_TEST_NUMBER}" -2  | head -1 | grep -oE '[0-9]*')

  run ./occ news:feed:read "$user" "$ID" -v

  assert_output --partial "items as read"
}

@test "[$TESTSUITE] Delete one" {
  run ./occ news:feed:list "$user"
  assert_output "[]"

  ./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"

  run ./occ news:feed:list "$user"
  assert_success

  echo "$output" | grep "Something-${BATS_SUITE_TEST_NUMBER}"

  ID=$(./occ news:feed:list 'admin' | grep "Something-${BATS_SUITE_TEST_NUMBER}" -2  | head -1 | grep -oE '[0-9]*')
  run ./occ news:feed:delete "$user" "$ID"
  assert_success
}
