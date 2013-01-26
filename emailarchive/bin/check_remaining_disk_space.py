#!/usr/bin/python
import subprocess
import os

#bash command to obtain the number corresponding to the percent of disk space used (e.g., 1 = 1%, 15 = 15%)
#  df -h | grep -o "/dev/sda1\(.\)\{1,\} /" | grep -o "[0-9]\{1,2\}%" | grep -o "[0-9]\{1,2\}"

MAX_DISK_PERCENT_ALLOWED_USED = 50
PATH_TO_EMAIL_ALERT_SCRIPT = "/home/appsappsinfo/emailarchive/bin/email_alert.py"

def getPercentUsedDiskSpace():
   step1 = subprocess.Popen(["df", "-h"], stdout = subprocess.PIPE)
   step2 = subprocess.Popen(["grep", "-o", "/dev\(.\)\{1,\} /"], stdin = step1.stdout, stdout = subprocess.PIPE)
   step3 = subprocess.Popen(["grep", "-o", "[0-9]\{1,2\}%"], stdin = step2.stdout, stdout = subprocess.PIPE)
   step4 = subprocess.Popen(["grep", "-o", "[0-9]\{1,2\}"], stdin = step3.stdout, stdout = subprocess.PIPE)
   rawOutput, errors = step4.communicate()

   percentUsedAsNumber = int(rawOutput)
   return percentUsedAsNumber
  
def main():
   percentUsedAsNumber = getPercentUsedDiskSpace()
   if percentUsedAsNumber >= MAX_DISK_PERCENT_ALLOWED_USED:
      callEmailCronAlert = subprocess.Popen([PATH_TO_EMAIL_ALERT_SCRIPT, '--description="Percent of disk space utilized exceeded permitted amount. Current percent used:' + str(percentUsedAsNumber) + '%"', '--file="' + os.path.abspath("check_remaining_disk_space.py") + '"'])
      rawOutput, errors = callEmailCronAlert.communicate()


if __name__ == "__main__":
    main()
