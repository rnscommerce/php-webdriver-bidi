#!/bin/sh
# Copies Selenium's WebDriver BiDi schema at a given commit into schema/.
# Usage: tools/update-schema.sh [selenium-commit]
set -eu
cd "$(dirname "$0")/.."
commit="${1:-$(sed -n 's/^commit: //p' schema/SOURCE)}"
curl -fsSL "https://raw.githubusercontent.com/SeleniumHQ/selenium/${commit}/common/bidi/schema.json" -o schema/schema.json
sha256=$(sha256sum schema/schema.json | cut -d' ' -f1)
cat > schema/SOURCE <<SOURCE
repository: https://github.com/SeleniumHQ/selenium
path: common/bidi/schema.json
commit: ${commit}
sha256: ${sha256}
SOURCE
echo "schema/schema.json at ${commit} (${sha256})"
