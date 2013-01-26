#!/usr/bin/python
import os
import time
import subprocess

LOGS_DIRECTORY = "/home/appsappsinfo/emailarchive/logs/"
DEFAULT_CUTOFF_DAYS = 40
COMPRESSED_FILE_EXTENSIONS = 'gz'

MAXIMUM_LOGS_DIRECTORY_SIZE = 10737418240 #10GB folder quota
PATH_TO_EMAIL_ALERT_SCRIPT = "/home/appsappsinfo/emailarchive/bin/email_alert.py"

# get list of logs to delete based upon days expired
def getListOfAbsoluteLogPathsToDelete(daysOldToDelete):
   print "Checking back in logs directory for files back " + str(daysOldToDelete) + " days"
   toDeleteList = []
   listing = os.listdir(LOGS_DIRECTORY)
   for fileName in listing:      
     if toDelete(LOGS_DIRECTORY + fileName, daysOldToDelete):
         toDeleteList.append(LOGS_DIRECTORY + fileName)

   return toDeleteList

# determine whether to delete a file
def toDelete(absoluteFilePath, daysBack):
   modifiedTimestamp =  os.path.getctime(absoluteFilePath)
   cutOffTimestamp = time.time() - (60*60*24*daysBack)

   if absoluteFilePath.endswith("." + COMPRESSED_FILE_EXTENSIONS) == True and modifiedTimestamp < cutOffTimestamp:
      return True
   else:
      return False

# delete list of specified files
def deleteOldFiles(listOfAbsolutePaths):
   if len(listOfAbsolutePaths) > 0:
      for filePath in listOfAbsolutePaths:
         print "Deleting file: " + filePath
         os.remove(filePath)

# return the number of bytes in the mailbox download directory
def getNumBytesInLogsDirectory():
   total_size = 0
   for dirpath, dirnames, filenames in os.walk(LOGS_DIRECTORY):
      for f in filenames:
         fp = os.path.join(dirpath, f)
         total_size += os.path.getsize(fp)
   return total_size

def main():
   # delete log files older than the default expired date
   filesToDelete = getListOfAbsoluteLogPathsToDelete(DEFAULT_CUTOFF_DAYS)
   deleteOldFiles(filesToDelete)

   daysOldToPurge = DEFAULT_CUTOFF_DAYS - 1
   while getNumBytesInLogsDirectory() > MAXIMUM_LOGS_DIRECTORY_SIZE:
      if daysOldToPurge <= 3:
         print "warning -- coming up against max permitted space of all logs"
         callEmailCronAlert = subprocess.Popen([PATH_TO_EMAIL_ALERT_SCRIPT, '--description="Unable to delete enough files from mailbox archive folder at ' + LOGS_DIRECTORY + ' to get its size down under ' + str(MAXIMUM_LOGS_DIRECTORY_SIZE) + '. Current mailbox directory size: ' + str(getNumBytesInLogsDirectory()) + '"', '--file="' + os.path.abspath("delete_logs_to_cap_disk_space_used_from_logs.py") + '"'])
         rawOutput, errors = callEmailCronAlert.communicate()
         break

      filesToDelete = getListOfAbsoluteLogPathsToDelete(daysOldToPurge)
      deleteOldFiles(filesToDelete)
      daysOldToPurge -= 1

if __name__ == "__main__":
    main()
