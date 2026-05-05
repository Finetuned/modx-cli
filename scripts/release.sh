#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 ]]; then
  echo "Usage: $0 <tag>" >&2
  exit 1
fi

tag="$1"
version="${tag#v}"

if ! git rev-parse --show-toplevel >/dev/null 2>&1; then
  echo "Release script must be run inside a git repository" >&2
  exit 1
fi

if [[ -z "$version" ]]; then
  echo "Invalid tag: '$tag'" >&2
  exit 1
fi

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
version_file="${root_dir}/VERSION"
changelog_file="${root_dir}/CHANGELOG.md"
expected_heading_regex="^## \\[${version//./\\.}\\] - [0-9]{4}-[0-9]{2}-[0-9]{2}$"

if git rev-parse -q --verify "refs/tags/${tag}" >/dev/null 2>&1; then
  echo "Tag already exists locally: ${tag}" >&2
  exit 1
fi

worktree_status="$(git -C "$root_dir" status --porcelain --untracked-files=all)"
if [[ -n "$worktree_status" ]]; then
  while IFS= read -r line; do
    path="${line:3}"
    if [[ "$path" != "CHANGELOG.md" && "$path" != "VERSION" ]]; then
      echo "Working tree has changes outside CHANGELOG.md and VERSION" >&2
      exit 1
    fi
  done <<< "$worktree_status"
fi

if [[ ! -f "$changelog_file" ]]; then
  echo "Missing changelog file: ${changelog_file}" >&2
  exit 1
fi

first_release_heading="$(awk '/^## \[.*\] - [0-9]{4}-[0-9]{2}-[0-9]{2}$/ { print; exit }' "$changelog_file")"
if [[ -z "$first_release_heading" || ! "$first_release_heading" =~ $expected_heading_regex ]]; then
  echo "CHANGELOG.md must start with a release heading for ${version} before running this script" >&2
  exit 1
fi

printf '%s\n' "$version" > "$version_file"

git -C "$root_dir" add CHANGELOG.md VERSION

if git -C "$root_dir" diff --cached --quiet; then
  echo "No release metadata changes to commit for ${tag}" >&2
  exit 1
fi

git -C "$root_dir" commit -m "Release ${tag}"
git -C "$root_dir" tag -a "$tag" -m "Release ${tag}"

echo "Prepared release ${tag}"
echo "Created commit: Release ${tag}"
echo "Created annotated tag: ${tag}"
