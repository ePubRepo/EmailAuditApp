#!/usr/bin/python
import sys
import re
import subprocess
import os
import httplib
import datetime
from urlparse import urlparse


# main purpose of this script is to check if a URL is responding and if not, email an alert to myself

inputArguments = {}
URL_KEY_NAME = 'url'
TIMEOUT_SECONDS = 30
PATH_TO_EMAIL_ALERT_SCRIPT = "/home/appsappsinfo/emailarchive/bin/email_alert.py"

# read in the flags that were passed to this script and take ones in the format --flagVariableName="Value here" into a dictionary
def buildInputVariableDictionary():
   for paramString in sys.argv:
      m = re.search(r'--(\w+)=(.+)', paramString)
      if m:
         inputArguments[m.group(1)] = m.group(2)


def checkValidUrlInputted():
   try:
      inputArguments[URL_KEY_NAME]
   except NameError:
      inputArguments[URL_KEY_NAME] = None

   # Test whether variable is defined to be None
   if inputArguments[URL_KEY_NAME] is None:
      return False
      
   return True


def sendEmailAlert(message, conn, response=None):
   if response is None:
      finalMessage = message
   else:
      responseHeaders = response.getheaders()
      status = response.status
      reason = response.reason
      version = response.version

      finalMessage = message  + " /// " + "Status: " + str(status) + " /// " + "Reason: " + str(reason) + " /// " + "Version: " + str(version) + " /// " + "Headers:"
      for header in responseHeaders:
         finalMessage += " /// %s" % (header,)

   
   now = datetime.datetime.now()
   finalMessage += " /// Day (Y-M-D H:M:S): " + str(now)

   print finalMessage
   callEmailCronAlert = subprocess.Popen([PATH_TO_EMAIL_ALERT_SCRIPT, '--description="' +  finalMessage + '"', '--file="' + os.path.abspath("check_website_up.py") + '"'])
   rawOutput, errors = callEmailCronAlert.communicate()


def checkWebsiteUp():
   parseO = urlparse(inputArguments[URL_KEY_NAME])

   if parseO.scheme == 'https':
      port = 443
   else:
      port = 80
   
   
   try:
      conn = httplib.HTTPConnection(parseO.netloc, port, timeout=TIMEOUT_SECONDS)
      #conn.set_debuglevel(1)
      conn.request("GET", parseO.path)
      r1 = conn.getresponse()

      if r1.status != 200 and r1.status != 302 and r1.status != 301:
         sendEmailAlert("Unable to reach proper status code for URL " + inputArguments[URL_KEY_NAME] + " /// Received status code: " + str(r1.status) + " /// Timeout limit: " + str(TIMEOUT_SECONDS), conn, r1)
      else:
         print "Successfully found URL: " + inputArguments[URL_KEY_NAME] + " within " + str(TIMEOUT_SECONDS) + " seconds."
   except Exception as inst:
         sendEmailAlert("Unable to reach proper status code for URL " + inputArguments[URL_KEY_NAME] + " /// Exception thrown /// " + str(inst), conn)


def main():
   buildInputVariableDictionary()
   if checkValidUrlInputted():
      checkWebsiteUp() 
   else:
      sendEmailAlert("An invalid input URL was supplied")

if __name__ == "__main__":
    main()

