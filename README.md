# Zippyshare-batch-download-PHP-cURL
A PHP (5.6.3) script that uses cURL to automatically download an array of Zippyshare links.

[GitHub project](https://github.com/Quirinus/Zippyshare-batch-download-PHP-cURL)
_____________________________________________________________________

Run example.php to start downloading... use the zippy_batch_dl function (at the end of the file) and feed it arguments. log.php will be created in the download folder.

_____________________________________________________________________
Features:
- Accepts arrays of zippyshare links, grouped by associative array that contains DL folder names.
- Tries to automatically solve the anti-bot code to get the validation number, in a number of ways. (other scripts require manualy specifying it, thus having to stop downloading, which can happen several times for longer batches, and prevents afk downloading)
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
- Example included: example.php just contains a bunch of working Zippyshare links/folders for testing/example purposes. zippy_batch_dl.php is the main file. 
