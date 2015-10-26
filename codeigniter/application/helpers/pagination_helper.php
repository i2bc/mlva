<?php
if (!function_exists('arrayPagination'))
{
	function arrayPagination($url, $count, $nb_per_page = 4, $pages=true, $show_numbers=true, $first_and_last=false, $queryString=true)
	{
		return  array('base_url' => $url,
                  'reuse_query_string' => true,
			            'total_rows' => $count,
			            'per_page' => $nb_per_page,
			            'use_page_numbers' => $pages,
			            'first_link' => $first_and_last,
			            'last_link' => $first_and_last,
			            'prev_tag_open' => '<li>',
			            'prev_tag_close' => '</li>',
			            'next_tag_open' => '<li>',
			            'next_tag_close' => '</li>',
			            'prev_link' => '<span aria-hidden="true">&larr;</span> Page Précédente',
			            'next_link' => 'Page Suivante <span aria-hidden="true">&rarr;</span>',
			            'display_pages' => $show_numbers,
									'num_tag_open' => '<li>',
									'num_tag_close' => '</li> ',
			            'full_tag_open' => '',
			            'full_tag_close' => '',
			            'cur_tag_open' => '<li><span class="btn active btn-lg">',
			            'cur_tag_close' => '</span></li> ');
	}
}
function getStart($page, $nbPerPage)
{
	return ($page-1) * $nbPerPage;
}
if (!function_exists('getPageAndStart'))
{
  function getPageAndStart($page, $nbPerPage)
  {
    $page = getIntOrOne($page);

    return [$page, getStart($page, $nbPerPage)];
  }
}
if (!function_exists('getIntOrOne'))
{
  function getIntOrOne($int)
  {
    $int = intval($int);

    if (!($int > 0))
      $int = 1;
    return $int;
  }
}
if (!function_exists('getIntOrZero'))
{
  function getIntOrZero($int)
  {
    $int = intval($int);

    if (!($int >= 0))
      $int = 0;
    return $int;
  }
}
function plural($str='', $value)
{
	if ($value > 1 or $value == 0)
	{
		return $value.' '.$str.'s';
	}
	else
	{
		return $value.' '.$str;
	}
}
