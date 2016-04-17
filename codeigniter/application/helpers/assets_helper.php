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
