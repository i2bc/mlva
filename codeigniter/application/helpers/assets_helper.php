<?php

function urlTitle($model, $title, $id)
{
	return base_url($model.'/voir/'.$id.'/'.old_url_title($title, '_'));
}


function old_url_title($str, $separator = '-', $lowercase = FALSE)
{
  if ($separator === 'dash')
  {
    $separator = '-';
  }
  elseif ($separator === 'underscore')
  {
    $separator = '_';
  }

  $q_separator = preg_quote($separator, '#');

  $trans = array(
    '&.+?;'			=> '',
    '[^a-z0-9 _-]'		=> '',
    '\s+'			=> $separator,
    '('.$q_separator.')+'	=> $separator
  );

  $str = strip_tags($str);
  foreach ($trans as $key => $val)
  {
    $str = preg_replace('#'.$key.'#i', $val, $str);
  }

  if ($lowercase === TRUE)
  {
    $str = strtolower($str);
  }

  return trim(trim($str, $separator));
}

/**
* Remplace les liens vidéos par des iframes
* @param  string $contenu La chaîne à parser
* @param  string $height
* @param  string $width
* @return string          la chaîne avec les urls remplacées
*/
function auto_iframe($contenu, $height='378', $width='620'){
    /*
     * 3 preg_replace pour YouTube, Vimeo et Dailymotion
     */
    $filtreDailymotion =	'/\[video\](http(s)?:\/\/www.dailymotion\.com\/video\/([a-zA-Z0-9]+))([a-zA-Z0-9\-_\?=&]*)/i';
    $filtreDaily       =  '/\[video\](http(s)?:\/\/dai\.ly\/([a-zA-Z0-9]+))/i';
    $filtreVimeo       =  '/\[video\](http(s)?:\/\/vimeo.com\/([0-9]+))/i';
    $filtreYoutube     = 	'/\[video\](http(s)?:\/\/www.youtube\.com\/watch\?([a-zA-Z0-9\-\_=&]*)v=([a-zA-Z0-9\-\_]+))/i';
    $filtreYoutu       =	'/\[video\](http(s)?:\/\/youtu\.be\/([a-zA-Z0-9\-\_]+))/i';


    $contenu = preg_replace($filtreYoutube, iframe_with_type('$4', 'YouTube', $height, $width), $contenu);
    $contenu = preg_replace($filtreYoutu, iframe_with_type('$3', 'YouTube', $height, $width), $contenu);
    $contenu = preg_replace($filtreVimeo, iframe_with_type('$3', 'Vimeo', $height, $width), $contenu);
    $contenu = preg_replace($filtreDailymotion, iframe_with_type('$3', 'Daily', $height, $width), $contenu);
    $contenu = preg_replace($filtreDaily, iframe_with_type('$3', 'Daily', $height, $width), $contenu);

    return $contenu;
}

/**
* Renvoit l'iframe correspondant au type de vidéo (Youtube, Vimeo, Dailymotion) envoyé
**/
function iframe_with_type($id_video, $type, $height='378', $width='620')
{

  switch ($type) {

    case 'YouTube':
      $iframe = iFrame($height, $width, 'http://www.youtube.com/embed/'.$id_video.'?rel=0');
      break;

    case 'Vimeo':
      $iframe = iFrame($height, $width, 'http://player.vimeo.com/video/'.$id_video);
      break;

    case 'Daily':
        $iframe = iFrame($height, $width, 'http://www.dailymotion.com/embed/video/'.$id_video);
      break;

    default:
      $iframe = $id_video;
      break;
  }
  return '<div class="embed-responsive embed-responsive-16by9">'.$iframe.'</div>';

}
function iFrame($height='378', $width='620', $src)
{
	return '<iframe  class="embed-responsive-item" width="'.$width.'" height="'.$height.'" src="'.$src.'" allowFullScreen></iframe>';
}

function vanillicon($pseudo ='', $size = 100)
{
	return 'https://vanillicon.com/'.md5($pseudo).'_'.$size.'.png';
}

function removeExcessiveSpaces($string)
{
	return preg_replace( '/\s+/', ' ', trim($string));
}
function removeAllSpaces($string)
{
	return preg_replace( '/\s+/', '', trim($string));
}
