<?php
/**
 * Return true if the string contains only alpha_numeric, dashes and spaces
 */
function alpha_dash_spaces($str)
{
  return preg_match("/^[a-z0-9 _àèéù-]+$/i", $str) ? true : false;
}
