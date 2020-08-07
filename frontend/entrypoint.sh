#!/bin/bash

set -e # exit when subcommands fail

# Install if first time running
if [[ ! -d node_modules ]]; then
  npm ci
fi

# Run local server
npm run dev
