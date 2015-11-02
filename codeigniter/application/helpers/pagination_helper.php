<?php
/**
 * Return an array containing informations for formatting pagination
 * and pagination link in html
 */
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
		            'prev_link' => '<span aria-hidden="true">&larr;</span> Previous',
		            'next_link' => 'Next <span aria-hidden="true">&rarr;</span>',
		            'display_pages' => $show_numbers,
								'num_tag_open' => '<li>',
								'num_tag_close' => '</li> ',
		            'full_tag_open' => '',
		            'full_tag_close' => '',
		            'cur_tag_open' => '<li><span class="btn active btn-lg">',
		            'cur_tag_close' => '</span></li> ');
}

/**
 * Return the row where the results start for the limit statement in the SQL query
 */
function getStart($page, $nbPerPage)
{
	return ($page-1) * $nbPerPage;
}

function getOrder($allowedOrderBy = [], $allowedOrders = ['asc', 'desc'], $defaultOrder = 'asc')
{
	if (!in_array($orderBy = get_instance()->input->get('orderBy'), $allowedOrderBy))
	{
		$orderBy = 'id';
	}

	if (!in_array($order = get_instance()->input->get('order'), $allowedOrders))
	{
		$order = $defaultOrder;
	}
	return [$orderBy, $order];
}

/**
 * Return the number of the page (small check) and the start for the SQL query
 */
function getPageAndStart($page, $nbPerPage)
{
  $page = getIntOrOne($page);

  return [$page, getStart($page, $nbPerPage)];
}
/**
 * Return $int if it is a valid strict positive integer,
 * return one in other cases
 */
function getIntOrOne($int)
{
  $int = intval($int);

  if (!($int > 0))
    $int = 1;
  return $int;
}
/**
 * Return $int if it is a valid positive integer,
 * return zero in other cases
 */
function getIntOrZero($int)
{
  $int = intval($int);

  if (!($int >= 0))
    $int = 0;
  return $int;
}
/**
* An helper to pluralize words depending on the value
 * Add an "s" at the end of the string if $value > 0 (or == 0)
 * and return it
 */
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
