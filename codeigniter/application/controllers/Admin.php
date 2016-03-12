<?php
class Admin extends CI_Controller {

  const NB_USERS_PER_PAGE = 20;
  const NB_GROUPS_PER_PAGE = 20;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('Twig');
    $this->load->model('users_model', 'user');
    checkRight('admin', '', true);//403 if not admin
	}

  public function index()
  {
    $data = array('session' => $_SESSION);
    $this->twig->render('admin/dashboard', $data);
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
                  'page_infos' => $page_infos,
                  'info' => $this->session->flashdata('info')
                  );
		$this->twig->render('admin/users', $data);
  }

  protected function showGroups($page, $url, $orderBy='id', $page_infos = array(), $where = array(), $order = 'asc')
  {
    $this->load->library('pagination');
    $count = $this->user->countGroups($where);
    $this->pagination->initialize(arrayPagination(base_url() . $url, $count, self::NB_GROUPS_PER_PAGE));

    list($page, $start)  = getPageAndStart($page, self::NB_GROUPS_PER_PAGE);

    $data = array('session' => $_SESSION,
                  'count' => $count,
                  'groups' => $this->user->getAllGroups(self::NB_GROUPS_PER_PAGE, $start, $orderBy, $where, $order),
                  'pagination' => $this->pagination->create_links(),
                  'page_infos' => $page_infos,
                  'info' => $this->session->flashdata('info')
                  );
		$this->twig->render('admin/groups', $data);
  }

  public function groups($page = 1)
  {
    checkRight('admin', '', true);
    $this->load->library('form_validation');

    list($orderBy, $order) = getOrder(['id', 'name', 'label']);

    $page_infos = array('title' => 'Groups List');
    $this->showGroups($page, '/admin/groups/', $orderBy, $page_infos, [], $order);
  }

  public function databases($page = 1)
  {
    checkRight('admin', '', true);
    list($orderBy, $order) = getOrder(['id', 'name', 'creator_name', 'created_at', 'last_update']);
    $where = [];
    $this->load->model('databases_model', 'database');

    $this->load->library('pagination');
    $count = $this->database->count();
    $this->pagination->initialize(arrayPagination(base_url('databases/'), $count, self::NB_GROUPS_PER_PAGE));

    list($page, $start)  = getPageAndStart($page, self::NB_GROUPS_PER_PAGE);

    $data = array('session' => $_SESSION,
                  'count' => $count,
                  'databases' => $this->database->getAllDatabases(self::NB_GROUPS_PER_PAGE, $start, $orderBy, $where, $order),
                  'pagination' => $this->pagination->create_links(),
                  'page_infos' => "",
                  'info' => $this->session->flashdata('info')
                  );
    $this->twig->render('admin/databases', $data);
  }

  public function users($page = 1)
  {
    checkRight('admin', '', true);
    $this->load->library('form_validation');

    list($orderBy, $order) = getOrder(['userId', 'username', 'email', 'last_login']);
    if($orderBy == 'id')
      $orderBy = 'userId'; //Ugly fix because of ambiguous database column
    $page_infos = array('title' => 'Users List');
    $this->showUsers($page, '/admin/users/', $orderBy, [], [], $order);
  }

  public function users_of_group($group_id = 0, $page = 1)
  {
    $group_id = str_replace('group_', '', $group_id);
    if (!($group_id = getIntOrOne($group_id)))
    {
      show_404();
    }
    checkRight('edit', 'user');

    $this->load->library('pagination');
    $count = $this->user->countOfGroup($group_id);
    $this->pagination->initialize(arrayPagination(base_url('admin/users_of_group/group_'.$group_id.'/'), $count, self::NB_USERS_PER_PAGE));

    list($page, $start) = getPageAndStart($page, self::NB_USERS_PER_PAGE);
    $groupName = getGroupName($group_id, $groups = $this->user->getAllGroups());

    $data = array('session' => $_SESSION,
                  'count' => $count,
                  'users' => $this->user->getUsersOfGroup($group_id, self::NB_USERS_PER_PAGE, $start),
                  'groups' => $groups,
                  'pagination' => $this->pagination->create_links(),
                  'groupName' => $groupName
                  );
		$this->twig->render('admin/users_of_group', $data);

  }
  public function edit_group($group_id = 0)
  {
    if (!($group_id = getIntOrZero($group_id)))
    {
      show_404();
    }
    checkRight('edit', 'group');

    $this->load->library('form_validation');
    $info = array('info' => $this->session->flashdata('info'));

    if($this->form_validation->run('group'))
    {
      $this->user->updateGroup($this->input->post(['name', 'permissions']), ['id' => $group_id]);
      $info['success'] = lang('auth_success_edit');
    }
    $data = array(
      'session' => $_SESSION,
      'group' => $this->user->getGroup($group_id)
    );
    $data = array_merge($info, $data);
    $this->twig->render('admin/group_edit', $data);
  }
  public function create_group()
  {
    checkRight('create', 'group');
    $this->load->library('form_validation');

    if($this->form_validation->run('group'))
    {
      $group_id = $this->user->createGroup($this->input->post(['name', 'permissions']));
      $this->session->set_flashdata('info', lang('auth_group_created'));
      redirect(base_url('admin/groups'));
    }
    $data = array('session' => $_SESSION);
    $this->twig->render('admin/group_create', $data);
  }

  public function deleteGroup($key, $group_id)
  {
    checkRight('delete', 'group', true);
    if($key == $this->session->userdata('key'))
    {
      if ($group_id == $this->user->getAdminGroupId())
      {
        show_error(lang('auth_delete_admin_group'), 403, lang('auth_error'));
      }
      $this->user->deleteGroup($group_id);
      $this->session->set_flashdata('info', 'The group '.$group_id.' has been deleted');
      redirect(base_url('admin/groups'));
    }
    else
    {
      show_403();
    }
  }

  public function deleteUser($key, $user_id)
  {
    checkRight('delete', 'user', true);
    if($key == $this->session->userdata('key'))
    {
      //Security do avoid admin deletion
      if (inGroup($this->user->getAdminGroupId(), $current_user = false, $this->user->getUserGroups($user_id)))
      {
        show_error(lang('auth_delete_admin'), 403, lang('auth_error'));
      }
      $this->user->deleteUser($user_id);
      $this->session->set_flashdata('info', 'The user '.$user_id.' is deleted');
      redirect(base_url('admin/users'));
    }
    else
    {
      show_403();
    }
  }
}
