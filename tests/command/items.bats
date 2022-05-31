#!/usr/bin/env bats

load "helpers/settings"

TESTSUITE="Items"

setup() {
  ./occ news:feed:add "$user" "https://github.com/nextcloud/news/releases.atom" --title "Something-${BATS_SUITE_TEST_NUMBER}"

  TAG=$(curl --silent "https://api.github.com/repos/nextcloud/news/releases/latest" | grep '"tag_name":' | sed -E 's/.*"([^"]+)".*/\1/')
  ID=$(./occ news:feed:list 'admin' | grep 'github\.com' -1  | head -1 | grep -oE '[0-9]*')
}

teardown() {
  if [ -n "$ID" ]; then
    ./occ news:feed:delete "$user" "$ID"
  fi
}

@test "[$TESTSUITE] List 200 items in feed" {
  run ./occ news:item:list-feed "$user" "$ID" --limit 200
  [ "$status" -eq 0 ]

  if ! echo "$output" | grep "$TAG"; then
    ret_status=$?
    echo "Release not found in feed list"
    return $ret_status
  fi
}

@test "[$TESTSUITE] List all items in feed" {
  run ./occ news:item:list-feed "$user" "$ID" --limit 0
  [ "$status" -eq 0 ]

  if ! echo "$output" | grep "$TAG"; then
    ret_status=$?
    echo "Release not found in feed list"
    return $ret_status
  fi
}

@test "[$TESTSUITE] List 200 items in folder" {
  run ./occ news:item:list-folder "$user" --limit 200
  [ "$status" -eq 0 ]

  if ! echo "$output" | grep "$TAG"; then
    ret_status=$?
    echo "Release not found in folder list"
    return $ret_status
  fi
}

@test "[$TESTSUITE] List all items in folder" {
  run ./occ news:item:list-folder "$user" --limit 0
  [ "$status" -eq 0 ]

  if ! echo "$output" | grep "$TAG"; then
    ret_status=$?
    echo "Release not found in folder list"
    return $ret_status
  fi
}

@test "[$TESTSUITE] List 200 items" {
  run ./occ news:item:list "$user" --limit 200
  [ "$status" -eq 0 ]

  if ! echo "$output" | grep "$TAG"; then
    ret_status=$?
    echo "Release not found in list"
    return $ret_status
  fi
}

@test "[$TESTSUITE] List all items" {
  run ./occ news:item:list "$user" --limit 0
  [ "$status" -eq 0 ]

  if ! echo "$output" | grep "$TAG"; then
    ret_status=$?
    echo "Release not found in list"
    return $ret_status
  fi
}

@test "[$TESTSUITE] Test author fallback" {
  ./occ news:feed:add "$user" $HEISE_FEED --title "Something-${BATS_SUITE_TEST_NUMBER}"
  ID=$(./occ news:feed:list 'admin' | grep 'heise\.de' -1  | head -1 | grep -oE '[0-9]*')

  run ./occ news:item:list-feed "$user" "$ID" --limit 200
  [ "$status" -eq 0 ]

  if ! echo "$output" | grep '"author": "heise online",'; then
    ret_status=$?
    echo "Author fallback did not work"
    return $ret_status
  fi
}