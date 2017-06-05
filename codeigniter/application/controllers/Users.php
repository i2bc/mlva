<?php
class Users extends CI_Controller {

  const NB_USERS_PER_PAGE = 20;
  const NB_GROUPS_PER_PAGE = 20;
  const AVATAR_WIDTH = 100;
  const AVATAR_HEIGHT = 100;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('Twig');
    $this->load->model('users_model', 'user');
	}

  /**
   * Helper to get the info of a user if it enchant_broker_dict_exists
   * Show a 404 page if we don't find the user in the BDD
   */
  private function findOrFail($user_id)
  {
    if(!($user = $this->user->get($user_id)))
    {
      show_404();
    }
    return $user;
  }

  /**
   * Several security checks to edit user informations
   */
  private function editSecurityCheck($user_id)
  {
    if (!($user_id = getIntOrZero($user_id)))
    {
      show_404();
    }

    if(!(isOwnerById($user_id) || checkRight('edit', 'users')))
    {
      show_403();
    }
    if(!isOwnerById($user_id))
    {
      //Security do avoid admin deletion
      if (inGroup($this->user->getAdminGroupId(), $current_user = false, $this->user->getUserGroups($user_id)))
      {
        show_error(lang('auth_dont_edit_admin'), 403, lang('auth_error'));
      }
    }
  }

/**
 * A generic method to fetch and paginate users
 */
  private function showUsers($page, $url, $orderBy='userId', $page_infos = array(), $where = array(), $order = 'desc', $perPage = self::NB_USERS_PER_PAGE, $tpl ='users')
  {
    $this->load->library('pagination');
    $count = $this->user->count($where);
    $this->pagination->initialize(arrayPagination(base_url() . $url, $count, $perPage));

    list($page, $start)  = getPageAndStart($page, $perPage);

    $data = array('session' => $_SESSION,
                  'count' => $count,
                  'users' => $this->user->groupConcatToArray($this->user->getAll($perPage, $start, $orderBy, $where, $order)),
                  'pagination' => $this->pagination->create_links(),
                  'page_infos' => $page_infos
                  );
		$this->twig->render('users/'.$tpl, $data);
  }

  public function index()
  {
    $this->last_registered();
  }

  public function alphabetic($page = 1)
	{
    $this->showUsers($page, '/users/alphabetic/', 'username', [], [], 'asc');
	}

  /**
   * Delete a user
   */
  public function delete($user_id, $key="")
  {
    if($key != $this->session->key)
      show_403();

    checkRight('delete', 'users', true);

    if (($user_id = getIntOrZero($user_id)) && ($user = $this->user->get($user_id)))
    {
      if($user['id'] == $this->session->user['id'])
      {
        show_error("You can not delete your own account, please contact an admin", 403, lang('auth_error'));
      }
      else
      {
        //Security do avoid admin deletion
        if (inGroup($this->user->getAdminGroupId(), $current_user = false, $this->user->getUserGroups($user_id)))
        {
          show_error(lang('auth_dont_edit_admin'), 403, lang('auth_error'));
        }
        $this->user->deleteUser($user_id);
        setFlash('info', "The user has been deleted");
        redirect(base_url('users/'));      }

    }
    else
    {
      show_404();
    }
  }

  public function dashboard()
  {
    redirectIfNotLogged();
    $data = array(
      'session' => $_SESSION,
      'groups' => $this->user->getUserGroups($user_id = $this->session->user['id']),
    );
    $this->twig->render('users/dashboard', array_merge(getInfoMessages(), $data));
  }
