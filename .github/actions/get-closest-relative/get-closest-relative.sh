#!/bin/bash
#
# This script identifies the closest relative branch in the Git tree
# by calculating the path length through the common merge-base.
#
# Usage:
#   ./get-closest-relative [--commit=VALUE] [--scope=VALUE]
#
# Arguments:
#   --commit: The commit to compare against (default: HEAD).
#   --scope: Which branches to search: local, remote, or both (default: both).
#
# Outputs:
#   Prints progress to stderr.
#   Prints the name of the closest branch to stdout as the final line.

set -o errexit
set -o nounset
set -o pipefail

# -----------------------------------------------------------------------------
# Identify the closest relative branch to a given commit.
# Arguments:
#   $@: Command line flags (--commit, --scope).
# Outputs:
#   Writes the closest branch name to stdout.
# -----------------------------------------------------------------------------
main() {
  local source_commit="HEAD"
  local include_scope="both"

  # ----------------------------------------
  # ARGUMENT PARSING
  # ----------------------------------------

  while [[ $# -gt 0 ]]; do
    case "$1" in
      -c=*|--commit=*)
        source_commit="${1#*=}"
        shift
        ;;
      -s=*|--scope=*)
        include_scope="${1#*=}"
        shift
        ;;
      -h|--help)
        echo "Usage: $0 [--commit=VALUE] [--scope=VALUE]" >&2
        exit 0
        ;;
      *)
        echo "Error: unknown argument $1" >&2
        exit 1
        ;;
    esac
  done

  readonly source_commit
  readonly include_scope

  # ----------------------------------------
  # VALIDATION
  # ----------------------------------------

  # Validate the source commit exists.
  if ! git rev-parse --verify "${source_commit}" >/dev/null 2>&1; then
    echo "Error: invalid source commit: ${source_commit}" >&2
    exit 1
  fi

  echo "Selected source commit: ${source_commit}" >&2
  echo "Search scope:           ${include_scope}" >&2

  # Determine the Git ref-paths based on the requested scope.
  local ref_scopes=()
  case "${include_scope}" in
    "local")
      ref_scopes=("refs/heads/")
      ;;
    "remote")
      ref_scopes=("refs/remotes/origin/")
      ;;
    "both")
      ref_scopes=("refs/heads/" "refs/remotes/origin/")
      ;;
    *)
      echo "Error: invalid include_scope: ${include_scope}. Expected: local, remote, both." >&2
      exit 1
      ;;
  esac

  # ----------------------------------------
  # COLLECT CANDIDATE REFS
  # ----------------------------------------

  # Resolve the short branch name of our source to exclude it from our candidates in local and remote scopes.
  local branch_name_to_exclude
  branch_name_to_exclude=$(git rev-parse --abbrev-ref "${source_commit}" 2>/dev/null | sed 's|^origin/||' || echo "")

  echo "Selected branch: ${branch_name_to_exclude}" >&2

  # Resolve the full reference name of our self-commit so we can exclude it.
  local -r self_ref_full=$(git rev-parse --symbolic-full-name "${source_commit}" 2>/dev/null || echo "")

  echo "Full reference name: ${self_ref_full}" >&2
  echo "" >&2

  # Collect candidate references (full paths like refs/heads/main).
  # We exclude any ref that matches our current branch name (local or remote)
  # and noisy HEAD pointers.
  local -r candidate_refs=$(git for-each-ref \
    --format='%(refname)' \
    "${ref_scopes[@]}" \
    | grep -v "/${branch_name_to_exclude}$" \
    | grep -v "^${branch_name_to_exclude}$" \
    | grep -v "/HEAD$")

  if [[ -z "${candidate_refs}" ]]; then
    echo "Error: no candidate branches found in the specified scopes: ${include_scope}" >&2
    exit 1
  fi

  # ----------------------------------------
  # DISTANCE CALCULATION
  # ----------------------------------------

  local min_distance_found=999999999
  local best_branch_ref=""

  echo "Calculating distances to $(echo "${candidate_refs}" | wc -w) branches..." >&2

  local target_ref
  for target_ref in ${candidate_refs}; do
    local common_ancestor_commit
    common_ancestor_commit=$(git merge-base "${source_commit}" "${target_ref}" 2>/dev/null || true)

    # Log diagnostic information to stderr.
    local display_ref="${target_ref}"
    if [[ ${#display_ref} -gt 40 ]]; then
      display_ref="${display_ref:0:37}..."
    fi

    if [[ -z "${common_ancestor_commit}" ]]; then
      printf " -> %-40s %8s (A:%s B:%s)\n" \
        "${display_ref}" "inf" "?" "?" >&2
      continue
    fi

    local distance_to_source
    local distance_to_candidate
    distance_to_source=$(git rev-list --count "${common_ancestor_commit}..${source_commit}")
    distance_to_candidate=$(git rev-list --count "${common_ancestor_commit}..${target_ref}")

    local total_path_distance=$((distance_to_source + distance_to_candidate))

    printf " -> %-40s %8d (A:%d B:%d)\n" \
      "${display_ref}" "${total_path_distance}" "${distance_to_source}" "${distance_to_candidate}" >&2

    # Update the winner if a closer relative is discovered.
    if [[ "${total_path_distance}" -lt "${min_distance_found}" ]]; then
      min_distance_found="${total_path_distance}"
      best_branch_ref="${target_ref}"
    fi
  done

  # Handle the case where no relatives were found.
  if [[ -z "${best_branch_ref}" ]]; then
    echo "Error: could not identify a closest relative branch." >&2
    exit 1
  fi

  # ----------------------------------------
  # OUTPUT RESULTS
  # ----------------------------------------

  # Clean up the output name for a slug-friendly short name (e.g., refs/remotes/origin/main -> main).
  local best_branch_short
  best_branch_short=$(echo "${best_branch_ref}" | sed -E 's!^refs/(heads|remotes/origin)/!!')

  # Log final summary to stderr.
  echo >&2
  echo "Summary:" >&2
  echo "  Closest relative ref:    ${best_branch_ref}" >&2
  echo "  Short branch name:       ${best_branch_short}" >&2
  echo "  Total graph distance:    ${min_distance_found}" >&2
  echo >&2

  # Export to CI environment if applicable (handles GitHub, Gitea, Forgejo, etc.)
  local output_file
  for output_file in "${GITHUB_OUTPUT:-}" "${GITEA_OUTPUT:-}" "${FORGEJO_OUTPUT:-}"; do
    if [[ -n "${output_file}" ]]; then
      echo "branch=${best_branch_ref}" >> "${output_file}"
      echo "short_name=${best_branch_short}" >> "${output_file}"
    fi
  done

  # Final result to stdout.
  echo "${best_branch_ref}"
}

# Run the script.
main "$@"