#!/usr/bin/env bash

mkdir -p app/Controllers
mkdir -p app/DataTables
mkdir -p app/Forms
mkdir -p app/Models
mkdir -p app/ObjectList
mkdir -p app/Objects
mkdir -p app/Services
mkdir -p cache
mkdir -p cache/cache
mkdir -p cache/metadata
mkdir -p cache/twig
mkdir -p storage
mkdir -p storage/keyvalue
mkdir -p storage/media
mkdir -p storage/media/default
mkdir -p public_html/media
mkdir -p public_html/media/files
mkdir -p public_html/media/thumbs
mkdir -p env

if [ -f env/config.ini ]; then
    exit
fi

echo "[application]
env = dev

[database]
username = root
password = [DB-PASS]
dbname = [DB-NAME]
host = mysql

[mailer]
host = mail
port = 1025" >> env/config.ini

rm createdirs.sh