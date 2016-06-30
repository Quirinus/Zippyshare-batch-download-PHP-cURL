<?php

	include_once('zippy_batch_dl.php');
	
	function print_r2($stuff)
	{
		echo '<pre>';
		print_r($stuff);
		echo '</pre>';
	}

	$zippy_links = [
		"Rock Band ACDC" => [
			"http://www38.zippyshare.com/v/30819272/file.html",
			"http://www15.zippyshare.com/v/98285835/file.html",
			"http://www46.zippyshare.com/v/37059986/file.html",
			"http://www53.zippyshare.com/v/57652376/file.html",
			"http://www41.zippyshare.com/v/10556896/file.html",
			"http://www40.zippyshare.com/v/81927964/file.html"
		],
		"Green Day Rock Band" => [
			"http://www72.zippyshare.com/v/27314844/file.html",
			"http://www6.zippyshare.com/v/5353353/file.html",
			"http://www49.zippyshare.com/v/49586179/file.html",
			"http://www25.zippyshare.com/v/75260868/file.html"
		]
	];
	
	//example usage
	$parent_folder = 'DL_new';
	
	$start_folder = 0;
	$end_folder = 'end';
	
	$start_link = 0;
	$end_link = 'end';
	
	$folder_names = array_keys($zippy_links);
	
	print_r2($zippy_links);
	print_r2($folder_names);
	
	zippy_batch_dl($zippy_links, $folder_names, $parent_folder, $start_folder, $end_folder, $start_link, $end_link);
	
?>