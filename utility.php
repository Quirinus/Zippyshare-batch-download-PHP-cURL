<?php

	function get_string_between($string, $start, $end)
	{
		$string = " ".$string;
		$ini = strpos($string,$start);
		if ($ini == 0) return "";
		$ini += strlen($start);   
		$len = strpos($string,$end,$ini) - $ini;
		return substr($string,$ini,$len);
	}

	function print_r2($val)
	{
		echo '<pre>';
		print_r($val);
		echo  '</pre>';
	}
	
	function var_dump2($val)
	{
		echo '<pre>';
		var_dump($val);
		echo  '</pre>';
	}
	
	// http://stackoverflow.com/a/12233983/2331033
	function list_abs_path_files_sub($rootpath)
	{
		$fileinfos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootpath));
		foreach($fileinfos as $pathname => $fileinfo) {
			if (!$fileinfo->isFile()) continue;
			$paths[] = $pathname;
		}
		return $paths;
	}
	
	function list_abs_path_files($rootpath)
	{
		$fileinfos = new DirectoryIterator($rootpath);
		foreach ($fileinfos as $pathname => $fileinfo) {
			//if($fileinfo->isDot()) continue;
			if (!$fileinfo->isFile()) continue;
			//$paths[] = $pathname;
			$paths[] = $fileinfo->getPathname();
		}
		return $paths;
	}
	
	//example
	/*$paths = list_abs_path_files_sub('C:\Users\Username\Desktop\Documentation_stuff');
	echo count($paths)."<br>\r\n";
	print_r2($paths);*/
	/*$paths = list_abs_path_files('C:\Users\Username\Desktop\Documentation_stuff');
	echo count($paths)."<br>\r\n";
	print_r2($paths);*/

?>