/**
 * Edit a user
 * Multiples check are made to avoid editing an admin or choosing an already taken email
 */
  public function edit ($user_id = 0) {

    $this->editSecurityCheck($user_id);
    $user = $this->findOrFail($user_id);
    $this->load->library('form_validation');
    $info = array('info' => $this->session->flashdata('info'));

    if($this->form_validation->run('edit_user')) {
      $email = $this->input->post('email');
      $username = $this->input->post('username');

      $error = '';

      // Check if the email is not used by another user
      $usernameCheck = $this->user->getWhere(['username' => $username]);
      if ($usernameCheck && ($usernameCheck['id'] != $user_id)) {
        $error = lang('form_validation_username_unique');
      }
      // Check if the email is not used by another user
      $userEmailCheck = $this->user->getWhere(['email' => $email]);
      if ($userEmailCheck && ($userEmailCheck['id'] != $user_id)) {
        $error = lang('form_validation_email_unique');
      }

      if ($error != '') {
        $info['error'] = $error;
      } else {
        if (($password = $this->input->post('password')) && $this->input->post('password_confirm')) {
          $inputs = [ 'username' => $username, 'email' => $email, 'password' => simpleHash($password) ];
        } else {
          $inputs = [ 'username' => $username, 'email' => $email ];
        }
        if (checkRight('edit', 'users')) {
          $groups = $this->input->post('groups') ? $this->input->post('groups') :  [];
          $this->user->updateUserGroups($groups, $user_id);
          $info['info'] = lang('auth_success_edit_group');
        }
        $this->user->update($inputs, ['id' => $user_id]);
        $user = $this->user->get($user_id);
        $info['success'] = lang('auth_success_edit');
        $this->auth->login($user = $this->user->get($user_id), $this->user->getUserGroups($user_id));
      }
    }

    $this->twig->render('users/edit', array_merge($info, [
      'session' => $_SESSION,
      'user' => $user,
      'user_groups' => $this->user->getUserGroups($user_id),
      'groups' => $this->user->getAllGroups()
    ]));
  }

  /**
   * Edit profile informations of a user
   */
    public function editInfos($user_id = 0)
    {
      $this->editSecurityCheck($user_id);
      $user = $this->findOrFail($user_id);

      $this->load->library('form_validation');
      $info = array('info' => $this->session->flashdata('info'));

      if($this->form_validation->run('edit_user_infos'))
      {
        $inputs = $this->input->post(['first_name', 'last_name', 'website', 'bio']);

        $this->user->update($inputs, ['user_id' => $user_id], true);
        $user = $this->user->get($user_id);
        $info['success'] = lang('auth_success_edit');
      }
      $data = array(
        'session' => $_SESSION,
        'user' => $user,
        'user_groups' => $this->user->getUserGroups($user_id),
        'groups' => $this->user->getAllGroups()
      );
      $data = array_merge($info, $data);
      $this->twig->render('users/edit', $data);
    }

    /**
     * Create a group (but cannot modify permissions)
     */
    public function create_group()
    {
      redirectIfNotLogged();

      $this->load->library('form_validation');

      if($this->form_validation->run('group'))
      {
        $infos = array_merge($this->input->post(['name', 'description']), ['permissions' => '{"database.view":1}']);
        $group_id = $this->user->createGroup($infos);
        setFlash('info', lang('auth_group_created'));

        $users = $this->input->post('_users') ? $this->input->post('_users') : [];
        $this->user->syncUsersOfGroup($users, $group_id);
        $_SESSION['groups'] = $this->user->getUserGroups(getCurrentUserId());
        redirect(base_url('users/edit_group/'.$group_id));
      }
      $data = array(
        'session' => $_SESSION,
        'users' => [$this->user->get(getCurrentUserId())]
      );
      $this->twig->render('users/group_create', array_merge(getInfoMessages(), $data));
    }

  /**
   * Edit a group (edit name, add/remove members)
   */
  public function edit_group($group_id = 0)
  {
    if (!($group_id = getIntOrZero($group_id)))
    {
      show_404();
    }
    //Check if the user is in the group or if it has the right
    if(!(checkRight('edit', 'group')||inGroup($group_id, true)))
      show_403();

    $this->load->library('form_validation');

    if($this->form_validation->run('group'))
    {
      $this->user->updateGroup(['name' => $this->input->post('name'), 'description' => $this->input->post('description')], ['id' => $group_id]);
      $users = $this->input->post('_users') ? $this->input->post('_users') : [];
      $this->user->syncUsersOfGroup($users, $group_id);
      $this->session->set_flashdata('success', lang('auth_success_edit'));
    }
    $data = array(
      'session' => $_SESSION,
      'group' => $this->user->getGroup($group_id),
      'users' => $this->user->getUsersOfGroup($group_id)
    );
    $this->twig->render('users/group_edit', array_merge(getInfoMessages(), $data));
  }

