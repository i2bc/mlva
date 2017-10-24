<?php

function getJSON ($key = null) {
  $data = json_decode(file_get_contents('php://input'), true);
  if ($key == null) {
    return $data;
  } else {
    return isset($data[$key]) ? $data[$key] : NULL;
  }
}

function getBlockJSON ($handle) {
  $str = "";
  $depth = 0;
  while (($c = fgetc($handle)) !== false) {
    if ($c == "{") $depth += 1;
    if ($depth > 0) $str .= "$c";
    if ($c == "}") {
      $depth -= 1;
      if ($depth == 0) return json_decode($str, true);
    }
  }
  return false;
}
