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
'edit_user_infos' => array(
                      array(
                              'field' => 'last_name',
                              'label' => 'LastName',
                              'rules' => 'trim|min_length[2]|max_length[50]|alpha_dash'
                           ),
                      array(
                              'field' => 'first_name',
                              'label' => 'FirstName',
                              'rules' => 'trim|min_length[2]|max_length[30]|alpha_dash'
                           ),
                     array(
                              'field' => 'website',
                              'label' => 'Website',
                              'rules' => 'trim|valid_url'
                           ),
                     array(
                              'field' => 'bio',
                              'label' => 'About You',
                              'rules' => 'trim|max_length[1000]'
                           ),
                         ),
        'group' => array(
                         array(
                                  'field' => 'name',
                                  'label' => 'Name',
                                  'rules' => 'removeAllSpaces|required|max_length[255]|alpha_dash_spaces'
                               ),
                         array(
                                  'field' => 'description',
                                  'label' => 'Group Description',
                                  'rules' => 'trim|max_length[1000]'
                               ),
                          array(
                                  'field' => 'permissions',
                                  'label' => 'Permissions',
                                  'rules' => 'trim|isJsonValid'
                               )
                          ),
        'login' => array(
                          array(
                                  'field' => 'username',
                                  'label' => 'Username',
                                  'rules' => 'trim|required|min_length[3]|max_length[18]|alpha_dash'
                               ),
                          array(
                                  'field' => 'password',
                                  'label' => 'Password',
                                  'rules' => 'trim|required'
                               )
                         ),

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// DATABASES
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    // = Create ==========
    'csv-create1' => array(
                       array(
                               'field' => 'csvMode',
                               'label' => 'CSV Mode',
                               'rules' => 'trim'
                            )
                         ),
    'csv-create2' => array(
                      array(
                              'field' => 'basename',
                              'label' => 'Database name',
                              'rules' => 'trim|required|max_length[255]|alpha_dash_spaces'
                           ),
                      array(
                              'field' => 'name',
                              'label' => 'Strain key/name',
                              'rules' => 'trim|required|max_length[255]|alpha_dash_spaces'
                           ),
                         ),

    // = Import ==========
    'csv-import2' => array(

						),

    // = Edit ==========
     'edit_db' => array(
                       array(
                               'field' => 'name',
                               'label' => 'Database name',
                               'rules' => 'trim|required|max_length[255]|alpha_dash_spaces'
                            ),
                       array(
                               'field' => 'group',
                               'label' => 'Group',
                               'rules' => 'trim|required|integer'
                            ),
                        array(
                                 'field' => 'website',
                                 'label' => 'Website',
                                 'rules' => 'trim|valid_url'
                              ),
                        array(
                                 'field' => 'description',
                                 'label' => 'Database Description',
                                 'rules' => 'trim|max_length[1000]'
                              ),
                          ),

    // = Edit ==========
     'export_db' => array(
                       array(
                               'field' => 'csvMode',
                               'label' => 'CSV Mode',
                               'rules' => 'required'
                            )
                          ),

    // = Edit Panel ==========
     'edit_panel' => array(
                       array(
                               'field' => 'name',
                               'label' => 'Panel name',
                               'rules' => 'trim|required|max_length[255]|alpha_dash_spaces'
                            ),
                          ),

    // = Query ==========
     'query' => array(
                       // array(
                               // 'field' => 'data',
                               // 'label' => 'Data',
                               // 'rules' => 'required'
                            // ),
                       array(
                               'field' => 'max_dist',
                               'label' => 'Maximal distance',
                               'rules' => 'required|is_natural'
                            ),
                          ),

);
