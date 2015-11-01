<?php
/**
 * The Auth class that check the rights of a user
 */
class Auth
{
  private $mergedPermissions;
  var $CI;
  const COOKIE_AUTOLOGIN_DURATION = 172800;// 3600*24*2 = 2 days
  const COOKIE_SEPARATOR = '-----'; //separator in the autologin cookie

  public function __construct()
  {
    $this->CI =& get_instance();//save the CodeIgniter instance in order to access methods
    $this->CI->load->model('users_model', 'user');
    $this->tryAutoLogin(); //Try to login the user if he is not logged and has a autologin cookie
    $this->setGuestIfNotLogged();//Initialise the session if it is empty
  }

/**
 * Decode the JSON string of permissions
 */
  public function decodePermissions($permissions)
	{

		if ( ! $_permissions = json_decode($permissions, true))
		{
			throw new Exception("Cannot JSON decode permissions [$permissions].");
		}

    return $_permissions;
	}

  /**
   * Delete the autologin cookie
   */
  public function deleteAutologinCookie()
  {
    setcookie('autologin', '', time() - 3600, '/');
  }
  /**
   * Return the unique key to identify a user for the autologin key
   */
  public function getCookieKey(array $user)
  {
    return sha1($user['token'].$user['username'].$user['id'].'Some_Stuff-4nd-D4ta;'.$this->CI->input->ip_address());
  }

  /**
   * Returns an array of merged permissions for each
   * group the user is in.
   *
   * @return array
   */
	public function getMergedPermissions($groups)
	{
		if ( ! $this->mergedPermissions)
		{
			$permissions = [];

			foreach ($groups as $group)
			{
				$permissions = array_merge($permissions, $this->decodePermissions($group['permissions']));
        unset($group['permissions']);
			}

		}

		return $this->mergedPermissions = $permissions;
	}

  /**
   * Generate a random string
   */
  public function getRandomPassword()
  {
    return sha1(rand(0, time()).uniqid());
  }

  /*Login the user and decode its permissions
  * return the user array without
  */
  public function login(array $user, array $groups)
  {
    $user['permissions'] = $this->getMergedPermissions($groups);
    $data = array(
      'user' => $user,
      'groups' => $groups,
      'key' => sha1($user['id'].uniqid()),
      'isLogged' => TRUE
    );
    $this->CI->session->set_userdata($data);
    $this->CI->session->set_flashdata('success', lang('auth_logged'));
    $this->CI->user->update(['last_login' => Carbon\Carbon::now()], ['id' => $user['id']]);
  }

  public function logout($key)
  {
    if($key == $this->CI->session->userdata('key'))
    {
      $this->deleteAutologinCookie();
      $this->CI->session->unset_userdata('user', 'groups', 'key');
      $this->setGuest();
      $this->CI->session->set_flashdata('info', lang('auth_success_deconnect'));
    }
    else
    {
      show_error(lang('auth_wrong_key'), 500, lang('auth_error'));
    }
  }

  /**
   * Set the session infos to a guest user
   */
  public function setGuest()
  {
    $data = array(
      'user' => array('id' => 0, 'permissions' => []),
      'groups' => [],
      'key' => '',
      'isLogged' => FALSE
    );
    $this->CI->session->set_userdata($data);
  }

  public function setGuestIfNotLogged()
  {
    if (!isLogged())
    {
      $this->setGuest();
    }
  }

  /**
   * Set the autologin cookie for a given user
   */
  public function setAutologinCookie(array $user)
  {
    setcookie('autologin', $user['id'].self::COOKIE_SEPARATOR.$this->getCookieKey($user), time() + self::COOKIE_AUTOLOGIN_DURATION, '/');
  }
  /**
   * Try to autologin the user if the cookie autologin exist and is valid
   * Return true if it has succeeded
   */
  public function tryAutoLogin()
  {
    if (!isLogged() && ($cookie = $this->CI->input->cookie('autologin')))
    {
      //Check if the cookie value seems to have an acceptable format
      if (preg_match('/[0-9]+('.self::COOKIE_SEPARATOR.')[0-9a-f]{40}/', $cookie))
      {
        list($id, $cookie_key) = explode(self::COOKIE_SEPARATOR, $cookie);

        if ($user = $this->CI->user->get($id))
        {
          if ($cookie_key == $this->getCookieKey($user))
          {
            //Login the user
            $this->login($user, $this->CI->user->getUserGroups($id));
            //Regenerate the token for more security
            $user['token'] = $this->getRandomPassword();
            $this->CI->user->update(['token' => $user['token']], ['id' => $user['id']]);
            //Extend cookie duration
            $this->setAutologinCookie($user);
            return true;
          }
        }
      }
      //Delete the cookie
      $this->deleteAutologinCookie();
      return false;
    }
  }
}
