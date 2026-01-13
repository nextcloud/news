import argparse
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
        result = subprocess.run(
            [
                "gh",
                "pr",
                "list",
                "--search",
                commit_sha,
                "--state",
                "merged",
                "--json",
                "number",
                "--repo",
                REPO,
            ],
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
        print(
            "Please ensure the GitHub CLI ('gh') is installed and you are authenticated ('gh auth login').",
            file=sys.stderr,
        )
        return None


def main():
    """Main function to update the changelog.

    By default this script processes the `# Unreleased` section. Optionally pass a
    release short name like `28.0.0-beta.2` and it will process the corresponding
    `## [<name>]` section instead.
    """
    parser = argparse.ArgumentParser(description="Annotate changelog lines with PR numbers")
    parser.add_argument(
        "section",
        nargs="?",
        help="Optional release short name to process (e.g. 28.0.0-beta.2). If omitted, the 'Unreleased' section is processed.",
    )
    args = parser.parse_args()

    changelog_path = Path(CHANGELOG_FILE)
    print(f"Looking for {CHANGELOG_FILE}...")
    if not changelog_path.is_file():
        print(f"Error: {CHANGELOG_FILE} not found.", file=sys.stderr)
        sys.exit(1)

    print(f"Processing {CHANGELOG_FILE}...")
    lines = changelog_path.read_text(encoding="utf-8").splitlines()
    new_lines = lines[:]
    updated_count = 0

    # Regex to find if a line already has a PR ID
    has_pr_regex = re.compile(r"\s\(#\d+\)$")

    in_section = False
    start_index = None

    if args.section:
        target = args.section
        # find the header line that contains the target inside a '## [' header
        for idx, line in enumerate(lines):
            if line.strip().startswith("## [") and target in line:
                in_section = True
                start_index = idx
                break
        if not in_section:
            print(f"Error: Could not find a release section containing '{target}'.", file=sys.stderr)
            sys.exit(1)
    else:
        # default: Unreleased
        for idx, line in enumerate(lines):
            if line.strip() == "# Unreleased":
                in_section = True
                start_index = idx
                break
        if not in_section:
            print("Error: 'Unreleased' section not found.", file=sys.stderr)
            sys.exit(1)

    # Process lines starting after start_index until the next top-level '## ' header (for releases)
    has_pr_regex = re.compile(r"\s\(#\d+\)$")

    for i in range(start_index + 1, len(lines)):
        line = lines[i]
        stripped = line.strip()
        # stop at the next release header (top-level '## ')
        if stripped.startswith("## ") and i > start_index + 0:
            break

        is_changelog_item = stripped.startswith("-")
        if is_changelog_item and not has_pr_regex.search(stripped):
            line_number = i + 1
            print(f"\nProcessing line {line_number}: {stripped}")

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