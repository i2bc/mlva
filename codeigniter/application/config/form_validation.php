<?php
$config =
array(
 'signup' => array(
                    array(
                            'field' => 'pseudo',
                            'label' => 'Pseudo',
                            'rules' => 'trim|required|min_length[4]|max_length[18]|alpha_numeric'
                         ),
                    array(
                            'field' => 'pass',
                            'label' => 'Mot de passe',
                            'rules' => 'trim|required'
                         ),
                    array(
                            'field' => 'pass2',
                            'label' => 'Confirmation du mot de passe',
                            'rules' => 'trim|required|min_length[4]|max_length[32]|matches[pass]'
                         ),
                    array(
                            'field' => 'email',
                            'label' => 'Email',
                            'rules' => 'trim|required|valid_email'
                         ),
                    array(
                            'field' => 'recaptcha_response_field',
                            'label' => 'Captcha (Image)',
                            'rules' => 'trim|required|callback_check_captcha'
                         )
                    ),
  'login' => array(
                    array(
                            'field' => 'pseudo',
                            'label' => 'Pseudo',
                            'rules' => 'trim|required|min_length[4]|alpha_numeric'
                         ),
                    array(
                            'field' => 'pass',
                            'label' => 'Password',
                            'rules' => 'trim|required'
                         )
                    )
);
