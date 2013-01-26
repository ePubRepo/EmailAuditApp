#!/usr/bin/python
import os
import time
import subprocess

MAILBOX_DIRECTORY = "/home/appsappsinfo/emailarchive/datastore/mailboxes/"
DEFAULT_CUTOFF_DAYS = 40

MAXIMUM_MAILBOX_DIRECTORY_SIZE = 322122547200 #300GB folder quota
PATH_TO_EMAIL_ALERT_SCRIPT = "/home/appsappsinfo/emailarchive/bin/email_alert.py"

# get list of mailboxes to delete based upon days expired
def getListOfAbsoluteMailboxPathsToDelete(daysOldToDelete):
   toDeleteList = []
   listing = os.listdir(MAILBOX_DIRECTORY)
   for fileName in listing:      
     if toDelete(MAILBOX_DIRECTORY + fileName, daysOldToDelete):
         toDeleteList.append(MAILBOX_DIRECTORY + fileName)

   return toDeleteList

# determine whether to delete a file
def toDelete(absoluteFilePath, daysBack):
   modifiedTimestamp =  os.path.getctime(absoluteFilePath)
   cutOffTimestamp = time.time() - (60*60*24*daysBack)

   if modifiedTimestamp < cutOffTimestamp:
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
def getNumBytesInMailboxDirectory():
   total_size = 0
   for dirpath, dirnames, filenames in os.walk(MAILBOX_DIRECTORY):
      for f in filenames:
         fp = os.path.join(dirpath, f)
         total_size += os.path.getsize(fp)
   return total_size

def main():
   # delete mailbox files older than the default expired date
   filesToDelete = getListOfAbsoluteMailboxPathsToDelete(DEFAULT_CUTOFF_DAYS)
   deleteOldFiles(filesToDelete)

   daysOldToPurge = DEFAULT_CUTOFF_DAYS - 1
   while getNumBytesInMailboxDirectory() > MAXIMUM_MAILBOX_DIRECTORY_SIZE:
      if daysOldToPurge <= 3:
         print "warning -- coming up against max permitted space of all mailboxed"
         callEmailCronAlert = subprocess.Popen([PATH_TO_EMAIL_ALERT_SCRIPT , '--description="Unable to delete enough files from mailbox archive folder at ' + MAILBOX_DIRECTORY + ' to get its size down under ' + str(MAXIMUM_MAILBOX_DIRECTORY_SIZE) + '. Current mailbox directory size: ' + str(getNumBytesInMailboxDirectory()) + '"', '--file="' + os.path.abspath("delete_mailboxes_to_cap_disk_space_used_from_mailboxes.py") + '"'])
         rawOutput, errors = callEmailCronAlert.communicate()
         break

      filesToDelete = getListOfAbsoluteMailboxPathsToDelete(daysOldToPurge)
      deleteOldFiles(filesToDelete)
      daysOldToPurge -= 1

if __name__ == "__main__":
    main()
