# Zippyshare-batch-download-PHP-cURL
A PHP (5.6.3) script that uses cURL to automatically download an array of Zippyshare links.

[GitHub project](https://github.com/Quirinus/Zippyshare-batch-download-PHP-cURL)
_____________________________________________________________________
To-Do:
- Cosmetic/speed:
  - add datetimes to each log entry
  - clean up code... it's a mess atm.
  - make a nicer display of download links and their structure/number than it currently is.
  - format log to make it actually easy to read
  - check and note which PHP version and libraries this script uses (I'm running it on Windows 7 with: ApacheFriends XAMPP Version 5.6.3 which uses PHP 5.6.3 (VC11 X86 32bit thread safe) + PEAR, Apache 2.4.4, libraries: glob, curl...)
  - optimize code (eg. remove various preg matches if it can be done by quicker functions, maybe a faster approach than using the glob function, refactor some code...)
- Features/functional:
  - add server index to the file name, between array index and url number
  - check file size first (read from site, CURLOPT_RANGE, CURLOPT_WRITEFUNCTION, CURLOPT_HEADERFUNCTION, or to get exact size, curl to get file size from header of the temp dl link - but that requires an aditional curl (if you do it, make it not request body, only header, and add other curl options to make it simpler)) If you read file size from from site, it's here: &lt;font style=&quot;line-height:18px; font-size: 13px; font-weight: bold;&quot;&gt;Size:&lt;/font&gt;&lt;font style=&quot;line-height:18px; font-size: 13px;&quot;&gt;29.52 MB&lt;/font&gt;&lt;br /&gt; : [Link 1](http://curl.haxx.se/libcurl/php/examples/callbacks.html), [Link 2](http://stackoverflow.com/questions/10991443/curl-get-remote-file-and-force-download-at-same-time)
  - re-download files if size doesn't match (give an optional argument for it, that overrides the overwrite argument)
  - make it constantly check a specified file for a 1 or 0 value. in case it's 0, stop running the script after finishing the current download.
  - add arbitrarily nested arrays of folder/links, along with support to specify download start/end folder/link indexes by having them nested like the folder/link array
  - increase the dl timeout according to the dl speed, only if script execution time isn't set to 0 (infinite)
  - - download resume: [Link 1](http://www.ankur.com/blog/106/php/resume-http-downloads-php-curl-fsockopen/), [Link 2](http://stackoverflow.com/questions/2032924/how-to-partially-download-a-remote-file-with-curl byteserving)
  - support for running two or more of these scripts in parallel