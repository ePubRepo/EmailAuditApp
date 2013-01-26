#!/usr/bin/python
import os
import gzip

# this script is designed to compress (gzip up) log files that: (1) are not compressed already (2) are over a specified file size

MAXIMUM_PERMITTED_UNCOMPRESSED_FILESIZE = 262144000 #max filesize in bytes (currently 250MB)
LOGS_DIRECTORY = "/home/appsappsinfo/emailarchive/logs/"
UNCOMPRESSED_FILE_EXTENSIONS = 'txt'

# create a list containing the absolute path to all large uncompressed log files
def getLogFilesList():
   toCompressList = []
   listing = os.listdir(LOGS_DIRECTORY)
   for fileName in listing:
      if toCompress(LOGS_DIRECTORY + fileName):
         toCompressList.append(LOGS_DIRECTORY + fileName)
      
   return toCompressList

# determine whether to compress a file
def toCompress(absoluteFilePath):
   if absoluteFilePath.endswith("." + UNCOMPRESSED_FILE_EXTENSIONS) == True and os.path.getsize(absoluteFilePath) > MAXIMUM_PERMITTED_UNCOMPRESSED_FILESIZE:
      return True
   else:
      return False

# compress the log file specified by the absolute file path inputted
def compressLogFile(absoluteFilePath):
   oldPath = absoluteFilePath
   newPath = oldPath + '.gz'

   f_in = open(oldPath, 'rb')
   f_out = gzip.open(newPath, 'wb')
   f_out.writelines(f_in)
   f_out.close()
   f_in.close()

   os.remove(oldPath)

   print "Successfully compressed file " + oldPath + " as file " + newPath


def main():
   filesToCompress = getLogFilesList()
   for filePath in filesToCompress:
      compressLogFile(filePath)

if __name__ == "__main__":
    main()
