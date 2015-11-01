<?php
$config =
array(

       'signup' => array(
                          array(
                                  'field' => 'username',
                                  'label' => 'Pseudo',
                                  'rules' => 'trim|required|min_length[3]|max_length[18]|alpha_dash|is_unique[users.username]'
                               ),
                          array(
                                  'field' => 'password',
                                  'label' => 'Mot de passe',
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
                                  'label' => 'Image anti-robots (Captcha)',
                                  'rules' => 'trim|required|isCaptchaValid'
                               )
                          ),
        'edit_user' => array(
                          array(
                                  'field' => 'password',
                                  'label' => 'Mot de passe',
                                  'rules' => 'trim|min_length[4]|max_length[255]|matches[password_confirm]'
                               ),
                          array(
                                  'field' => 'password_confirm',
                                  'label' => 'Confirmation du mot de passe',
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
                                  'label' => 'Nom',
                                  'rules' => 'removeAllSpaces|required|max_length[255]|regex_match[/^([a-z0-9 _àèéù-])+$/i]'
                               ),
                          array(
                                  'field' => 'permissions',
                                  'label' => 'Permissions',
                                  'rules' => 'trim|required|isJsonValid'
                               )
                          ),
      'edit_user_infos' => array(
                          array(
                                  'field' => 'nom',
                                  'label' => 'Nom',
                                  'rules' => 'trim|min_length[3]|max_length[30]|alpha'
                               ),
                          array(
                                  'field' => 'prenom',
                                  'label' => 'Prenom',
                                  'rules' => 'trim|min_length[3]|max_length[30]|alpha'
                               ),
                          array(
                                  'field' => 'date',
                                  'label' => 'Date de naissance',
                                  'rules' => 'trim|valid_age'
                               ),
                          array(
                                  'field' => 'vimeo',
                                  'label' => 'Profil Vimeo',
                                  'rules' => 'trim|valid_vimeo'
                               ),
                          array(
                                  'field' => 'youtube',
                                  'label' => 'Profil You Tube',
                                  'rules' => 'trim|valid_youtube'
                               ),
                          array(
                                  'field' => 'daily',
                                  'label' => 'Profil Dailymotion',
                                  'rules' => 'trim|valid_daily'
                               ),
                          array(
                                  'field' => 'twitter',
                                  'label' => 'Profil Twitter',
                                  'rules' => 'trim|valid_twitter'
                               ),
                         array(
                                  'field' => 'facebook',
                                  'label' => 'Profil Facebook',
                                  'rules' => 'trim|valid_facebook'
                               ),
                         array(
                                  'field' => 'google',
                                  'label' => 'Profil Google+',
                                  'rules' => 'trim|valid_google'
                               ),
                         array(
                                  'field' => 'site_web',
                                  'label' => 'Site internet',
                                  'rules' => 'trim|valid_lien'
                               ),
                         array(
                                  'field' => 'bio',
                                  'label' => 'A propos de vous',
                                  'rules' => 'trim|max_length[900]'
                               ),
                          ),
        'login' => array(
                          array(
                                  'field' => 'username',
                                  'label' => 'Pseudo',
                                  'rules' => 'trim|required|min_length[2]|alpha_numeric'
                               ),
                          array(
                                  'field' => 'password',
                                  'label' => 'Mot de passe',
                                  'rules' => 'trim|required'
                               )
                          ),
       'email' => array(
                          array(
                                  'field' => 'emailaddress',
                                  'label' => 'EmailAddress',
                                  'rules' => 'required|valid_email'
                               ),
                          array(
                                  'field' => 'name',
                                  'label' => 'Name',
                                  'rules' => 'required|alpha'
                               ),
                          array(
                                  'field' => 'title',
                                  'label' => 'Title',
                                  'rules' => 'required'
                               ),
                          array(
                                  'field' => 'message',
                                  'label' => 'MessageBody',
                                  'rules' => 'required'
                               )
                          ),
         'com' => array(
                          array(
                                  'field' => 'pseudo',
                                  'label' => 'Pseudo',
                                  'rules' => 'trim|required|min_length[4]|max_length[20]|alpha_numeric'
                               ),
                          array(
                                  'field' => 'email',
                                  'label' => 'Email',
                                  'rules' => 'trim|required|valid_email'
                               ),
                          array(
                                  'field' => 'com',
                                  'label' => 'Commentaire',
                                  'rules' => 'trim|required|min_length[3]|max_length[1500]|htmlspecialchars'
                               ),
                          array(
                                  'field' => 'video_id',
                                  'label' => 'Video id',
                                  'rules' => 'trim|required'
                               )
                          ),
         'com_news' => array(
                          array(
                                  'field' => 'pseudo',
                                  'label' => 'Pseudo',
                                  'rules' => 'trim|required|min_length[4]|max_length[20]|alpha_numeric'
                               ),
                          array(
                                  'field' => 'email',
                                  'label' => 'Email',
                                  'rules' => 'trim|required|valid_email'
                               ),
                          array(
                                  'field' => 'com',
                                  'label' => 'Commentaire',
                                  'rules' => 'trim|required|min_length[4]|max_length[1500]|htmlspecialchars'
                               ),
                          array(
                                  'field' => 'news_id',
                                  'label' => 'News Id',
                                  'rules' => 'trim|required'
                               )
                          ),
          'edit_com' => array(
                          array(
                                  'field' => 'com',
                                  'label' => 'Commentaire',
                                  'rules' => 'trim|required|min_length[4]|max_length[1500]|htmlspecialchars'
                               )
                          ),

         'envoi' => array(
                          array(
                                  'field' => 'titre',
                                  'label' => 'Titre',
                                  'rules' => 'trim|required|min_length[3]|max_length[150]|alpha_dash'
                               ),
                          array(
                                  'field' => 'lien',
                                  'label' => 'Lien',
                                  'rules' => 'required|valid_lien'
                              ),
                          array(
                                  'field' => 'desc',
                                  'label' => 'Description',
                                  'rules' => 'trim|required|min_length[3]|max_length[3000]'
                               ),
                          array(
                                  'field' => 'cat',
                                  'label' => 'Categorie',
                                  'rules' => 'trim|required|is_natural'
                               ),
                          array(
                                  'field' => 'id_video',
                                  'label' => 'Id video',
                                  'rules' => 'trim|required|min_length[2]|max_length[100]'
                               ),
                          array(
                                  'field' => 'image',
                                  'label' => 'Image',
                                  'rules' => 'trim|required|valid_lien_image'
                               ),
                          array(
                                  'field' => 'auteur',
                                  'label' => 'Auteur',
                                  'rules' => 'trim|required|min_length[3]|max_length[100]|alpha_numeric'
                               ),
                          array(
                                  'field' => 'tags',
                                  'label' => 'Tags',
                                  'rules' => 'trim2|required|alpha_dash|min_tags[3]|max_tags[25]|tag_simplify'
                               ),
                           ),
         'video_edit' => array(
                          array(
                                  'field' => 'titre',
                                  'label' => 'Titre',
                                  'rules' => 'trim|required|min_length[3]|max_length[150]|alpha_dash'
                               ),
                          array(
                                  'field' => 'id',
                                  'label' => 'Id',
                                  'rules' => 'trim|required'
                               ),
                          array(
                                  'field' => 'lien',
                                  'label' => 'Lien',
                                  'rules' => 'required|valid_lien'
                              ),
                          array(
                                  'field' => 'desc',
                                  'label' => 'Description',
                                  'rules' => 'trim|required|min_length[3]|max_length[3000]'
                               ),
                          array(
                                  'field' => 'cat',
                                  'label' => 'Categorie',
                                  'rules' => 'trim|required|is_natural'
                               ),
                          array(
                                  'field' => 'id_video',
                                  'label' => 'Id video',
                                  'rules' => 'trim|required|min_length[2]|max_length[100]'
                               ),
                          array(
                                  'field' => 'image',
                                  'label' => 'Image',
                                  'rules' => 'trim|required|valid_lien_image'
                               ),
                          array(
                                  'field' => 'tags',
                                  'label' => 'Tags',
                                  'rules' => 'trim2|required|alpha_dash|min_tags[3]|max_tags[25]|tag_simplify'
                               ),
                          array(
                                  'field' => 'auteur',
                                  'label' => 'Auteur',
                                  'rules' => 'trim|required|min_length[3]|max_length[100]|alpha_numeric'
                               )
                          ),

        'news_edit' => array(
                          array(
                                  'field' => 'titre',
                                  'label' => 'Titre',
                                  'rules' => 'trim|required|min_length[3]|max_length[250]|alpha_dash'
                               ),
                          array(
                                  'field' => 'intro',
                                  'label' => 'Description',
                                  'rules' => 'trim|required|min_length[10]|max_length[2000]'
                               ),
                          array(
                                  'field' => 'cat',
                                  'label' => 'Categorie',
                                  'rules' => 'trim|required|is_natural'
                               ),
                          array(
                                  'field' => 'lien_video',
                                  'label' => 'Url de la video',
                                  'rules' => 'valid_lien'
                              ),
                          array(
                                  'field' => 'contenu',
                                  'label' => 'Contenu',
                                  'rules' => 'trim|required|min_length[30]|max_length[300000]'
                               ),
                          array(
                                  'field' => 'image',
                                  'label' => 'Image',
                                  'rules' => 'trim|valid_lien_image'
                               ),
                         array(
                                  'field' => 'news_id',
                                  'label' => 'News Id',
                                  'rules' => 'trim|required|is_natural'
                               ),
                          array(
                                  'field' => 'auteur',
                                  'label' => 'Auteur',
                                  'rules' => 'trim|required|min_length[3]|max_length[100]'
                               ),
                          array(
                                  'field' => 'tags',
                                  'label' => 'Tags',
                                  'rules' => 'trim2|required|alpha_dash|min_tags[3]|max_tags[25]|tag_simplify'
                               ),
                          array(
                                  'field' => 'user_id',
                                  'label' => 'Id',
                                  'rules' => 'trim|required|is_natural'
                               ),                          ),
      'news' => array(
                          array(
                                  'field' => 'titre',
                                  'label' => 'Titre',
                                  'rules' => 'trim|required|min_length[3]|max_length[250]|alpha_dash'
                               ),
                          array(
                                  'field' => 'intro',
                                  'label' => 'Description',
                                  'rules' => 'trim|required|min_length[10]|max_length[2000]'
                               ),
                          array(
                                  'field' => 'cat',
                                  'label' => 'Categorie',
                                  'rules' => 'trim|required|is_natural'
                               ),
                          array(
                                  'field' => 'lien_video',
                                  'label' => 'Url de la video',
                                  'rules' => 'valid_lien'
                              ),
                          array(
                                  'field' => 'contenu',
                                  'label' => 'Contenu',
                                  'rules' => 'required|min_length[30]|max_length[300000]'
                               ),
                          array(
                                  'field' => 'image',
                                  'label' => 'Image',
                                  'rules' => 'trim|valid_lien_image'
                               ),
                          array(
                                  'field' => 'auteur',
                                  'label' => 'Auteur',
                                  'rules' => 'trim|required|min_length[3]|max_length[100]'
                               ),
                          array(
                                  'field' => 'tags',
                                  'label' => 'Tags',
                                  'rules' => 'trim2|required|alpha_dash|min_tags[3]|max_tags[25]|tag_simplify'
                               ),
                        ),
		
		// = IMPORT ==========
        "csv-create" => array(
                          array(
                                  'field' => 'basename',
                                  'label' => 'Database name',
                                  'rules' => 'trim|required'
                               ),
                          array(
                                  'field' => 'name',
                                  'label' => 'Strain key/name',
                                  'rules' => 'trim|required'
                               )
                          )
);
