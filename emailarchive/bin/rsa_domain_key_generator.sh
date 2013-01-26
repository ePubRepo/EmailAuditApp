#!/bin/bash

FILE_BEING_EXECUTED=`readlink -f "$0"`

SCRIPT_USER=`echo $USER`
SCRIPT_USER2=`echo "$(whoami)"`
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Key Generation run as -- $SCRIPT_USER -- also as -- $SCRIPT_USER2 "`

#VALIDATE INPUT DOMAIN
DOMAIN_TO_GENERATE_KEY="$1"
if [ `expr length "$DOMAIN_TO_GENERATE_KEY"` -lt "3" ]; then
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Invalid Domain Input: $DOMAIN_TO_GENERATE_KEY"`
        echo "ERROR -- INVALID INPUT"
        exit
fi

#ENSURE NO EXTANT KEYPAIR EXISTS
#THIS PREVENTS FILE-OVERWRITE, WHICH WOULD BE CATASTROPHIC
if [ -f "/home/appsappsinfo/emailarchive/datastore/gpg-keys/$DOMAIN_TO_GENERATE_KEY.pub" ]; then
`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Public key already exists: $DOMAIN_TO_GENERATE_KEY"`
   echo "ERROR -- DANGER -- PUBLIC KEY EXISTS ALREADY"
   exit
fi

`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Will begin key creation for domain: $DOMAIN_TO_GENERATE_KEY"`

#CREATE NEW KEY
#sudo apt-get install rng-tools http://serverfault.com/questions/214605/gpg-not-enough-entropy
#means to monitor and generate entropy http://www.question-defense.com/2010/03/03/not-enough-random-bytes-available-please-do-some-other-work-to-give-the-os-a-chance-to-collect-more-entropy-need-283-more-bytes
KEY_CREATE_TEXT="%echo Generating a basic OpenPGP key
Key-Type: RSA
Key-Length: 1024
Name-Real: BashScript-Generated
Name-Comment: with stupid passphrase
Name-Email: administrator@$DOMAIN_TO_GENERATE_KEY
Expire-Date: 0
Passphrase: abcabcabc
%pubring /home/appsappsinfo/emailarchive/datastore/gpg-keys/$DOMAIN_TO_GENERATE_KEY.pub
%secring /home/appsappsinfo/emailarchive/datastore/gpg-keys/$DOMAIN_TO_GENERATE_KEY.sec
# Do a commit here, so that we can later print done
%commit
%echo done
"

`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Raw text to create GPG Key: $KEY_CREATE_TEXT"`

KEY_CREATION_TEMP_FILE='/home/appsappsinfo/emailarchive/bin/keycreate.tmp'

echo "$KEY_CREATE_TEXT" > $KEY_CREATION_TEMP_FILE

#SCRIPT TO GENERATE ENTROPY
#cat /home/eric/Downloads/eclipse-javascript-indigo-linux-gtk-x86_64.tar.gz > /dev/null
#cat largefile.txt > /dev/null

#GENERATE GPG KEY
gpg --batch --gen-key $KEY_CREATION_TEMP_FILE

`/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Ran the following command: gpg --batch --gen-key $KEY_CREATION_TEMP_FILE"`

if [ -f "/home/appsappsinfo/emailarchive/datastore/gpg-keys/$DOMAIN_TO_GENERATE_KEY.pub" ]; then
   `/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Successfully Created GPG Key Pair for Domain: $DOMAIN_TO_GENERATE_KEY"`
   echo "SUCCESS"
else
   `/home/appsappsinfo/emailarchive/bin/append_to_info_log.sh "$FILE_BEING_EXECUTED" "Failed to Create GPG Key Pair for Domain: $DOMAIN_TO_GENERATE_KEY"`
   echo "ERROR -- FAILED TO CREATE FILE"
   exit
fi

rm $KEY_CREATION_TEMP_FILE
