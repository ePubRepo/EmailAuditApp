#!/bin/bash

#FINALIZED PROD CODE (closurebuilder.py)
#http://code.google.com/closure/library/docs/closurebuilder.html
/home/appsappsinfo/www/closure-library-2389asj2023/closure/bin/build/closurebuilder.py --root=emailarchive/ --root=closure-library-2389asj2023/ --compiler_jar=/home/appsappsinfo/common/compiler.jar --compiler_flags="--warning_level=VERBOSE" --compiler_flags="--compilation_level=ADVANCED_OPTIMIZATIONS" --compiler_flags="--summary_detail_level=3" --compiler_flags="--externs=/home/appsappsinfo/www/emailarchive/js/spin.js" --output_mode=compiled --output_file=/home/appsappsinfo/www/emailarchive/js/prod/prod.js --namespace=app.emailarchive.bootstrap
