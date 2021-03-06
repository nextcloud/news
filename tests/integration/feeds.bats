#!/usr/bin/env bats

load "helpers/settings"

TESTSUITE="Feeds"

teardown() {
  ID=$(./occ news:feed:list 'admin' | grep "Something-${BATS_SUITE_TEST_NUMBER}" -2  | head -1 | grep -oE '[0-9]*')
  if [ -n "$ID" ]; then
    ./occ news:feed:delete "$user" "$ID"
  fi
}

@test "[$TESTSUITE] Create new" {
  run "./occ" news:feed:add "$user" "$NC_FEED"
  [ "$status" -eq 0 ]

  if ! echo "$output" | grep '"ID":'; then
    ret_status=$?
    echo "Feed ID not returned"
    return $ret_status
  fi
}

@test "[$TESTSUITE] List all" {
  ./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"

  run ./occ news:feed:list "$user"
  [ "$status" -eq 0 ]

  if ! echo "$output" | grep "Something-${BATS_SUITE_TEST_NUMBER}"; then
    ret_status=$?
    echo "Feed not found in list"
    return $ret_status
  fi

  if ! echo "$output" | grep -F '"faviconLink": "https:\/\/nextcloud.com\/media\/screenshot-150x150.png"'; then
    ret_status=$?
    echo "Favicon not found in list"
    return $ret_status
  fi
}

@test "[$TESTSUITE] List all items" {
  ./occ news:feed:add "$user" "https://github.com/nextcloud/news/releases.atom" --title "Something-${BATS_SUITE_TEST_NUMBER}"

  TAG=$(curl --silent "https://api.github.com/repos/nextcloud/news/releases/latest" | grep '"tag_name":' | sed -E 's/.*"([^"]+)".*/\1/')
  ID=$(./occ news:feed:list 'admin' | grep 'github\.com' -1  | head -1 | grep -oE '[0-9]*')

  run ./occ news:item:list-feed "$user" "$ID" --limit 200
  [ "$status" -eq 0 ]

  if ! echo "$output" | grep "$TAG"; then
    ret_status=$?
    echo "Release not found in list"
    return $ret_status
  fi
}

@test "[$TESTSUITE] Delete all" {
  ./occ news:feed:add "$user" "$NC_FEED" --title "Something-${BATS_SUITE_TEST_NUMBER}"

  run ./occ news:feed:list "$user"
  [ "$status" -eq 0 ]

  echo "$output" | grep "Something-${BATS_SUITE_TEST_NUMBER}"

  ID=$(./occ news:feed:list 'admin' | grep "Something-${BATS_SUITE_TEST_NUMBER}" -2  | head -1 | grep -oE '[0-9]*')
  run ./occ news:feed:delete "$user" "$ID"
  [ "$status" -eq 0 ]
}