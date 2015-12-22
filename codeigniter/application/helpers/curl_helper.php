<?php
// Curl helper function to make a get request
function curl_get($url)
{
  if (function_exists('curl_version'))//If Curl is available
  {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    $response = curl_exec($curl);
    curl_close($curl);
  }
  else
  {
    $response = file_get_contents($url);
  }

  return $response;
}

// Return the status code for an http request on the page $url
function get_http_response_code($url)
{
    return substr(get_headers($url)[0], 9, 3);
}

function getAndSave($url, $destinationPath)
{
  if(function_exists('curl_version'))
  {
    $curl = curl_init($url);
    $file = fopen($destinationPath, 'w+b');
    curl_setopt($curl, CURLOPT_FILE, $file);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_exec($curl);
    curl_close($curl);
    fclose($file);
  }
  else
  {
    file_put_contents($destinationPath, file_get_contents($url));
  }
}
