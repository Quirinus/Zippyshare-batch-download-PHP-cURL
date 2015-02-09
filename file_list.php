<?php
	
	include_once('utility.php');

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
	
	
	
	
	//fetching a bunch of links and folders from a random site (only for testing purposes)
	$gh1_file = file_get_contents('GH1_example.html');
	$gh2_file = file_get_contents('GH2_example.html');
	
	$table_delimiter = '<table class="wikitable';
	$gh1_parts = explode($table_delimiter, $gh1_file);
	$gh2_parts = explode($table_delimiter, $gh2_file);
		
	preg_match_all('/<span class="mw\-headline">([^\n<]+)<\/span>/i', $gh1_file, $gh1_folder_names);
	preg_match_all('/<span class="mw\-headline">([^\n<]+)<\/span>/i', $gh2_file, $gh2_folder_names);
	$gh1_folder_names = array_map('fix_bad_path_names',$gh1_folder_names[1]);
	$gh2_folder_names = array_map('fix_bad_path_names',$gh2_folder_names[1]);
	
	$skip_tables = 3;
	
	for ($i=0;$i<$skip_tables+1;$i++)
	{
		unset($gh1_parts[$i]);
		unset($gh2_parts[$i]);
	}
	unset($gh1_folder_names[0]);
	unset($gh2_folder_names[0]);
	$gh1_parts = array_values($gh1_parts);
	$gh2_parts = array_values($gh2_parts);
	$gh1_folder_names = array_values($gh1_folder_names);
	$gh2_folder_names = array_values($gh2_folder_names);
	$gh_folder_names = array_merge($gh1_folder_names,$gh2_folder_names);
	
	$pattern = '/href\="http:\/\/www[0-9]*\.zippyshare\.com\/v\/[0-9]*\/file.html"/i';
	$gh1_match_count_total = 0;
	$gh2_match_count_total = 0;
	$gh1_count = count($gh1_parts);
	$gh2_count = count($gh2_parts);

	for ($i=0;$i<$gh1_count;$i++)
	{
		$gh1_match_count[$i] = preg_match_all($pattern, $gh1_parts[$i], $gh1_zippy_links[$i]);
		$gh1_zippy_links[$i] = array_map('trim_stuff',$gh1_zippy_links[$i][0]);
		$gh1_match_count_total += $gh1_match_count[$i];
	}
	
	for ($i=0;$i<$gh2_count;$i++)
	{
		$gh2_match_count[$i] = preg_match_all($pattern, $gh2_parts[$i], $gh2_zippy_links[$i]);
		$gh2_zippy_links[$i] = array_map('trim_stuff',$gh2_zippy_links[$i][0]);
		$gh2_match_count_total += $gh2_match_count[$i];
	}
	$gh1_zippy_links = array_combine($gh1_folder_names,$gh1_zippy_links);
	$gh2_zippy_links = array_combine($gh2_folder_names,$gh2_zippy_links);
	$gh_zippy_links = array_merge($gh1_zippy_links, $gh2_zippy_links);
	
	$gh_match_count_total = $gh1_match_count_total + $gh2_match_count_total;
	
	//shows link/folder results
	echo "Total zippyshare link count: $gh_match_count_total";
	print_r2($gh_folder_names);
	echo "<hr>";
	echo "GH page 1 zippyshare link count: $gh1_match_count_total";
	print_r2(array_combine($gh1_folder_names,$gh1_match_count));
	echo "<hr>";
	echo "GH page 2 zippyshare links count: $gh2_match_count_total";
	print_r2(array_combine($gh2_folder_names,$gh2_match_count));
	echo "<hr>";
	echo "GH page 1 zippyshare links:";
	print_r2($gh1_zippy_links);
	echo "<hr>";
	echo "GH page 2 zippyshare links:";
	print_r2($gh2_zippy_links);
	echo "<hr>";

?>