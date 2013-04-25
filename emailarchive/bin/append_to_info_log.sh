#!/bin/bash
# Copyright 2011. Eric Beach. All Rights Reserved.

#VALIDATE INPUT TEXT
EXECUTING_SCRIPT_FILE="$1"
# the proper way to get this is: `readlink -f "$0"` 
if [ `expr length "$EXECUTING_SCRIPT_FILE"` -lt "3" ]; then
        echo "ERROR -- INVALID INPUT"
        exit
fi

TO_LOG="$2"
if [ `expr length "$TO_LOG"` -lt "3" ]; then
        echo "ERROR -- INVALID INPUT"
        exit
fi

#2011-10-08 19:03:28 *** administrator@apps-email.info *** /home/appsappsinfo/common/classes/model/HTTPResponse.php35 HTTP Response Status Code: 200

echo `date +"%Y-%m-%d %k:%M:%S"` "*** Bash Script *** "$EXECUTING_SCRIPT_FILE " *** "  "$TO_LOG" >> /home/appsappsinfo/emailarchive/logs/info_log.txt
