#!/usr/bin/env bats


TESTSUITE="Items"

setup() {
  load "../test_helper/bats-support/load"
  load "../test_helper/bats-assert/load"
  load "helpers/settings"
  TAG=$(curl --silent "https://api.github.com/repos/nextcloud/news/releases/latest" | grep '"tag_name":' | sed -E 's/.*"([^"]+)".*/\1/')
  
}

teardown(){
  ID_LIST=($(./occ news:feed:list 'admin' | grep -Po '"id": \K([0-9]+)' | tr '\n' ' '))
  for ID in $ID_LIST; do
    ./occ news:feed:delete "$user" "$ID"
  done
}

@test "[$TESTSUITE] List 200 items in feed" {
  ID=$(./occ news:feed:add "$user" "https://github.com/nextcloud/news/releases.atom" --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')
  run ./occ news:item:list-feed "$user" "$ID" --limit 200
  assert_success

  assert_output --partial $TAG
}

@test "[$TESTSUITE] List all items in feed" {
  ID=$(./occ news:feed:add "$user" "https://github.com/nextcloud/news/releases.atom" --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')
  run ./occ news:item:list-feed "$user" "$ID" --limit 0
  assert_success

  assert_output --partial $TAG
}

@test "[$TESTSUITE] List 200 items in folder" {
  ID=$(./occ news:feed:add "$user" "https://github.com/nextcloud/news/releases.atom" --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')
  run ./occ news:item:list-folder "$user" --limit 200
  assert_success

  assert_output --partial $TAG
}

@test "[$TESTSUITE] List all items in folder" {
  ID=$(./occ news:feed:add "$user" "https://github.com/nextcloud/news/releases.atom" --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')
  run ./occ news:item:list-folder "$user" --limit 0
  assert_success

  assert_output --partial $TAG
}

@test "[$TESTSUITE] List 200 items" {
  ID=$(./occ news:feed:add "$user" "https://github.com/nextcloud/news/releases.atom" --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')
  run ./occ news:item:list "$user" --limit 200
  assert_success

  assert_output --partial $TAG
}

@test "[$TESTSUITE] List all items" {
  ID=$(./occ news:feed:add "$user" "https://github.com/nextcloud/news/releases.atom" --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')
  run ./occ news:item:list "$user" --limit 0
  assert_success

  assert_output --partial $TAG
}

@test "[$TESTSUITE] Test author fallback" {
  ID=$(./occ news:feed:add "$user" $HEISE_FEED --title "Something-${BATS_SUITE_TEST_NUMBER}" | grep -Po '"id": \K([0-9]+)')

  run ./occ news:item:list-feed "$user" "$ID" --limit 200
  assert_success


  assert_output --partial '"author": "heise online",'
}
