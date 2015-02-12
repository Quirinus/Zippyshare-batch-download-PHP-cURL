# Zippyshare-batch-download-PHP-cURL
A PHP (5.6.3) script that uses cURL to download an array of Zippyshare links.
_____________________________________________________________________

Run dl.php to start downloading... use the zippy\_batch\_dl function (at the end of the file) and feed it arguments. log.php will be created in the download folder.

(working example included, read at the end of features)
_____________________________________________________________________
Features:
- Accepts arrays of zippyshare links, grouped by associative array that contains DL folder names.
- Adapts to temporary download link number generating algorithm changes reasonably well. (other scripts require manualy specifying it, thus having to stop downloading)
- Names files by index, as they appear in the array, then by download link number, and then by file name.
- While downloading, the file name is modified to be *.part.file_name.part - when finished it is renamed to remove both .part .
- When starting a new session, always deletes partially downloaded files and starts downloading them again.
- Can choose if you want to overwrite existing files.
- Checks if files already exist by searching the folder for index number and download link number. Skips already existing complete downloads (unless overwrite is on).
- Another check if file exists, after it fetches the file name from the zippyshare page. Skips already existing complete downloads (unless overwrite is on).
- Can specify various timeout, wait and delay times.
- Can specify from which folder/link to start, and where to end (can choose 'end' to go till the end).
- Can specify download folder.
- Has a pretty extensive log (not very well formatted).
- Has a lot of error detection.
- Example included. dl.php is the main file. file\_list.php, GH\_example.html are just to generate a bunch of working Zippyshare links/folders to feed dl.php for testing/example purposes.


To-Do:
- Cosmetic/speed:
  - add datetimes to each log entry
  - clean up code... it's a mess atm.
  - add comments to everything. to explain what each code piece does.
  - make a nicer display of download links and their structure/number than it currently is.
  - format log to make it actually easy to read
  - check and note which PHP version and libraries this script uses (I'm running it on Windows 7 with: ApacheFriends XAMPP Version 5.6.3 which uses PHP 5.6.3 (VC11 X86 32bit thread safe) + PEAR, Apache 2.4.4, libraries: glob, curl...)
  - optimize code (eg. remove various preg matches if it can be done by quicker functions, maybe a faster approach than using the glob function, refactor some code...)
- Features/functional:
  - add server index to the file name, between array index and url number
  - check file size first (read from site, or to get exact size, curl to get file size from header of the temp dl link - but that requires an aditional curl (if you do it, make it not request body, only header, and add other curl options to make it simpler)) If you read file size from from site, it's here: &lt;font style=&quot;line-height:18px; font-size: 13px; font-weight: bold;&quot;&gt;Size:&lt;/font&gt;&lt;font style=&quot;line-height:18px; font-size: 13px;&quot;&gt;29.52 MB&lt;/font&gt;&lt;br /&gt;
  - re-download files if size doesn't match (give an optional argument for it, that overrides the overwrite argument)
  - make it constantly check a specified file for a 1 or 0 value. in case it's 0, stop running the script after finishing the current download.
  - add arbitrarily nested arrays of folder/links, along with support to specify download start/end folder/link indexes by having them nested like the folder/link array
  - increase the dl timeout according to the dl speed, only if script execution time isn't set to 0 (infinite)
  - download resume
  - support for running two or more of these scripts in parallel
