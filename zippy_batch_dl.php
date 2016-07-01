<?php
	
	//name: Zippyshare batch download (PHP 5.6.3 & cURL)
	//description: Allows automatic download of Zippyshare links and places them in folders.
	//source: https://github.com/Quirinus/Zippyshare-batch-download-PHP-cURL
	//author: Ivan Jelenić (Quirinus) @ GitHub
	
	$version_number = 'v1.6';
	
	//http://stackoverflow.com/a/3380159/2331033
	function fix_bad_path_names($path)
	{
		$bad = array_merge(
			array_map('chr', range(0,31)),
			array('<', '>', ':', '"', "/", "\\", '|', '?', '*')); //cannot be contained in file/folder names
		return trim(str_replace($bad, '', $path));
	}
	
	function left_to_right_slash($text)
	{
		return str_replace('\\','/',$text);
	}
	
	//formatting for the log file
	function red($text) //error
	{
		return "<span class='red'>$text</span><br>\r\n";
	}
	function green($text) //success
	{
		return "<span class='green'>$text</span><br>\r\n";
	}
	function strike($text) //skip
	{
		return "<s>$text</s><br>\r\n";
	}
	
	//create just one level of folders
	function create_folders($paths)
	{
		if (is_array($paths))
		{
			foreach ($paths as $path)
			{
				if (!file_exists($path))
					mkdir($path, 0777, true);
			}
		}
		else
		{
			if (!file_exists($paths))
				mkdir($paths, 0777, true);
		}
	}
	
	//set time limit for the dl according to the file size and dl speed
	/*$filesize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD); //in bytes
	if ($filesize)
		set_time_limit(($filesize/(8*1024))/$dl_speed); //dl speed in kb/s*/

	//get zippyshare download page
	function _zippy_get_page($url, $response_time, $timeout, &$p_error)
	{
		if ($response_time > $timeout)
			$timeout = $response_time;
			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $response_time); //sec limited response time
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); //sec limited time
		$page = curl_exec($ch);
		if (curl_error($ch))
			$p_error = curl_error($ch);
		curl_close($ch);
		return $page;
	}
	
	//check if the zippyshare page is ok for processing
	function _page_has_errors($zippy_page, &$p_error)
	{
		//check for error while curling the page
		if (($p_error !== '') || ($zippy_page === false))
			$p_error = "Error fetching: $p_error";
			
		//check for empty page
		elseif (trim($zippy_page) == '')
			$p_error = 'Error: Page empty.';
			
		//check for title
		elseif (!(preg_match('/<title>([^\n\<]*)<\/title>/i', $zippy_page, $title)))
			$p_error = 'Error: No title. Wrong page? Stop.';
			
		//check if the title contains 'zippyshare.com -'
		elseif (stripos($title[1],'Zippyshare.com - ') === false)
			$p_error = "Error: Wrong page - title '$title' doesn't contain 'Zippyshare.com - '.";
			
		
		elseif (stripos($zippy_page,'File does not exist on this server') !== false)
			$p_error = 'Error: File removed/deleted from zippy share or wrong zippyshare link.';
		
		if ($p_error !== '')
				return true;
		return false;
	}
	
	//download zippyshare file
	function _zippy_get_file($dl_url, $referral_url, $cookie_jsid, $dl_path, $dl_response_time, $dl_timeout, &$c_error)
	{
		if ($dl_response_time > $dl_timeout)
			$dl_timeout = $dl_response_time;
			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $dl_url);
		//curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
		//curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
		//curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: JSESSIONID=$cookie_jsid"));
		//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0');
		//curl_setopt($ch, CURLOPT_HEADER, 1);
		//curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.0) Gecko/20100101 Firefox/14.0.1');
		//curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, $referral_url);
		
		$fp = fopen ($dl_path, 'w+');
		curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $dl_response_time); //sec limited response time
		curl_setopt($ch, CURLOPT_TIMEOUT, $dl_timeout); //sec limited time
		curl_exec($ch);
		if (curl_error($ch))
			$c_error = curl_error($ch);
		curl_close($ch);
		fclose($fp);
	}
	
	//download one file and write to log file
	function _zippy_download_one($zippy_page_url, $folder_path, $file_index, $dl_response_time, $dl_timeout, $dl_fetch_delay, $overwrite=false)
	{
		//get the unique url number
		preg_match('/\/v\/([^\n\\/]+)\/file\./i',$zippy_page_url, $zippy_url_number); 
		
		//get the server number
		if (!preg_match('/www([0-9]*)\./i',$zippy_page_url, $zippy_server_number))
			return red("Error finding the server number. ($zippy_page_url)");
		
		//check if the file is already downloaded (by url number)
		$dirname = dirname(__FILE__);
		$glob_match = left_to_right_slash("$dirname/$folder_path")."/$file_index.{$zippy_url_number[1]}.*";
		$glob = glob($glob_match, GLOB_NOSORT); //GLOB_NOSORT | GLOB_NOESCAPE 
		if (count($glob))
		{
			if (file_exists($glob[0]) && !$overwrite)
			{
				$skip_file = $glob[0];
				$glob_match = left_to_right_slash("$dirname/$folder_path")."/$file_index.{$zippy_url_number[1]}.part.*";
				$glob = glob($glob_match, GLOB_NOSORT); // GLOB_NOSORT | GLOB_NOESCAPE
				if (!count($glob))
					return strike("Skipping $skip_file. Pre-url-fetch check: File already exists. ($zippy_page_url)");
			}
		}
		
		//fetch the download page, and check for errors
		$p_error = '';
		$zippy_page = _zippy_get_page($zippy_page_url, $dl_response_time, $dl_timeout, $p_error);
		if (_page_has_errors($zippy_page, $p_error))
			return red("$p_error ($zippy_page_url)");
		
		//get cookie
		if (!preg_match('/Set\-Cookie: JSESSIONID=([^\n\;]+); Path=/i', $zippy_page, $zippy_cookie_jsid))
			return red("Error matching cookie. ($zippy_page_url)");
			
		//try to solve the anti-bot algorithm to get the dl link's variable verification number
		$algorithm_script_code = end(explode('<script type="text/javascript">',explode('document.getElementById(\'fimage\').href',$zippy_page)[0]));
		$algorithm_variables_code = explode('document.getElementById(\'dlbutton\').href', $algorithm_script_code)[0];
		if (stripos($algorithm_variables_code,'Math') !== false)
			return red("Error matching algorithm, JS Math function used. ($zippy_page_url)");
		if (!preg_match('/\/\s*([^\n\/]*)"\s*\+\s*([^\n]+)\s*\+\s*"([^\n\/]*)\//i',$algorithm_script_code, $algorithm_number_code))
			return red("Error finding algorithm number generating code. ($zippy_page_url)");
		if (stripos($algorithm_variables_code,'var ') !== false)
		{
			if (!preg_match_all('/var ([^\n \$\=]+) \=/i',$algorithm_variables_code, $algorithm_variable_names, PREG_PATTERN_ORDER))
				return red("Error finding algorithm variable names. ($zippy_page_url)");
			$algorithm_variable_names = $algorithm_variable_names[1];
			$algorithm_variable_names_dollar = $algorithm_variable_names;
			array_walk($algorithm_variable_names_dollar, function(&$value, $key) {$value = "$$value";}); //add $ in front of variable names
			$algorithm_number_code[2] = str_replace($algorithm_variable_names,$algorithm_variable_names_dollar,$algorithm_number_code[2]); //add $ to variable names in code
			$algorithm_variables_code = str_replace($algorithm_variable_names,$algorithm_variable_names_dollar,$algorithm_variables_code);
			$algorithm_variables_code = str_replace('var ','',$algorithm_variables_code);
			eval($algorithm_variables_code); //eval is dangerous, but without using it, I'd have to write a whole JS interpreter
		}
		
		//get file name and human verification number
		if (!preg_match("/\+\s*\"[^\n\/]*\/([^\n\"]+)\";/i",$algorithm_script_code, $zippy_dl_url_name))
			return red("Error evaluating the variable number code part from the url. ($zippy_page_url)");
		eval('$human_check_number = "'.$algorithm_number_code[1].'".'.$algorithm_number_code[2].'."'.$algorithm_number_code[3].'";');
		if (!$human_check_number)
			return red("Error finding the name of the file in the download page. ($zippy_page_url)");
		
		//make dl url and path
		$referral_url = $zippy_page_url;
		$dl_url = "http://www{$zippy_server_number[1]}.zippyshare.com/d/{$zippy_url_number[1]}/$human_check_number/{$zippy_dl_url_name[1]}";
		$cookie_jsid = $zippy_cookie_jsid[1];
		$dl_path = "$folder_path\\$file_index.{$zippy_url_number[1]}.".fix_bad_path_names(rawurldecode($zippy_dl_url_name[1]));
		$dl_path_part = "$folder_path\\$file_index.{$zippy_url_number[1]}.part.".fix_bad_path_names(rawurldecode($zippy_dl_url_name[1])).".part";
		
		//download file if it doesn't already exist, or incomplete, or if overwrite on
		if (file_exists($dl_path) && !$overwrite)
			return strike("Skipping: $dl_path. File already exists and overwrite off. ($zippy_page_url)");
		else
		{
			create_folders($folder_path);
			sleep($dl_fetch_delay);
			
			if (file_exists($dl_path))
				unlink($dl_path); //delete
			if (file_exists($dl_path_part))
				unlink($dl_path_part);
			
			//download the file
			$c_error = '';
			set_time_limit($dl_timeout); //0 = unlimited
			_zippy_get_file($dl_url, $referral_url, $cookie_jsid, $dl_path_part, $dl_response_time, $dl_timeout, $c_error);
			if ($c_error !== '') //if error while downloading
			{
				//full file name already exists, delete it
				if (file_exists($dl_path))
				{
					unlink($dl_path);
					return red("Error downloading: $c_error Deleted file: $dl_path ($zippy_page_url)");
				}
				//partialy downloaded file already exists, delete it
				if (file_exists($dl_path_part))
				{
					unlink($dl_path_part);
					return red("Error downloading: $c_error Deleted partial file: $dl_path_part ($zippy_page_url)");
				}
				
				return red("Error downloading: $c_error File path: $dl_path_part ($zippy_page_url)");
			}
			
			//when dl over, if present, delete old full file, and remove "part" from the name of the now finished file
			if (file_exists($dl_path))
				unlink($dl_path);
			if (file_exists($dl_path_part))
				rename($dl_path_part, $dl_path);
			$file_size = round(filesize($dl_path)/(1024*1024),2);
			return green("Download complete: $dl_path ($file_size MB) ($referral_url)");
		}
	}
	
	//download multiple files
	function zippy_download_batch($zippy_links, $parent_folder = 'DL', $start_folder = 0, $end_folder = 'end', $start_link = 0, $end_link = 'end', $dl_response_time=0, $dl_timeout=0, $sleep_between=2, $dl_fetch_delay=1, $overwrite=0)
	{
		//turn folder array into a flat array with paths, and create them
		$folder_names = array_keys($zippy_links);
		$folder_paths = $folder_names;
		array_walk($folder_paths, function(&$value, $key, $parent_folder) {$value = "$parent_folder\\$key.$value";}, $parent_folder);
		create_folders($folder_paths);
		$folder_end = $end_folder === 'end' ? count($folder_names) - 1 : $end_folder; //if the number of folders to download is 'end' then download till the last folder
		
		//create log txt for unformatted logging
		$datetime = new DateTime();
		$datetime = $datetime->format('d-m-Y H:i:s (P \U\T\C)');
		file_put_contents("$parent_folder\\log.txt", "Session started: $datetime (script $version_number). Download folders: $start_folder-$end_folder, download files: $end_link-$end_link. Overwrite: $overwrite.<br>\r\n", FILE_APPEND);
		
		//create log php for viewing formatted log
		if (!file_exists("$parent_folder\\log.php"))
		{
			$log_php = "<!DOCTYPE html>\r\n<html>\r\n<head><meta charset='utf-8'><title>DL Log</title><style>.red{color:red} .green{color:green}</style></head>\r\n<body>\r\n<?php echo file_get_contents(\"log.txt\") ?>\r\n</body>\r\n</html>";
			file_put_contents("$parent_folder\\log.php", $log_php);
		}
		
		//loop over the folders and links to download, and do the download + log
		for ($i = $start_folder; $i <= $folder_end; $i++)
		{
			$link_end = $end_link === 'end' ? count($zippy_links[$folder_names[$i]]) - 1 : $end_link; //if the number of links to download is 'end' then download till the end
			for ($j = $start_link; $j <= $link_end; $j++)
			{
				file_put_contents("$parent_folder\\log.txt", "Download link: {$zippy_links[$folder_names[$i]][$j]}. Path: {$folder_paths[$i]}. Folder/Link index: $i/$j.<br>\r\n", FILE_APPEND);
				$result = _zippy_download_one($zippy_links[$folder_names[$i]][$j], $folder_paths[$i], $j, $dl_response_time, $dl_timeout, $dl_fetch_delay, $overwrite);
				file_put_contents("$parent_folder\\log.txt", $result, FILE_APPEND);
				sleep($sleep_between);
			}
		}
	}
	
?>