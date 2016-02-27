<?php
/**
 * This is an helper for Unweighted Pair-Group Method with Arithmetic mean (UPGMA) algorithm
 * It is a clustering method
 * More info: http://www.southampton.ac.uk/~re1u06/teaching/upgma/
 * (this code is an adaptation of the code below)
 * Python implementation: http://wwwabi.snv.jussieu.fr/jompo/Public/moduleISV/Documents/GE/upgma.html
 */

/**
 * Simple Class to represent a cluster
 */
class Cluster
{
  public $id, $data, $size, $depth;
  function __construct($id, $data, $size, $depth)
  {
    $this->id = $id;
    $this->data = $data;
    $this->size = $size;
    $this->depth = $depth;
  }
}

/**
 * Create the array of clusters
 */
function makeClusters($species)
{
  $clusters = [];
  $id = 0;
  foreach ($species as $specimen)
  {
    $cluster = new Cluster($id, $specimen, 1, 0);
    $clusters[$id] = $cluster;
    $id++;
  }
  return $clusters;
}

/**
 * Find the minimum distance
 */
function findMin($clusters, $dist)
{
  $min = -1;
  $i_min = 0;
  $j_min = 0;
  foreach($clusters as $i => $cluster1)
  {
    foreach($clusters as $j => $cluster2)
    {
      if($j > $i)
      {
        $tmp = $dist[$j][$i];
        if($min < 0)
          $min = $tmp;
        if($tmp <= $min)
        {
          $i_min = $i;
          $j_min = $j;
          $min = $tmp;
        }
      }
    }
  }
  return [$i_min, $j_min, $min];
}

/**
 * Regroup the different cluster
 */
function reGroup($clusters, $dist)
{
  list($i, $j, $dij) = findMin($clusters, $dist);
  $ci = $clusters[$i];
  $cj = $clusters[$j];
  // Create a new cluster
  $k = new Cluster(max(array_keys($clusters))+1, [$ci, $cj], $ci->size+$cj->size, $dij/2.);
  // Remove clusters
  unset($clusters[$i], $clusters[$j]);
  //Initialize the new entry in the distance matrix for the new cluster
  array_push($dist, []);
  for ($m=0; $m < $k->id; $m++)
  {
    array_push($dist[$k->id], 0);
  }
  //Compute new distance values and insert them
  foreach($clusters as $l => $cluster)
  {
    $dil = $dist[max($i, $l)][min($i, $l)];
    $djl = $dist[max($j, $l)][min($j, $l)];
    $dkl = ($dil * $ci->size + $djl * $cj->size) / floatval($ci->size + $cj->size);
    $dist[$k->id][$l] = $dkl;
  }
  //Insert the new cluster
  $clusters[$k->id] = $k;
  if(count($clusters)==1)
  {
    return array_values($clusters)[0];
  }
  else
  {
    return reGroup($clusters, $dist);
  }

}

function getNewickTree($keys, $matrixDistance)
{
  $baseClusters = makeClusters($keys);
  $tree = reGroup($baseClusters, $matrixDistance);
  ob_start();
  printNewickTree($tree, $tree->depth);
  $newickTree = ob_get_contents();
  ob_end_clean();
  return $newickTree.';';
}

/**
 * Return a tree to the Newick format
 */
function printNewickTree($tree, $len)
{
  if($tree->size > 1)
  {
    //it's an internal node
    echo "(";
    printNewickTree($tree->data[0], $tree->depth);
    echo ",";
    printNewickTree($tree->data[1], $tree->depth);
    echo '):'.(number_format($len - $tree->depth, 2));
  }
  else
  {
    //it's a leaf
    echo $tree->data[0].':'.number_format($len, 2);
  }
}
