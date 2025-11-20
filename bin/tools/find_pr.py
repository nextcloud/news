import re
import subprocess
import sys
from pathlib import Path

# Determine the project root directory (which is two levels up from this script's location)
# and construct the path to CHANGELOG.md from there.
PROJECT_ROOT = Path(__file__).parent.parent.parent.resolve()
CHANGELOG_FILE = PROJECT_ROOT / "CHANGELOG.md"
REPO = "nextcloud/news"  # Change if you use it in a different repository

def get_commit_for_line(filepath: Path, line_number: int) -> str | None:
    """Finds the commit hash for a specific line in a file using git blame."""
    try:
        result = subprocess.run(
            [
                "git",
                "blame",
                "-L",
                f"{line_number},{line_number}",
                "--porcelain",
                str(filepath),
            ],
            capture_output=True,
            text=True,
            check=True,
            encoding="utf-8",
        )
        first_line = result.stdout.splitlines()[0]
        return first_line.split(" ")[0]
    except (subprocess.CalledProcessError, FileNotFoundError, IndexError) as e:
        print(f"Error blaming line {line_number}: {e}", file=sys.stderr)
        return None

def get_pr_for_commit(commit_sha: str) -> str | None:
    """Finds the pull request number for a given commit using the GitHub CLI."""
    try:
        # This command finds the PR that a commit was part of.
        # It works reliably even for rebased and squashed commits.
        result = subprocess.run(
            ["gh", "pr", "list", "--search", commit_sha, "--state", "merged", "--json", "number", "--repo", REPO],
            capture_output=True,
            text=True,
            check=True,
            encoding="utf-8",
        )
        # The output is a JSON array, e.g., '[{"number":1234}]'
        import json
        prs = json.loads(result.stdout)
        if prs:
            return str(prs[0]["number"])
        else:
            print(f"Info: No PR found for commit {commit_sha[:10]} on GitHub.", file=sys.stderr)
            return None
    except (subprocess.CalledProcessError, FileNotFoundError, json.JSONDecodeError) as e:
        print(f"Error finding PR for commit {commit_sha}: {e}", file=sys.stderr)
        print("Please ensure the GitHub CLI ('gh') is installed and you are authenticated ('gh auth login').", file=sys.stderr)
        return None

def main():
    """Main function to update the changelog."""
    changelog_path = Path(CHANGELOG_FILE)
    print(f"Looking for {CHANGELOG_FILE}...")
    if not changelog_path.is_file():
        print(f"Error: {CHANGELOG_FILE} not found.", file=sys.stderr)
        sys.exit(1)

    print(f"Processing {CHANGELOG_FILE}...")
    lines = changelog_path.read_text(encoding="utf-8").splitlines()
    new_lines = lines[:]
    updated_count = 0

    in_unreleased_section = False
    # Regex to find if a line already has a PR ID
    has_pr_regex = re.compile(r"\s\(#\d+\)$")

    for i, line in enumerate(lines):
        if line.strip() == "# Unreleased":
            in_unreleased_section = True
            continue
        
        if in_unreleased_section and line.strip().startswith("# Releases"):
            in_unreleased_section = False
            break # Stop after the unreleased section

        stripped_line = line.strip()
        is_changelog_item = stripped_line.startswith('-')

        if in_unreleased_section and is_changelog_item and not has_pr_regex.search(stripped_line):
            line_number = i + 1
            print(f"\nProcessing line {line_number}: {stripped_line}")

            commit_sha = get_commit_for_line(changelog_path, line_number)
            if not commit_sha:
                continue

            print(f"  -> Found commit: {commit_sha}")
            pr_number = get_pr_for_commit(commit_sha)

            if pr_number:
                updated_line = f"{line.rstrip()} (#{pr_number})"
                new_lines[i] = updated_line
                updated_count += 1
                print(f"  => Found PR #{pr_number}. Updated line.")
            else:
                print("  => Could not determine PR. Line left unchanged.")


    if updated_count > 0:
        changelog_path.write_text("\n".join(new_lines) + "\n", encoding="utf-8")
        print(f"\nSuccessfully updated {updated_count} line(s) in {CHANGELOG_FILE}.")
    else:
        print("\nNo lines needed updating.")

if __name__ == "__main__":
    main()