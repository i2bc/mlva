<?php

/**
 * Calculate the Hamming distance bewteen two strains
 * @param $strainReference the reference strain (an associtive array of its markers we need to consider)
 * @param $firstStrain the first strain (array)
 * @param $secondStrain the second strain
 */
function hammingDistance($strainReference, $firstStrain, $secondStrain)
{
  $dist = 0;
  foreach ($strainReference as $key => $value)
  {
    //If we should consider the current marker in the distance calculus
    if(!in_array($value, ["", -1]))
    {
      if(array_key_exists($key, $firstStrain) && array_key_exists($key, $secondStrain))
      {
        if ($firstStrain[$key] != $secondStrain[$key])
        {
          $dist += 1;
        }
      }
    }
  }
  return $dist;
}

/**
 * Fonction of comparison to sort the strains
 * 1. Sort by hamming distance to reference
 * 2. If equal sort by alphabetical order
 */
function compareStrainByDistance($strainA, $strainB)
{
  // Same hamming distance to reference
  if ($strainA['dist_to_ref'] == $strainB['dist_to_ref'])
  {
    //sort by alphetical order
    if($strainA['name'] != $strainB['name'])
    {
      return ($strainA['name'] < $strainB['name']) ? -1 : 1;
    }
    // in the other case, they are equal
    return 0;
  }
  return ($strainA['dist_to_ref'] < $strainB['dist_to_ref']) ? -1 : 1;
}

/**
 * Compute the distance matrix (Lower-left matrix) between strains
 * @param a sorted strains array (by distance to ref and by name)
 * @return an array [$keys, $matrix]
 */
function computeMatrixDistance($reference, $strains)
{
  $matrixDistance = [];
  $n = count($strains);
  $i = 0;
  $j = 0;
  $keys = [];
  // add the queried strain
  $queriedStrain = ['name'=>"Queried Strain", 'dist_to_ref' =>0, 'data'=>$reference];
  array_push($strains, $queriedStrain);
  foreach($strains as $firstStrain)
  {
    $j = 0;
    array_push($keys, [$firstStrain['name'], $firstStrain['dist_to_ref']]);
    foreach ($strains as $secondStrain)
    {
      if($j < $i)
      {
        $matrixDistance[$i][$j] = hammingDistance($reference, $firstStrain['data'], $secondStrain['data']);
      }
      else
      {
        $matrixDistance[$i][$j] = '';
      }
      $j++;
    }
    $i++;
  }
  return [$keys, $matrixDistance];
}

/**
 * Create the html table to print the distance matrix
 * It fills the matrix if some keys don't exist
 * @param $matrixAndKeys an array containing the keys, their distance to reference and the distance matrix
 */
function printMatrixDistance($matrixAndKeys)
{
  list($keys, $matrix) = $matrixAndKeys;
  $n = count($matrix);//Dimension of our square matrix
  $CI =& get_instance();//save the CodeIgniter instance in order to access methods
  $template = array('table_open' => '<table class="table table-striped table-bordered text-center">');

  $CI->load->library('table');
  $CI->table->set_template($template);
  $heading = array_column($keys, 0);
  array_unshift($heading, "key (distance)");// Add an empty entry at the beginning
  $CI->table->set_heading($heading);

  for ($i=0; $i < $n; $i++)
  {
    for ($j=0; $j < $n; $j++)
    {
      if (!array_key_exists($j, $matrix[$i]))
      {
        $matrix[$i][$j] = "";//Fill the matrix to avoid errors
      }
    }
    $row = $matrix[$i];
    array_unshift($row, '<b>'.$keys[$i][0].'</b>');//Add the current row number
    $CI->table->add_row($row);
  }
  return $CI->table->generate();
}

/**
 * Export a distance matrix to the MEGA format
 * See http://www.megasoftware.net/docs
 */
function exportToMEGAFormat($matrixAndKeys, $title="MLVA_Export", $description="")
{
  list($keys, $matrix) = $matrixAndKeys;
  $nbStrains = count($keys);
  $title = url_title($title);
  header("Content-Type: application/txt");
  header("Content-disposition: attachment; filename=".$title.".meg");
  echo "#mega\r\n[if file does not open with MEGA, check MEGA manual for taxa names rules.]\r\n";
  echo "!Title: $title;\r\n!Format DataType=Distance DataFormat=LowerLeft NTaxa=$nbStrains;\r\n";
  echo "!Description\r\n$description\r\n;\r\n\r\n";
  // replace incompatibles characters
  // And print strains
  for ($i = 0; $i < $nbStrains; $i++)
  {
    echo '#'.url_title($keys[$i][0]).PHP_EOL;
  }
  // Print the matrix
  for ($i = 0; $i < $nbStrains; $i++)
  {
    echo implode(" ", $matrix[$i]).PHP_EOL;
  }
}
