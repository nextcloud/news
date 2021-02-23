#!/usr/bin/env bats

load "helpers/settings"

TESTSUITE="Folders"

teardown() {
  ID=$(./occ news:folder:list 'admin' | grep "Something-${BATS_SUITE_TEST_NUMBER}" -1  | head -1 | grep -oE '[0-9]*')
  if [ -n "$ID" ]; then
    ./occ news:folder:delete "$user" "$ID"
  fi
}

@test "[$TESTSUITE] Create new" {
  run ./occ news:folder:add "$user" "Something-${BATS_SUITE_TEST_NUMBER}"
  [ "$status" -eq 0 ]


  if echo "$output" | grep 'new folder'; then
    ret_status=$?
    echo "Folder ID not returned"
    return $ret_status
  fi
}

@test "[$TESTSUITE] List all" {
  ./occ news:folder:add "$user" "Something-${BATS_SUITE_TEST_NUMBER}"

  run ./occ news:folder:list "$user"
  [ "$status" -eq 0 ]

  if echo "$output" | grep "Something-${BATS_SUITE_TEST_NUMBER}"; then
    ret_status=$?
    echo "Folder not found in list"
    return $ret_status
  fi
}

@test "[$TESTSUITE] Delete all" {
  ID=$(./occ news:folder:add "$user" "Something-${BATS_SUITE_TEST_NUMBER}" | grep -oE '[0-9]*')

  run ./occ news:folder:list "$user"
  [ "$status" -eq 0 ]

  echo "$output" | grep "Something-${BATS_SUITE_TEST_NUMBER}"

  run ./occ news:folder:delete "$user" "$ID"
  [ "$status" -eq 0 ]
}