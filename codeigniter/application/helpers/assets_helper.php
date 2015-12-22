<?php
//Github identicon for default profile picture
function getIdenticon($name)
{
	return 'https://github.com/identicons/'.$name.'.png';//420x420px
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
