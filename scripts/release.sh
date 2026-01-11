#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <tag>" >&2
  exit 1
fi

tag="$1"
version="${tag#v}"

if [[ -z "$version" ]]; then
  echo "Invalid tag: '$tag'" >&2
  exit 1
fi

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
version_file="${root_dir}/VERSION"

printf '%s\n' "$version" > "$version_file"
echo "VERSION updated to ${version}"
