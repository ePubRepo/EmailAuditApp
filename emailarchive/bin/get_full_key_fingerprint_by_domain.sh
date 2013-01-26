#!/bin/bash

###VALIDATE INPUTS
#Validate Domain
DOMAIN_TO_QUERY_FOR_KEY="$1"
if [ `expr length "$DOMAIN_TO_QUERY_FOR_KEY"` -lt "3" ]; then
        echo "ERROR -- INVALID INPUT"
        exit
fi

FILE_BEING_EXECUTED=`readlink -f "$0"`

`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Script called with input domain: $DOMAIN_TO_QUERY_FOR_KEY"`

FULL_KEY_FINGERPRINT_DUMP=`gpg --fingerprint --with-colon administrator@"$DOMAIN_TO_QUERY_FOR_KEY"`
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Raw fingerprint dump: $FULL_KEY_FINGERPRINT_DUMP"`

KEY_FINGERPRINT=`echo "$FULL_KEY_FINGERPRINT_DUMP" | grep -o "::[A-Z0-9]\{4,\}:" | grep -o "[A-Z0-9]\{4,\}"`
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Final Key Fingerprint: $KEY_FINGERPRINT"`

echo "$KEY_FINGERPRINT"
