<?php
	
	include_once('file_list.php');

	function fetch_zippy_dl_page($url, &$p_error)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); //5 sec limited response time
		//curl_setopt($ch, CURLOPT_TIMEOUT, 15); //15 sec limited time
		$page = curl_exec($ch);
		if (curl_error($ch))
			$p_error = curl_error($ch);
		
		curl_close($ch);
			
		return $page;
	}
	
	function zippy_dl($dl_path, $dl_response_time, $dl_timeout, $referral_url, $dl_url, $cookie_jsid, &$c_error)
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
		/*$filesize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD); //in bytes
		if ($filesize)
			set_time_limit(($filesize/(8*1024))/$dl_speed); //dl speed in kb/s*/
		curl_close($ch);
		fclose($fp);
	}
	
	//modified http://php.net/manual/en/ref.array.php#108546
	/*function arrayCopyStructure($array) {
			$result = array();
			foreach($array as $key => $val)
			{
				if(is_array($val))
				{
					$result[$key] = arrayCopyStructure($val);
				}
				else
				{
					$result[$key] = 0;
				}
			}
			return $result;
	}
	
	$gh1_dl_flags = arrayCopyStructure($gh1_zippy);
	$gh2_dl_flags = arrayCopyStructure($gh2_zippy);*/
	
	function create_folders($paths)
	{
		if (is_array($paths))
		{
			foreach ($paths as $path)
			{
				if (!file_exists($path))
				{
					mkdir($path, 0777, true);
				}
			}
		}
		else
		{
			if (!file_exists($paths))
			{
				mkdir($paths, 0777, true);
			}
		}
	}

	function zippy_fetch_dl($zippy_page_url, $folder_path, $file_index,	$dl_response_time, $dl_timeout, $dl_fetch_delay, $overwrite=false)
	{
		preg_match('/\/v\/([^\n\\/]+)\/file\./i',$zippy_page_url, $zippy_url_number);
		$dirname = dirname(__FILE__);
		$glob_match = str_replace('\\','/',"$dirname/$folder_path")."/$file_index.{$zippy_url_number[1]}.*";
		$glob = glob($glob_match, GLOB_NOSORT); // GLOB_NOSORT | GLOB_NOESCAPE
		
		if (count($glob))
		{
			if ((file_exists($glob[0]))&&(!$overwrite))
			{
				$skip_file = $glob[0];
				$glob_match = str_replace('\\','/',"$dirname/$folder_path")."/$file_index.{$zippy_url_number[1]}.part.*";
				$glob = glob($glob_match, GLOB_NOSORT); // GLOB_NOSORT | GLOB_NOESCAPE
				if (!count($glob))
				{
					return "<s>Skipping: $skip_file || $zippy_page_url (pre-url-fetch check: file already exists)</s><br>\r\n";
				}
			}
		}
		
		$p_error = '';
		$zippy_page = fetch_zippy_dl_page($zippy_page_url, $p_error);
		if ($p_error !== '')
		{
			return "<span style='color:red;'>Page url: $zippy_page_url (Error fetching: $p_error)</span><br>\r\n";
		}
		if ($zippy_page === false)
		{
			return "<span style='color:red;'>Page url: $zippy_page_url (Error fetching: $p_error)</span><br>\r\n";
		}
		
		//check page title or check if it's empty
		if (trim($zippy_page) == '')
			return "<span style='color:red;'>Page url: $zippy_page_url (error: page empty)</span><br>\r\n";
		if (!(preg_match('/<title>([^\n\<]*)<\/title>/i', $zippy_page, $title)))
			return "<span style='color:red;'>Page url: $zippy_page_url (error: No title. Wrong page - title doesn't have 'Zippyshare.com - ')</span><br>\r\n";
		if (stripos($title[1],'Zippyshare.com - ') === false)
			return "<span style='color:red;'>Page url: $zippy_page_url Page title: $title (error: wrong page - title doesn't have 'Zippyshare.com - ')</span><br>\r\n";
		if (stripos($zippy_page,'File does not exist on this server') !== false)
			return "<span style='color:red;'>Page url: $zippy_page_url (error: file removed/deleted from zippy share or wrong zippyshare link)</span><br>\r\n";
		
		if (!(preg_match('/Set\-Cookie: JSESSIONID=([^\n\;]+); Path=/i', $zippy_page, $zippy_cookie_jsid)))
			return "<span style='color:red;'>Page url: $zippy_page_url (error matching cookie)</span><br>\r\n";
		
		$algorithm_script_code = end(explode('<script type="text/javascript">',explode('document.getElementById(\'fimage\').href',$zippy_page)[0]));
		$algorithm_variables_code = explode('document.getElementById(\'dlbutton\').href', $algorithm_script_code)[0];
		if (stripos($algorithm_variables_code,'Math') !== false)
			return "<span style='color:red;'>Page url: $zippy_page_url (error matching algorithm, JS Math function used)</span><br>\r\n";
		if (!(preg_match('/\/\"\s*\+\s*([^\n]+)\s*\+\s*\"\//i',$algorithm_script_code, $algorithm_number_code)))
			return "<span style='color:red;'>Page url: $zippy_page_url (error: can't find algorithm number generating code)</span><br>\r\n";
		$algorithm_number_code = $algorithm_number_code[1];
		if (stripos($algorithm_variables_code,'var ') !== false)
		{
			if (!(preg_match_all('/var ([^\n \$\=]+) \=/i',$algorithm_variables_code, $algorithm_variable_names, PREG_PATTERN_ORDER)))
				return "<span style='color:red;'>Page url: $zippy_page_url (error finding algorithm variable names)</span><br>\r\n";
			$algorithm_variable_names = $algorithm_variable_names[1];
			$algorithm_variable_names_dollar = $algorithm_variable_names;
			array_walk($algorithm_variable_names_dollar, function(&$value, $key) {$value = "$$value";}); //add $ in front of variable names
			$algorithm_number_code = str_replace($algorithm_variable_names,$algorithm_variable_names_dollar,$algorithm_number_code); //add $ to variable names in code
			$algorithm_variables_code = str_replace('var ','$',$algorithm_variables_code);
			eval($algorithm_variables_code);
		}
		
		if (!(preg_match('/www([0-9]*)\./i',$zippy_page_url, $zippy_page_server)))
			return "<span style='color:red;'>Page url: $zippy_page_url (error: can't find server number)</span><br>\r\n";
		if (!(preg_match("/\+\s*\"\/([^\n\"]+)\";/i",$algorithm_script_code, $zippy_dl_url_name)))
			return "<span style='color:red;'>Page url: $zippy_page_url (error finding the name of the file in the download url)</span><br>\r\n";
		
		eval('$mod_check = '.$algorithm_number_code.';');
		if (!($mod_check))
			return "<span style='color:red;'>Page url: $zippy_page_url (error evaluating the variable number code part from the url)</span><br>\r\n";
		
		$referral_url = $zippy_page_url;
		$dl_url = "http://www{$zippy_page_server[1]}.zippyshare.com/d/{$zippy_url_number[1]}/$mod_check/{$zippy_dl_url_name[1]}";
		$cookie_jsid = $zippy_cookie_jsid[1];
		$dl_path = "$folder_path\\$file_index.{$zippy_url_number[1]}.".fix_bad_path_names(rawurldecode($zippy_dl_url_name[1]));
		$dl_path_part = "$folder_path\\$file_index.{$zippy_url_number[1]}.part.".fix_bad_path_names(rawurldecode($zippy_dl_url_name[1])).".part";
		set_time_limit($dl_timeout); //0 = unlimited
		
		//echo "Referral link: $referral_url<br>\r\n";
		//echo "Download link: $dl_url<br>\r\n";
		//echo "Cookie JSessionID: $cookie_jsid<br>\r\n";
		//echo "Download path: $dl_path<br>\r\n";
		//echo "Download server response/connect time: $dl_response_time sec<br>\r\n";
		//echo "Download timeout ( &gt; response time): $dl_timeout sec<hr>\r\n";
		
		if ((!file_exists($dl_path))||$overwrite)
		{
			create_folders($folder_path);
			sleep($dl_fetch_delay);
			$c_error = '';
			
			if (file_exists($dl_path))
			{
				unlink($dl_path);
			}
			if (file_exists($dl_path_part))
			{
				unlink($dl_path_part);
			}
			
			zippy_dl($dl_path_part, $dl_response_time, $dl_timeout, $referral_url, $dl_url, $cookie_jsid, $c_error);
			if ($c_error !== '')
			{
				if (file_exists($dl_path))
				{
					unlink($dl_path);
					return "<span style='color:red;'>Page url: $zippy_page_url || Deleted file: $dl_path (Error downloading: $c_error)</span><br>\r\n";
				}
				if (file_exists($dl_path_part))
				{
					unlink($dl_path_part);
					return "<span style='color:red;'>Page url: $zippy_page_url || Deleted file: $dl_path_part (Error downloading: $c_error)</span><br>\r\n";
				}
				
				return "<span style='color:red;'>Page url: $zippy_page_url || File path: $dl_path_part (Error downloading: $c_error)</span><br>\r\n";
			}
			
			if (file_exists($dl_path))
			{
				unlink($dl_path);
			}
			if (file_exists($dl_path_part))
			{
				rename($dl_path_part, $dl_path);
			}
			$file_size = round(filesize($dl_path)/(1024*1024),2);
			return "<span style='color:green;'>Download path: $dl_path ($file_size MB) || DL URL: $dl_url</span><br>\r\n";
		}
		else
		{
			return "<s>Skipping: $dl_path || $dl_url (file already exists and overwrite off)</s><br>\r\n";
		}
	}
	
	//$dl_timeout >= $dl_response_time
	function zippy_batch_dl($gh_zippy_links, $gh_folder_names, $gh_parent_folder = 'GH_DL', $start_folder = 0, $end_folder = 'end', $start_link = 0, $end_link = 'end', $dl_response_time=0, $dl_timeout=0, $sleep_between=2, $dl_fetch_delay=1, $overwrite=0)
	{
		$folder_paths = $gh_folder_names;
		array_walk($folder_paths, function(&$value, $key, $gh_parent_folder) {$value = "$gh_parent_folder\\$key.$value";}, $gh_parent_folder);
		create_folders($folder_paths);
		$folder_end = $end_folder === 'end' ? count($gh_folder_names) - 1 : $end_folder;
		$datetime = new DateTime();
		$datetime = $datetime->format('d-m-Y H:i:s (P \U\T\C)');
		file_put_contents("$gh_parent_folder\\log.txt", "Session started: $datetime<br>\r\n", FILE_APPEND);
		if (!(file_exists("$gh_parent_folder\\log.php")))
		{
			$log_php = "<!DOCTYPE html>\r\n<html>\r\n<head><meta charset='utf-8'><title>GH DL Log</title></head>\r\n<body>\r\n<?php echo file_get_contents(\"log.txt\") ?>\r\n</body>\r\n</html>";
			file_put_contents("$gh_parent_folder\\log.php", $log_php);
		}
		for ($i = $start_folder; $i <= $folder_end; $i++)
		{
		
			$link_end = $end_link === 'end' ? count($gh_zippy_links[$gh_folder_names[$i]]) - 1 : $end_link;
			echo $gh_folder_names[$i]."<br>\r\n";
			echo (count($gh_zippy_links[$gh_folder_names[$i]]) - 1)."<br>\r\n";
			for ($j = $start_link; $j <= $link_end; $j++)
			{
				file_put_contents("$gh_parent_folder\\log.txt", "zippy_fetch_dl({$gh_zippy_links[$gh_folder_names[$i]][$j]}, {$folder_paths[$i]}, $j, $dl_response_time, $dl_timeout, $dl_fetch_delay, ".(string)$overwrite.")<br>\r\n", FILE_APPEND);
				$result = zippy_fetch_dl($gh_zippy_links[$gh_folder_names[$i]][$j], $folder_paths[$i], $j, $dl_response_time, $dl_timeout, $dl_fetch_delay, $overwrite);
				file_put_contents("$gh_parent_folder\\log.txt", $result, FILE_APPEND);
				sleep($sleep_between);
			}
		}
	}
	
	$gh_parent_folder = 'GH_DL';
	
	$start_folder = 0;
	$end_folder = 'end';
	
	$start_link = 0;
	$end_link = 'end';
	
	//zippy_batch_dl($gh_zippy_links, $gh_folder_names);
	zippy_batch_dl($gh_zippy_links, $gh_folder_names, $gh_parent_folder, $start_folder, $end_folder, $start_link, $end_link);
	
	//to-do maybe:
	//add server to the file name
	//add datetimes to log entries
	//add arbitrarily nested array of folder/links, along with support to specify download start/end folder/link indexes by having them nested like the folder/link array
	//check file size first (curl or read from site) //<font style="line-height:18px; font-size: 13px; font-weight: bold;">Size:</font>            <font style="line-height:18px; font-size: 13px;">29.52 MB</font><br />
	//re-download files if size doesn't match
	//increase the dl time according to the dl speed, only if script execution time isn't set to infinite
	//download resume
	
	//done:
		//make adaptive recognition of algorithm
		//fix glob
		//name file to $i.$number.$name.part while it's downloading
		//rename file to $i.$number.$name after it's done downloading
		//check for wrong/empty page, or deleted link
		//doesn't exist on server: File does not exist on this server
		//empty: 
		//wrong page apache: Object not found!
	

?>