#!/usr/bin/python
import subprocess
import os

# raw bash command to grep through an return the number of seconds each key generation took
# grep -o "GPG keypair for [A-Za-z0-9\.\-]\{1,20\} took \([0-9]\)\{1,10\} seconds" /home/appsappsinfo/emailarchive/logs/info_log.txt | grep -o " \([0-9]\)\{1,10\} seconds" | grep -o "[0-9]\{1,10\}"

PATH_TO_EMAIL_ALERT_SCRIPT = "/home/appsappsinfo/emailarchive/bin/email_alert.py"

# get a list containing the number of seconds that each key creation in the logs took
def getSeconds():
   result = subprocess.Popen(["grep", "GPG keypair for [A-Za-z0-9\.\-]\{1,20\} took \([0-9]\)\{1,10\} seconds", "/home/appsappsinfo/emailarchive/logs/info_log.txt"], stdout = subprocess.PIPE)
   result2 = subprocess.Popen(["grep", "-o", " \([0-9]\)\{1,10\} seconds"], stdin = result.stdout, stdout = subprocess.PIPE)
   result3 = subprocess.Popen(["grep", "-o", "[0-9]\{1,10\}"], stdin = result2.stdout, stdout=subprocess.PIPE,stderr=subprocess.PIPE)
   rawStrOutput, errors = result3.communicate()
   
   result.stdout.close()
   result.terminate()
   result2.stdout.close()
   result2.terminate()
   result3.stdout.close()
   
   rawListOutput = rawStrOutput.split("\n");
   rawListOutput.pop() #pop off last element which is blank whitespace
   
   refinedListOutput = []
   for strInt in rawListOutput:
      refinedListOutput.append(int(strInt)) # convert string value of integer (e.g., "1") to true integer
 
   return refinedListOutput

# based upon the list of key creation times, compute the average key creation time
def getAverageKeyCreationTime(secondsList):
   return sum(secondsList, 0.0) / len(secondsList)

# get the maximum key creation time from the list of all key creation times
def getMaximumKeyCreationTime(secondsList):
   return max(secondsList)

def main():
   secondsKeyCreatedList = getSeconds()
   avgTime = getAverageKeyCreationTime(secondsKeyCreatedList)
   if avgTime > 3:
      callEmailCronAlert = subprocess.Popen([PATH_TO_EMAIL_ALERT_SCRIPT, '--description="Average GPG key creation time exceeded the permitted threashold for average key creation. Average key creation time:' + str(avgTime) + '"', '--file="' + os.path.abspath("gpg_key_creation_time_checker.py") + '"'])
      rawOutput, errors = callEmailCronAlert.communicate()

   maxTime = getMaximumKeyCreationTime(secondsKeyCreatedList)
   if maxTime > 10:
      callEmailCronAlert = subprocess.Popen([PATH_TO_EMAIL_ALERT_SCRIPT, '--description="A single GPG key creation time exceeded the permitted threashold for a single key creation. Maximum key creation time:' + str(maxTime) + '"', '--file="' + os.path.abspath("gpg_key_creation_time_checker.py") + '"'])
      rawOutput, errors = callEmailCronAlert.communicate()

if __name__ == '__main__':
   main()
