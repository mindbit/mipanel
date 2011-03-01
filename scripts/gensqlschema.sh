#!/bin/bash

db="tmp_$(uuidgen -t | sed 's/-/_/g')"
basepath="$(dirname "$(readlink -f "$0")")"
user=postgres

psql -U "$user" -c "create database $db" || exit 1
psql -U "$user" -f "$basepath/../sql/changelog.sql" -q "$db"
pg_dump -U "$user" -s "$db" > "$basepath/../sql/schema.sql"
psql -U "$user" -c "drop database $db"
