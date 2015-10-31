<?php
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
