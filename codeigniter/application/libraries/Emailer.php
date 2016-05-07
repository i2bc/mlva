<?php
/**
 * Emailer class to easily send emails (notifications or about account info)
 */
class Emailer
{
  var $CI;
  public $adminGroup = 1;

  public function __construct()
  {
    $this->CI =& get_instance();//save the CodeIgniter instance in order to access methods
    $this->CI->load->library('email');
  }

  /**
   * A small helper function to send an email
   */
  public function sendEmail($to, $subject, $message)
  {
    $this->CI->email->set_protocol('mail'); //if we use the smtp or mail() fn
    $this->CI->email->set_mailtype('html');
    $this->CI->email->from(SITE_NO_REPLY_EMAIL, SITE_NAME);
    $this->CI->email->to($to);
    $this->CI->email->subject($subject);
    $this->CI->email->message($message);
    if (!$this->CI->email->send())
    {
      setFlash('error', lang('email_error_send'));
      if (ENVIRONMENT == 'development' || ENVIRONMENT == 'testing')
      {
        show_error($this->CI->email->print_debugger());
      }
    }
  }

  public function sendWelcomeMessage($to, $username)
  {
    $subject = "Welcome to MLVABank";
    $message = '<html><h3>Welcome '.$username.' on MLVABank</h3>';
    $message.= '<p>Your account has been succesfully created</p>';
    $message.= '<h4><a href="'.base_url().'?fromSignupEmail=1"> Click here to get back to the website</a></h4></html>';
    $this->sendEmail($to, $subject, $message);
  }

  public function sendForgottenPassword($to, $username, $newPassword)
  {
    $subject = lang('email_new_password');
    $message = '<html><h3>'.lang('auth_password_reset').'</h3>';
    $message.= '<p>Your credentials: <br> Username: '.$username.'<br> Password: '.$newPassword.'</p>';
    $message.= '<h4><a href="'.base_url().'users/login">Back to the website</a></h4></html>';

    $this->sendEmail($to, $subject, $message);
  }

  public function notifyAdminDatabasePublic($database)
  {
    $users = $this->CI->user->getUsersOfGroup($this->adminGroup);
    $subject = 'New public database';
    $message = '<html><h3>A new database has been made public</h3>';
    $message.= '<p>The database: '.$database['name'].'<br> Owner: '.$database['creator_name'].'</p>';
    $message.= '<h4><a href="'.base_url('databases/view/$database['id']).'">View the database</a></h4></html>';
    foreach ($users as $user)
    {
      $this->sendEmail($user['email'], $subject, $message);
    }
  }
}
