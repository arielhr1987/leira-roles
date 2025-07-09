#!/bin/bash

# This script updates the johnpbloch/wordpress package in composer.json in order to match the WordPress version used in wp-env.

set -e

# Get version from wp-env (inside the container)
WP_VERSION=$(wp-env run cli wp core version | tail -n 1)

echo "🌐 Detected WordPress version from wp-env: $WP_VERSION"

# Check if composer.json exists
if [ ! -f composer.json ]; then
	echo "❌ composer.json not found"
	exit 1
fi

# Require the matching version
echo "📦 Updating johnpbloch/wordpress to version: $WP_VERSION"
composer require --dev "johnpbloch/wordpress:$WP_VERSION"
composer update
