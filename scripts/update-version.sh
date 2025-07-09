#!/bin/bash

# This script updates the version in readme.txt and the main plugin file for a WordPress plugin.
# After running this script, the plugin is ready to create a new release.
# To do so, create a new tag in Git with the same version number.

set -e

PLUGIN_FILE="leira-roles.php"
README_FILE="readme.txt"
PACKAGE_JSON="package.json"

# Extract current version from readme.txt
CURRENT_VERSION=$(grep -E "^Stable tag:" "$README_FILE" | sed -E "s/^Stable tag:[[:space:]]*//" | tr -d '[:space:]')

if [[ -z "$CURRENT_VERSION" ]]; then
    echo "❌ Could not detect current version from $README_FILE"
    exit 1
fi

# Prompt for new version with default
read -p "Enter new version (current: $CURRENT_VERSION): " NEW_VERSION
NEW_VERSION=${NEW_VERSION:-$CURRENT_VERSION}

# Validate version format (basic SemVer)
if [[ ! $NEW_VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "❌ Invalid version format. Use MAJOR.MINOR.PATCH (e.g. 1.2.3)"
    exit 1
fi

echo "🔄 Updating version to $NEW_VERSION..."

# Update Stable tag in readme.txt (preserve whitespace)
sed -E -i '' "s/^(Stable tag:)[[:space:]]*[0-9]+\.[0-9]+\.[0-9]+/\1 $NEW_VERSION/" "$README_FILE"
if grep -qE "Stable tag:[[:space:]]*$NEW_VERSION" "$README_FILE"; then
	echo "Updated Stable tag in $README_FILE"
else
	echo "❌ Failed to update Stable tag in $README_FILE"
	exit 1
fi

# Update Version: line in plugin header (preserve indentation)
sed -E -i '' "s/^([[:space:]]*\*[[:space:]]+Version:[[:space:]]+)[0-9]+\.[0-9]+\.[0-9]+/\1$NEW_VERSION/" "$PLUGIN_FILE"
if grep -qE "Version:[[:space:]]*$NEW_VERSION" "$PLUGIN_FILE"; then
	echo "Updated Version header in $PLUGIN_FILE"
else
	echo "❌ Failed to update Version header in $PLUGIN_FILE"
	exit 1
fi

# Update define('LEIRA_ROLES_VERSION', ...) (preserve spacing)
sed -E -i '' "s/(define\([[:space:]]*'LEIRA_ROLES_VERSION'[[:space:]]*,[[:space:]]*')[^']+(')/\1$NEW_VERSION\2/" "$PLUGIN_FILE"
if grep -q "define([[:space:]]*'LEIRA_ROLES_VERSION'[[:space:]]*,[[:space:]]*'$NEW_VERSION'" "$PLUGIN_FILE"; then
	echo "Updated LEIRA_ROLES_VERSION in $PLUGIN_FILE"
else
	echo "❌ Failed to update LEIRA_ROLES_VERSION in $PLUGIN_FILE"
	exit 1
fi

# Update version in package.json
# This uses jq if available, else falls back to sed
if command -v jq >/dev/null 2>&1; then
  jq --arg ver "$NEW_VERSION" '.version = $ver' "$PACKAGE_JSON" > tmp.$$.json && mv tmp.$$.json "$PACKAGE_JSON"
else
  sed -E -i '' "s/(\"version\"\[[:space:]]*:\[[:space:]]*\")[0-9]+\.[0-9]+\.[0-9]+(\"\,?)/\1$NEW_VERSION\2/" "$PACKAGE_JSON"
fi

echo "✅ Version updated to $NEW_VERSION"


