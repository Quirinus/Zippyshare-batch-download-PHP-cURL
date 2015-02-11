<?php
	
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
	
	$gh_file = file_get_contents('GH_example.html'); //fetching a bunch of links and folders from a random site (only for testing purposes)
	
	preg_match_all('/<span class="mw\-headline">([^\n<]+)<\/span>/i', $gh_file, $gh_folder_names);
	$gh_folder_names = array_map('fix_bad_path_names',$gh_folder_names[1]);
	
	$table_delimiter = '<table class="wikitable';
	$gh_parts = explode($table_delimiter, $gh_file);
	$skip_tables = 4;
	$gh_parts = array_slice($gh_parts, $skip_tables);
	array_shift($gh_folder_names);
	
	$pattern = '/href\="http:\/\/www[0-9]*\.zippyshare\.com\/v\/[0-9]*\/file.html"/i';
	$gh_parts_count = count($gh_parts);
	for ($i=0;$i<$gh_parts_count;$i++)
	{
		$gh_parts_match_count[$i] = preg_match_all($pattern, $gh_parts[$i], $gh_zippy_links[$i]);
		$gh_zippy_links[$i] = array_map('trim_stuff',$gh_zippy_links[$i][0]);
	}
	$gh_match_count_total = array_sum($gh_parts_match_count);
	//$gh_zippy_links_by_folder_index = $gh_zippy_links;
	$gh_zippy_links = array_combine($gh_folder_names,$gh_zippy_links);
	
	//shows link/folder results
	echo "Total zippyshare link count: $gh_match_count_total";
	print_r2($gh_folder_names);
	print_r2(array_combine($gh_folder_names,$gh_parts_match_count));
	echo "<hr>GH Zippyshare links:";
	print_r2($gh_zippy_links);

?>