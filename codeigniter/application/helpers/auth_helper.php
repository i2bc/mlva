<?php
/**
 * Check if the given or current user has the given right
 *
 * @param  string  $right         name of the right
 * @param  string  $prefix       eventual prefix of the right
 * @param  boolean $throwError  indicates wether a 403 error should be thrown if the user has not the good right
 *
 * @return boolean        		  true if the user has the right, false if not
 */
function checkRight($right, $prefix = '', $throwError = false)
{
	$name = ($prefix ? $prefix.'.' : '').$right;

	$hasRight = hasAccess($name);//This could be optimized with a session remember

	if ($throwError && !$hasRight)
	{
		 show_403();
	}

	return $hasRight;
}

/**
* Return the html for the google captcha
**/
function getCaptchaMarkup()
{
	return '<div class="g-recaptcha" data-sitekey="'.CAPTCHA_PUBLIC_KEY.'"></div>';
}

/**
 * Return the name of a group given its id (and false if not found)
 * @param array $groups the array where we have to look for the id
 */
function getGroupName($groupId, $groups = [])
{
	foreach ($groups as $_group)
	{
		if ($_group['id'] == $groupId)
		{
			return $_group['name'];
		}
	}

	return false;
}
/**
 * Return an array of the possible info messages
 */
function getInfoMessages()
{
	return  [
						'info' => get_instance()->session->flashdata('info'),
						'error' => get_instance()->session->flashdata('error'),
						'success' => get_instance()->session->flashdata('success')
					];
}
/**
 * See if a user has access to the passed permission(s).
 * Permissions are merged from all groups the user belongs to
 * andthen are checked against the passed permission(s).
 *
 * If multiple permissions are passed, the user must
 * have access to all permissions passed through, unless the
 * "all" flag is set to false.
 *
 * Super users have access no matter what.
 *
 * @param  string|array  $permissions
 * @param  bool  $all
 * @return bool
 */
function hasAccess($permissions, $all = true)
{
	if (isAdmin())
	{
		return true;
	}

	return hasPermission($permissions, $all);
}

/**
* Returns if the user has access to any of the
* given permissions.
*
* @param  array  $permissions
* @return bool
*/
function hasAnyAccess(array $permissions)
{
	return hasAccess($permissions, false);
}

/**
 * See if a user has access to the passed permission(s).
 * Permissions are merged from all groups the user belongs to
 * and then are checked against the passed permission(s).
 *
 * If multiple permissions are passed, the user must
 * have access to all permissions passed through, unless the
 * "all" flag is set to false.
 *
 * Super users DON'T have access no matter what.
 *
 * @param  string|array  $permissions
 * @param  bool  $all
 * @return bool
 */
function hasPermission($permissions, $all = true)
{
	$mergedPermissions = $_SESSION['user']['permissions'];

	if ( ! is_array($permissions))
	{
		$permissions = (array) $permissions;
	}

	foreach ($permissions as $permission)
	{
		$matched = false;
		//Check if the permission exist and has its value equal to 1
		foreach ($mergedPermissions as $mergedPermission => $value)
		{
			if ($permission == $mergedPermission  && $value == 1)
			{
				$matched = true;
				break;
			}
		}

		// Now, we will check if we have to match all
		// permissions or any permission and return
		// accordingly.
		if ($all === true && $matched === false)
		{
			//We have not found one permission among all required, we should exit
			return false;
		}
		elseif ($all === false && $matched === true)
		{
			//We have found one permission, we don't have to go further in the loop
			return true;
		}
	}

	if ($all === false)
	{
		//If we have not found a single permission among all, access not granted
		return false;
	}

	return true;
}

function isAdmin()
{
  return inGroup(1, true);
}

/**
 * Check if the captcha code entered is valid (ReCaptcha V2)
 */
function isCaptchaValid($code, $ip = null)
{
	if (empty($code))
	{
			return false;
	}

	$params = [
	   'secret'    => CAPTCHA_SECRET_KEY,
	   'response'  => $code
	];

	if($ip)
	{
		$params['remoteip'] = $ip;
	}

	$url = "https://www.google.com/recaptcha/api/siteverify?" . http_build_query($params);

	if (function_exists('curl_version'))//If Curl is available
	{
	   $curl = curl_init($url);
	   curl_setopt($curl, CURLOPT_HEADER, false);
	   curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	   curl_setopt($curl, CURLOPT_TIMEOUT, 1);
	   curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	   $response = curl_exec($curl);
	}
	else
	{
	   $response = file_get_contents($url);
	}

	if (empty($response) || is_null($response))
	{
			return false;
	}

	$json = json_decode($response);
	return $json->success;
}

/**
 * See if the user is in the given group.
 *
 * @param  The id of the group $groupId
 * @param  Whether to check the current user or not $current_user
 * @param  The group of groups in which we have to look $userGroups
 * @return bool
 */
function inGroup($groupId, $current_user = false, $userGroups = [])
{
	//If $userGroups is an empty array, don't fill it
	if ($current_user)
	{
		$userGroups = $_SESSION['groups'];
	}
	foreach ($userGroups as $_group)
	{
		if ($_group['id'] == $groupId)
		{
			return true;
		}
	}

	return false;
}

/**
 * See if the user is in the given group.
 *
 * @param  The name of the group $groupName
 * @return bool
 */
function inGroupByName($groupName)
{
	foreach ($_SESSION['groups'] as $_group)
	{
		if ($_group['name'] == $groupName)
		{
			return true;
		}
	}

	return false;
}

/**
 * Return true if $json is a valid json string
 */
function isJsonValid($json)
{
	return json_decode($json) ? true : false;
}


/**
 * Check if the user is logged (and if the get variable is set)
 */
function isLogged()
{
  return !empty($_SESSION['isLogged']) ? $_SESSION['isLogged'] : false;
}

/**
 * Check if the user owned an object identified by user_id
 */
function isOwnerById($user_id)
{
	return (!$_SESSION['user']['id'] == 0) ? $_SESSION['user']['id'] == getIntOrZero($user_id) : false;
}

function redirectIfLogged($url = '')
{
	if (empty($url))
	{
		$url = base_url('users/');
	}
	if (isLogged())
	{
		redirect($url);
	}
}

/**
 * A shortcut for the show_error function
 */
function show_403()
{
	show_error(lang('auth_error_403'), 403, lang('auth_error'));
}

/**
 * An alias to the php function
 */
function simpleHash($str)
{
	return password_hash($str, PASSWORD_DEFAULT);
}
