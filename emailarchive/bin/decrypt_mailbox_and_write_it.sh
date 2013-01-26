#!/bin/bash

FILE_BEING_EXECUTED=`readlink -f "$0"`

###VALIDATE INPUTS
#Validate Domain
DOMAIN_TO_QUERY_FOR_KEY="$1"
if [ `expr length "$DOMAIN_TO_QUERY_FOR_KEY"` -lt "3" ]; then
        `/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Script called with illegal input domain: $DOMAIN_TO_QUERY_FOR_KEY"`
        echo "ERROR -- INVALID INPUT"
        exit
fi

#Validate Decrypted Mailbox Location
ENCRYPTED_MAILBOX_LOCATION="$2"
if [ ! -f $ENCRYPTED_MAILBOX_LOCATION ];
then
    `/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Script called with illegal input encrypted mailbox: $ENCRYPTED_MAILBOX_LOCATION"`
    echo "ERRPR -- INVALID INPUT"
    exit
fi

if [ `expr length "$ENCRYPTED_MAILBOX_LOCATION"` -lt "10" ]; then
        echo "ERROR -- INVALID INPUT"
        exit
fi

#Validate Secret Key
SECRET_KEY_LOCATION="/home/appsappsinfo/emailarchive/datastore/gpg-keys/""$DOMAIN_TO_QUERY_FOR_KEY"".sec"
if [ ! -f $SECRET_KEY_LOCATION ];
then
    `/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Failed to find secret key at location: $SECRET_KEY_LOCATION"`
    echo "ERRPR -- SECRET KEY NOT FOUND"
    exit
fi


`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Going to attempt to decrypt with secret key location: $SECRET_KEY_LOCATION"`
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Script called with input domain: $DOMAIN_TO_QUERY_FOR_KEY"`
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Script called with input encrypted mailbox: $ENCRYPTED_MAILBOX_LOCATION"`

OUTPUT_OF_IMPORT=`gpg --allow-secret-key-import --import /home/appsappsinfo/emailarchive/datastore/gpg-keys/"$DOMAIN_TO_QUERY_FOR_KEY".sec`
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Raw return of private key import operation: $OUTPUT_OF_IMPORT"`

RAW_UNENCRYPTED_EXPORTED_MAILBOX=`gpg --batch --no-use-agent --passphrase "abcabcabc" --output "$ENCRYPTED_MAILBOX_LOCATION".unencrypted.txt --decrypt "$ENCRYPTED_MAILBOX_LOCATION"`
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Command Run to Decrypt: gpg --batch --no-use-agent --passphrase "abcabcabc" --output "$ENCRYPTED_MAILBOX_LOCATION".unencrypted --decrypt $ENCRYPTED_MAILBOX_LOCATION"`

if [ ! -f "$ENCRYPTED_MAILBOX_LOCATION".unencrypted.txt ]; then
	echo "ERROR -- NO DECRYPTED FILE AFTER DECRYPTION ATTEMPT"
	exit
fi

KEY_FINGERPRINT=`/home/appsappsinfo/emailarchive/bin/get_full_key_fingerprint_by_domain.sh "$DOMAIN_TO_QUERY_FOR_KEY"`
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Key Fingerprint for Deletion from Keyring: $KEY_FINGERPRINT"`

`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Command to be run to delete private key: gpg --batch --delete-keys --yes "$KEY_FINGERPRINT""`

`gpg --batch --delete-secret-keys --yes "$KEY_FINGERPRINT"`
`gpg --batch --delete-keys --yes "$KEY_FINGERPRINT"`

echo "SUCCESS"
