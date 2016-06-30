# Zippyshare-batch-download-PHP-cURL
A PHP (5.6.3) script that uses cURL to automatically download an array of Zippyshare links.

[GitHub project](https://github.com/Quirinus/Zippyshare-batch-download-PHP-cURL)
_____________________________________________________________________

To-do:
- add server to the file name
- add datetimes to log entries
- add arbitrarily nested array of folder/links, along with support to specify download start/end folder/link indexes by having them nested like the folder/link array
- check file size first (curl or read from site, CURLOPT_RANGE, CURLOPT_WRITEFUNCTION, CURLOPT_HEADERFUNCTION): [Link 1](http://curl.haxx.se/libcurl/php/examples/callbacks.html), [Link 2](http://stackoverflow.com/questions/10991443/curl-get-remote-file-and-force-download-at-same-time)
- re-download files if size doesn't match
- increase the dl time according to the dl speed, only if script execution time isn't set to infinite
- download resume: [Link 1](http://www.ankur.com/blog/106/php/resume-http-downloads-php-curl-fsockopen/), [Link 2](http://stackoverflow.com/questions/2032924/how-to-partially-download-a-remote-file-with-curl byteserving)
