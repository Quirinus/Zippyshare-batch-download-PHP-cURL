<?php
	
	//author: Ivan Jelenić (Quirinus) @ GitHub
	
	//don't remember where I got it from, probably print_r php docs comments
	function print_r2($val)
	{
		echo '<pre>';
		print_r($val);
		echo  '</pre>';
	}

	function trim_stuff($string)
	{
		return substr($string, 6, -1);
	}
	
	//http://stackoverflow.com/a/3380159/2331033
	function fix_bad_path_names($path)
	{
		$bad = array_merge(
			array_map('chr', range(0,31)),
			array('<', '>', ':', '"', "/", "\\", '|', '?', '*'));
		return str_replace($bad, '', $path);
	}
	
	$file = file_get_contents('GH_example.html'); //fetching a bunch of links and folders from a random site (only for testing purposes)
	
	//get table names for grouping files in dl folders
	preg_match_all('/<span class="mw\-headline">([^\n<]+)<\/span>/i', $file, $folder_names);
	$folder_names = array_map('fix_bad_path_names',$folder_names[1]);
	
	//skip first 4 tables as they don't contain dl links
	$table_delimiter = '<table class="wikitable';
	$parts = explode($table_delimiter, $file);
	$skip_tables = 4;
	$parts = array_slice($parts, $skip_tables);
	array_shift($folder_names);
	
	//extract links and put them in an array that groups them by folder name
	$pattern = '/href\="http:\/\/www[0-9]*\.zippyshare\.com\/v\/[0-9]*\/file.html"/i';
	$parts_count = count($parts);
	for ($i=0;$i<$parts_count;$i++)
	{
		$parts_match_count[$i] = preg_match_all($pattern, $parts[$i], $zippy_links[$i]);
		$zippy_links[$i] = array_map('trim_stuff',$zippy_links[$i][0]);
	}
	$gh_match_count_total = array_sum($parts_match_count);
	//$zippy_links_by_folder_index = $zippy_links;
	$zippy_links = array_combine($folder_names,$zippy_links);
	
	//shows link/folder results
	echo "Total zippyshare link count: $gh_match_count_total";
	print_r2($folder_names);
	print_r2(array_combine($folder_names,$parts_match_count));
	echo "<hr>GH Zippyshare links:";
	print_r2($zippy_links);

?>