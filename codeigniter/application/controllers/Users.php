<?php
class Users extends CI_Controller {

  const NB_USERS_PER_PAGE = 4;
  const NB_GROUPS_PER_PAGE = 20;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('Twig');
    $this->load->model('users_model', 'user');
	}

  private function getForgottenPasswordMessage($username, $newPassword)
  {
    $message = '<html><h3>Votre mot de passe a bien été réinitialisé</h3>';
    $message.= '<p>Voici vos identifiants: <br> Pseudo: '.$username.'<br> Mot de passe: '.$newPassword.'</p>';
    $message.= '<h4><a href="'.base_url().'users/login"> Retour au site </a></h4></html>';
    return $message;
  }

  private function getOrder($allowedOrderBy = [], $allowedOrders = ['asc', 'desc'], $defaultOrder = 'asc')
  {
    if (!in_array($orderBy = $this->input->get('orderBy'), $allowedOrderBy))
    {
      $orderBy = 'id';
    }

    if (!in_array($order = $this->input->get('order'), $allowedOrders))
    {
      $order = $defaultOrder;
    }
    return [$orderBy, $order];
  }

  /**
   * A small helper function to send an email
   */
  private function sendEmail($to, $subject, $message)
  {
    $this->load->library('email');
    $this->email->set_protocol('mail'); //if we use the smtp or mail() fn
    $this->email->from('ne_pas_repondre@lemondedustopmotion.fr', 'Le Monde Du Stop Motion');
    $this->email->to($to);
    $this->email->subject($subject);
    $this->email->message($message);
    return $this->email->send();
  }

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

  public function edit($user_id = 0)
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
        show_error('Vous ne pouvez pas modifier un administrateur', 403, 'Une erreur est survenue');
      }
    }

    $this->load->library('form_validation');
    $info = array('info' => $this->session->flashdata('info'));

    if($this->form_validation->run('edit_user'))
    {
      $email = $this->input->post('email');
      $user = $this->user->getWhere(['email' => $email]);
      //Check if the email is not used by another user
      if ($user && ($user['id'] != $user_id))
      {
        $info['error'] = 'L\'email est déjà utilisé par un autre utilisateur';
      }
      else
      {
        if (($password = $this->input->post('password')) && $this->input->post('password_confirm'))
        {
          $inputs = ['email' => $email, 'password' => simpleHash($password)];
        }
        else
        {
          $inputs = ['email' => $email];
        }
        if (checkRight('edit', 'users'))
        {
          $groups = $this->input->post('groups') ? $this->input->post('groups') :  [];
          $this->user->updateUserGroups($groups, $user_id);
          $info['info'] = 'Les groupes ont été mis à jour';
        }
        $this->user->update($inputs, ['id' => $user_id]);
        $info['success'] = 'Les informations ont bien été enregistrées';
      }
    }
    $data = array(
      'session' => $_SESSION,
      'user' => $this->user->get($user_id),
      'user_groups' => $this->user->getUserGroups($user_id),
      'groups' => $this->user->getAllGroups()
    );
    $data = array_merge($info, $data);
    $this->twig->render('users/edit', $data);
  }

  public function forgotten_password()
  {
    redirectIfLogged();
    $this->load->library('form_validation');
    $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
    $this->form_validation->set_rules('g-recaptcha-response', 'Image anti-robots (Captcha)', 'trim|required|isCaptchaValid');
    $info = array('info' => $this->session->flashdata('info'));

    if($this->form_validation->run())
    {
      $info['success'] = 'Un email a été envoyé avec un nouveau mot de passe si le compte existe';
      $email = $this->input->post('email');
      if ($user = $this->user->getWhere(['email' => $email]))
      {
        $newPassword = $this->auth->GetRandomPassword(); //Get a random string
        $this->user->update(['password' => simpleHash($newPassword)], ['id' => $user['id']]);

        $message = $this->getForgottenPasswordMessage($user['username'], $newPassword);
        if (!$this->sendEmail($email, "Votre nouveau mot de passe", $message))
        {
          unset($info['success']);
          $info['error'] = 'L\'email n\'a pas pu être envoyé, veuillez contacter un administrateur';
        }
      }
    }
    $data = array_merge($info, array('session' => $_SESSION));
    $this->twig->render('users/forgotten_password', $data);
  }

  public function last_connected($page = 1)
	{
    $page_infos = array('title' => 'Les derniers utilisateurs connectés');
    $this->showUsers($page, '/users/last_connected/', 'last_login', $page_infos);
	}

	public function last_registered($page = 1)
	{
    $page_infos = array('title' => 'Les derniers membres inscrits');
    $this->showUsers($page, '/users/last_registered/', 'userId', $page_infos);
	}

  public function login()
  {
    redirectIfLogged();

    $this->load->library('form_validation');
    $info = array('info' => $this->session->flashdata('info'));

    if($this->form_validation->run('login'))
    {
      if ($user_id = $this->user->authenticate($this->input->post(array('username', 'password'))))
      {
        $this->auth->login($this->user->get($user_id), $this->user->getUserGroups($user_id));
        $this->user->update(['last_login' => Carbon\Carbon::now()], ['id' => $user_id]);
        $info['success'] = 'Vous êtes maintenant connecté !';
      }
      else
      {
        $info['error'] = 'Mauvais identifiants';
      }
    }
    $data = array_merge($info, array('session' => $_SESSION));
    $this->twig->render('users/login', $data);
  }

  public function logout($key = '')
  {
    if(isLogged())
      $this->auth->logout($key);

    redirect(base_url('users/login'));
  }

  public function signup()
  {
    redirectIfLogged();
    $this->load->library('form_validation');
    $info = array('info' => $this->session->flashdata('info'));

    if($this->form_validation->run('signup'))
    {
      $user_id = $this->user->create($this->input->post(['username', 'email', 'password']));
      $this->auth->login($this->user->get($user_id), $this->user->getUserGroups($user_id));
      $info['success'] = 'Vous êtes maintenant inscrit et connecté =) !';
    }
    $data = array_merge($info, array('session' => $_SESSION));
    $this->twig->render('users/signup', $data);
  }
}
