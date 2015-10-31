<?php
$config =
array(

       'signup' => array(
                          array(
                                  'field' => 'username',
                                  'label' => 'Username',
                                  'rules' => 'trim|required|min_length[3]|max_length[18]|alpha_dash|is_unique[users.username]'
                               ),
                          array(
                                  'field' => 'password',
                                  'label' => 'Password',
                                  'rules' => 'trim|required|min_length[4]|max_length[255]'
                               ),
                          array(
                                  'field' => 'password_confirm',
                                  'label' => 'Confirmation du mot de passe',
                                  'rules' => 'trim|required|min_length[4]|max_length[255]|matches[password]'
                               ),
                          array(
                                  'field' => 'email',
                                  'label' => 'Email',
                                  'rules' => 'trim|required|valid_email|is_unique[users.email]'
                               ),
                          array(
                                  'field' => 'g-recaptcha-response',
                                  'label' => 'Anti-bot image (Captcha)',
                                  'rules' => 'trim|required|isCaptchaValid'
                               )
                          ),
        'edit_user' => array(
                          array(
                                  'field' => 'password',
                                  'label' => 'Password',
                                  'rules' => 'trim|min_length[4]|max_length[255]|matches[password_confirm]'
                               ),
                          array(
                                  'field' => 'password_confirm',
                                  'label' => 'Password Confirmation',
                                  'rules' => 'trim|min_length[4]|max_length[255]|matches[password]'
                               ),
                          array(
                                  'field' => 'email',
                                  'label' => 'Email',
                                  'rules' => 'trim|required|valid_email'
                               )
                          ),
        'group' => array(
                          array(
                                  'field' => 'name',
                                  'label' => 'Name',
                                  'rules' => 'removeAllSpaces|required|max_length[255]|regex_match[/^([a-z0-9 _àèéù-])+$/i]'
                               ),
                          array(
                                  'field' => 'permissions',
                                  'label' => 'Permissions',
                                  'rules' => 'trim|required|isJsonValid'
                               )
                          ),

        'login' => array(
                          array(
                                  'field' => 'username',
                                  'label' => 'Username',
                                  'rules' => 'trim|required|min_length[2]|alpha_numeric'
                               ),
                          array(
                                  'field' => 'password',
                                  'label' => 'Password',
                                  'rules' => 'trim|required'
                               )
                          )
);
