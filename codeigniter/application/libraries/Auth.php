<?php
/**
 * The Auth class that check the rights of a user
 */
class Auth
{
  private $mergedPermissions;
  var $CI;

  public function __construct()
  {
    $this->CI =& get_instance();//save the CodeIgniter instance in order to access methods
    $this->CI->load->model('users_model', 'user');
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
  }

  public function logout($key)
  {
    if($key == $this->CI->session->userdata('key'))
    {
      $this->CI->session->unset_userdata('user', 'groups', 'key');
      $this->setGuest();
      $this->CI->session->set_flashdata('info', $this->CI->lang->line('auth_success_deconnect'));
    }
    else
    {
      show_error($this->CI->lang->line('auth_wrong_key'), 500, $this->CI->lang->line('auth_error'));
    }
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
}
