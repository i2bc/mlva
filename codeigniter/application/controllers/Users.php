<?php
class Users extends CI_Controller {

  const NB_USERS_PER_PAGE = 4;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('Twig');
    $this->load->model('users_model', 'user');
	}
  public function index()
  {
    /*echo '<a href="'.base_url('users/login').'">Login</a><br>';
    echo '<a href="'.base_url('users/logout/'.$this->session->key).'">Déconnexion</a><br>';*/
    $this->last_registered();
  }
  public function signup()
  {
    redirectIfLogged();
    $this->load->library('form_validation');
    $info = array('info' => $this->session->flashdata('info'));

    if($this->form_validation->run('signup'))
    {
      $user_id = $this->user->create($this->input->post(['username', 'email', 'password']));
      $this->auth->login($this->user->get($user_id), $this->user->getGroups($user_id));
      $info['success'] = 'Vous êtes maintenant inscrit et connecté =) !';
    }
    $data = array_merge($info, array('session' => $_SESSION));
    $this->twig->render('users/signup', $data);
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
        $this->auth->login($this->user->get($user_id), $this->user->getGroups($user_id));
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

  protected function showUsers($page, $url, $orderBy='userId', $page_infos = array(), $where = array(), $order = 'desc')
  {
    $this->load->library('pagination');
    $count = $this->user->count($where);
    $this->pagination->initialize(arrayPagination(base_url() . $url, $count, self::NB_USERS_PER_PAGE));

    list($page, $start)  = getPageAndStart($page, self::NB_USERS_PER_PAGE);

    $data = array('session' => $_SESSION,
                  'count' => $count,
                  'users' => $this->user->groupConcatToArray($this->user->getAll(self::NB_USERS_PER_PAGE, $start, $orderBy, $where, $order)),
                  'pagination' => $this->pagination->create_links(),
                  'page_infos' => $page_infos
                  );
		$this->twig->render('users/users', $data);
  }

  public function alphabetic($page = 1)
	{
    $this->showUsers($page, '/users/alphabetic/', 'username', [], [], 'asc');
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
    $this->load->library('form_validation');
    $info = array('info' => $this->session->flashdata('info'));

    if($this->form_validation->run('edit_user'))
    {
      if (($password = $this->input->post('password')) && $this->input->post('password_confirm'))
      {
        $inputs = ['email' => $this->input->post('email'), 'password' => simpleHash($password)];
      }
      else
      {
        $inputs = ['email' => $this->input->post('email')];
      }
      if (checkRight('edit', 'users') && ($groups = $this->input->post('groups')))
      {
        $this->user->updateGroups($groups, $user_id);
        $info['info'] = 'Les groupes ont été mis à jour';
      }
      $this->user->update($inputs, ['id' => $user_id]);
      $info['success'] = 'Les informations ont bien été enregistrées';
    }
    $data = array(
      'session' => $_SESSION,
      'user' => $this->user->get($user_id),
      'user_groups' => $this->user->getGroups($user_id),
      'groups' => $this->user->getAllGroups()
    );
    $data = array_merge($info, $data);
    $this->twig->render('users/edit', $data);
  }
}
