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

OUTPUT_OF_IMPORT=`gpg --import /home/appsappsinfo/emailarchive/datastore/gpg-keys/"$DOMAIN_TO_QUERY_FOR_KEY".pub`
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Raw return of public key import operation: $OUTPUT_OF_IMPORT"`

RAW_EXPORTED_PUBLIC_KEY=`gpg --armor --export administrator@"$DOMAIN_TO_QUERY_FOR_KEY"`
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Raw exported public key: $RAW_EXPORTED_PUBLIC_KEY"`

BASE64_EXPORTED_PUBLIC_KEY=`echo "$RAW_EXPORTED_PUBLIC_KEY" | base64`
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Base64 exported public key: $BASE64_EXPORTED_PUBLIC_KEY"`

echo "$BASE64_EXPORTED_PUBLIC_KEY" > /home/appsappsinfo/emailarchive/datastore/gpg-keys/"$DOMAIN_TO_QUERY_FOR_KEY".pub.base64.txt

KEY_FINGERPRINT=`/home/appsappsinfo/emailarchive/bin/get_full_key_fingerprint_by_domain.sh "$DOMAIN_TO_QUERY_FOR_KEY"`
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Key Fingerprint for Deletion from Keyring: $KEY_FINGERPRINT"`

gpg --batch --delete-keys --yes "$KEY_FINGERPRINT"

echo "SUCCESS"
