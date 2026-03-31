#!/usr/bin/env bash
set -euo pipefail

log() { printf '[LOG] %s :: %s\n' "$(date -Iseconds)" "$*"; }
die() { printf '[ERR] %s :: %s\n' "$(date -Iseconds)" "$*" >&2; exit 1; }

need_cmd() { command -v "$1" >/dev/null 2>&1 || die "Missing required command: $1"; }

# ---------- Config (env overrides) ----------
: "${AWS_CLI_VERSION:?Set AWS_CLI_VERSION to an exact pinned version, e.g. 2.13.24}"
: "${AWS_CLI_SHA256:?Set AWS_CLI_SHA256 to the expected SHA-256 of the ZIP}"

BINDIR="${BINDIR:-/usr/local/bin}"
INSTALL_DIR="${INSTALL_DIR:-/usr/local/aws-cli}"
# Makes a unique temp workdir for installation
WORKDIR="${WORKDIR:-$(mktemp -d -t awscli.XXXXXX)}"
CHANGELOG_URL="${CHANGELOG_URL:-https://raw.githubusercontent.com/aws/aws-cli/v2/CHANGELOG.rst}"

cleanup() { rm -rf "$WORKDIR"; }
trap cleanup EXIT

# Normalize "v2.13.24" -> "2.13.24"
AWS_CLI_VERSION="${AWS_CLI_VERSION#v}"
AWS_CLI_VERSION="${AWS_CLI_VERSION#V}"

# Enforce pinned version format
[[ "$AWS_CLI_VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]] \
  || die "AWS_CLI_VERSION must be an exact pinned version like 2.13.24 (got: $AWS_CLI_VERSION)"

choose_arch() {
  case "$(uname -m)" in
    x86_64|amd64)  echo "x86_64" ;;
    aarch64|arm64) echo "aarch64" ;;
    *) die "Unsupported architecture from uname -m: $(uname -m)" ;;
  esac
}

pick_downloader() {
  if command -v curl >/dev/null 2>&1; then echo "curl"
  elif command -v wget >/dev/null 2>&1; then echo "wget"
  else die "Need curl or wget"
  fi
}

download_to() {
  local url="$1" out="$2" dl="$3"
  if [[ "$dl" == "curl" ]]; then
    curl -fsSL "$url" -o "$out"
  else
    wget -qO "$out" "$url"
  fi
}

sha256_file() {
  local file="$1"
  if command -v sha256sum >/dev/null 2>&1; then
    sha256sum "$file" | awk '{print $1}'
  elif command -v shasum >/dev/null 2>&1; then
    shasum -a 256 "$file" | awk '{print $1}'
  elif command -v openssl >/dev/null 2>&1; then
    openssl dgst -sha256 "$file" | awk '{print $2}'
  else
    die "Need sha256sum, shasum, or openssl for SHA-256 verification"
  fi
}

installed_version() {
  if command -v aws >/dev/null 2>&1; then
    # aws --version outputs like: aws-cli/2.x.y ...
    aws --version 2>&1 | awk -F'[ /]' '{print $2}' | head -n1
  else
    echo ""
  fi
}

latest_version_from_changelog() {
  local dl="$1"
  local tmp="$WORKDIR/CHANGELOG.rst"
  download_to "$CHANGELOG_URL" "$tmp" "$dl"
  # First line that is exactly X.Y.Z
  grep -m1 -E '^[0-9]+\.[0-9]+\.[0-9]+$' "$tmp" || true
}

main() {
  need_cmd unzip
  local arch dl zip url actual_sha inst latest install_args=()

  dl="$(pick_downloader)"
  arch="$(choose_arch)"

  inst_ver="$(installed_version)"
  latest="$(latest_version_from_changelog "$dl")"

  if [[ -n "$inst_ver" ]]; then
    log "Currently installed AWS CLI: $inst_ver"
  else
    log "Currently installed AWS CLI: (not found)"
  fi

  if [[ -n "$latest" ]]; then
    log "Latest available AWS CLI (from CHANGELOG): $latest"
  else
    log "Latest available AWS CLI (from CHANGELOG): (could not determine)"
  fi

  # Optional informational hint (no failure)
  if [[ -n "$inst_ver" && -n "$latest" ]]; then
    if [[ "$inst_ver" != "$latest" ]]; then
      log "Note: installed ($inst_ver) is older than latest ($latest)"
    fi
  fi

  # Versioned AWS CLI v2 ZIP URL pattern (pin to exact version)
  # Example of versioned URL format: awscli-exe-linux-x86_64-2.13.24.zip
  zip="awscliv2-${arch}-${AWS_CLI_VERSION}.zip"
  url="https://awscli.amazonaws.com/awscli-exe-linux-${arch}-${AWS_CLI_VERSION}.zip"

  log "Pinned target version: $AWS_CLI_VERSION"
  log "Download URL: $url"
  log "Workdir: $WORKDIR"

  cd "$WORKDIR"

  log "Downloading ZIP..."
  download_to "$url" "$zip" "$dl"

  log "Verifying SHA-256..."
  actual_sha="$(sha256_file "$zip")"
  if [[ "${actual_sha,,}" != "${AWS_CLI_SHA256,,}" ]]; then
    die "SHA-256 mismatch!
Expected: $AWS_CLI_SHA256
Actual:   $actual_sha
Refusing to install."
  fi
  log "SHA-256 OK"

  log "Unzipping..."
  unzip -q "$zip"

  mkdir -p "$BINDIR" "$INSTALL_DIR"
  # Installer supports --bin-dir/--install-dir and --update in common usage patterns
  if [[ -e "$BINDIR/aws" || -d "$INSTALL_DIR" ]]; then
    install_args+=(--update)
  fi

  log "Running installer..."
  ./aws/install --bin-dir "$BINDIR" --install-dir "$INSTALL_DIR" "${install_args[@]}"

  log "Verifying installed version..."
  local vout
  vout="$("$BINDIR/aws" --version 2>&1 || true)"
  echo "$vout"
  [[ "$vout" == aws-cli/"$AWS_CLI_VERSION"* ]] || die "Installed version does not match pinned version. Got: $vout"

  log "Done"
}

main "$@"
