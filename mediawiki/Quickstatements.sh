#!/bin/bash
set -ex

php /var/www/html/extensions/OAuth/maintenance/createOAuthConsumer.php \
    --approve \
    --callbackUrl  "$QS_PUBLIC_SCHEME_HOST_AND_PORT/api.php" \
    --callbackIsPrefix true --user "$MW_ADMIN_NAME" --name QuickStatements --description QuickStatements --version `date '+%Y%m%d%H%M%S' \
    --grants createeditmovepage --grants editpage --grants highvolume --jsonOnSuccess > /quickstatements/data/qs-oauth.json \
    --conf /var/www/html/LocalSettings.php

OAUTH_CONSUMER_KEY=$(grep -o '"key":"[^"]*' /quickstatements/data/qs-oauth.json | grep -o '[^"]*$')
OAUTH_CONSUMER_SECRET=$(grep -o '"secret":"[^"]*' /quickstatements/data/qs-oauth.json | grep -o '[^"]*$')

export OAUTH_CONSUMER_KEY
export OAUTH_CONSUMER_SECRET

envsubst < /templates/oauth.ini > /quickstatements/data/oauth.ini

