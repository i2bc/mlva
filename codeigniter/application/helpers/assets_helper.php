<?php
//Github identicon for default profile picture
function getIdenticon($name)
{
	return 'http://identicon.rmhdev.net/'.$name.'.png';//420x420px
}

function vanillicon($pseudo ='', $size = 100)
{
	return 'https://vanillicon.com/'.md5($pseudo).'_'.$size.'.png';
}

function removeExcessiveSpaces($string)
{
	return preg_replace( '/\s+/', ' ', trim($string));
}
function removeAllSpaces($string)
{
	return preg_replace( '/\s+/', '', trim($string));
}

function safe_auto_link($str)
{
	return auto_link(htmlspecialchars($str));
}

function auto_link_publication($str)
{
	$str = htmlspecialchars($str);
	// Find and replace url
	$patternUrl = "/([\w,\sÀ-ÿ\-]+)(\(((\w*:\/\/|www\.)[^\s()<>;]+\w)\))/i";
	$replacement = '<a href="${3}">${1}</a>';
	$str = preg_replace($patternUrl, $replacement, $str);
	// Find and replace email
	$patternEmail = "#([\w\sÀ-ÿ\-]+)(\(([\w\.\-\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+[^[:punct:]\s])\))#i";
	$replacement = '<a href="mailto:${3}">${1}</a>';
	return preg_replace($patternEmail, $replacement, $str);
}

function base_and_panel($id, $panel) {
	$str = strval($id);
	if ($panel > 0)
		{ $str = $str . "?panel=" . strval($panel); }
	return $str;
}

/**
 * Return true if the column is masked
 */
function isMasked($col)
{
	$col = md5($col);
	return isset($_SESSION['currentDatabase']['col_masked'][$col]) && $_SESSION['currentDatabase']['col_masked'][$col] == 1;
}

//Debug function
function dd($var)
{
  var_dump($var);
  exit();
}

/**
 * Helper to autodetect a CSV file delimiter
 * @param $file the CSV file
 */
function detectCsvDelimiter($file)
{
    $data = null;
		$max = 0;
		$delim_list = array('semicolon'=>";", 'comma'=>",", 'tab'=>"\t");
		$d_count = [];
    $delimiter = $delim_list['comma'];

    foreach($delim_list as $key=>$value)
    {
				$nb_1 = 0;
				//Read first 20 lines
				for ($i=0; $i < 20; $i++)
				{
					$data = fgetcsv($file, 0, $value, $enclosure='"');
					//Early exit if we have reached the end of the file
					if($data === false)
						$i = 20;
					else
						$nb_1 += count($data);
				}
				$d_count[$key] = $nb_1;
				rewind($file);//Go to the beginning
				if($nb_1 > $max)
				{
					$delimiter = $key;
					$max = $nb_1;
				}
    }
		//var_dump($d_count);

    return $delim_list[$delimiter];
}