/**
 * Reset and send a new password to a user
 */
  public function forgotten_password()
  {
    redirectIfLogged();
    $this->load->library('form_validation');
    $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
    $this->form_validation->set_rules('g-recaptcha-response', 'Anti-bot image (Captcha)', 'trim|required|isCaptchaValid');
    $info = array('info' => $this->session->flashdata('info'));

    if($this->form_validation->run())
    {
      $info['success'] = lang('email_sent_if_exist');
      $email = $this->input->post('email');
      if ($user = $this->user->getWhere(['email' => $email]))
      {
        $newPassword = $this->auth->getRandomPassword(); //Get a random string
        $this->user->update(['password' => simpleHash($newPassword)], ['id' => $user['id']]);

        $this->load->library('emailer');
        $this->emailer->sendForgottenPassword($email, $user['username'], $newPassword);
      }
    }
    $data = array_merge($info, array('session' => $_SESSION));
    $this->twig->render('users/forgotten_password', $data);
  }

  public function last_connected($page = 1)
	{
    $page_infos = array('title' => 'Last users connected');
    $this->showUsers($page, '/users/last_connected/', 'last_login', $page_infos);
	}

	public function last_registered($page = 1)
	{
    $page_infos = array('title' => 'Last users registered');
    $this->showUsers($page, '/users/last_registered/', 'userId', $page_infos);
	}

  public function login()
  {
    redirectIfLogged();

    $this->load->library('form_validation');
    $info = getInfoMessages();

    if($this->form_validation->run('login'))
    {
      if ($user_id = $this->user->authenticate($this->input->post(array('username', 'password'))))
      {
        $this->auth->login($user = $this->user->get($user_id), $this->user->getUserGroups($user_id));

        if ($this->input->post('remember_me'))
        {
          $this->auth->setAutologinCookie($user);
        }
        redirect('databases');
      }
      else
      {
        $info['error'] = lang('auth_error_login');
      }
    }
    $data = array_merge($info, array('session' => $_SESSION));
    $this->twig->render('users/login', $data);
  }

/**
 * Logout the user
 * Check if the key pass in the url match with the session one to avoid unwanted logout
 */
  public function logout($key = '')
  {
    if(isLogged())
      $this->auth->logout($key);

    redirect(base_url('users/login'));
  }

  public function profile($username)
  {
    if (!($user = $this->user->getWhere(['username' => $username])))
    {
      show_404();
    }
    $data = array(
      'session' => $_SESSION,
      'user' => $user
    );

    $this->twig->render('users/profile', array_merge($data, getInfoMessages()));
  }

  public function signup()
  {
    redirectIfLogged();
    $this->load->library('form_validation');
    $info = getInfoMessages();

    if($this->form_validation->run('signup'))
    {
      $inputs = array_merge($this->input->post(['username', 'email', 'password']), ['token' => $this->auth->getRandomPassword()]);
      $user_id = $this->user->create($inputs);
      $this->auth->login($user = $this->user->get($user_id), $this->user->getUserGroups($user_id));

      $this->load->library('emailer');
      $this->emailer->sendWelcomeMessage($user['email'], $user['username']);

      setFlash('success', lang('auth_success_signup'));
      redirect('users/edit/'.$user_id);
    }
    $data = array_merge($info, array('session' => $_SESSION));
    $this->twig->render('users/signup', $data);
  }
}
