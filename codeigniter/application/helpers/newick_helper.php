<?php
/**
 * This is an helper for Unweighted Pair-Group Method with Arithmetic mean (UPGMA) algorithm
 * It is a clustering method to build a newick tree from a distance matrix
 * More info: http://www.southampton.ac.uk/~re1u06/teaching/upgma/
 * (this code is an adaptation of the code below)
 * Python implementation: http://wwwabi.snv.jussieu.fr/jompo/Public/moduleISV/Documents/GE/upgma.html
 */

/**
 * Simple Class to represent a cluster
 */
class Cluster
{
  public $id, $children, $size, $depth, $parentIndex;
  function __construct($id, $children, $size, $depth, $parentIndex)
  {
    $this->id = $id;
    $this->children = $children;
    $this->size = $size;
    $this->depth = $depth;
    $this->parentIndex = $parentIndex;
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
    $cluster = new Cluster($id, $specimen, 1, 0, -1);
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
  //If it is the same distance and there is a parent, add it to the parent cluster
  if($dij == 0 && ($ci->parentIndex != -1 || $cj->parentIndex != -1))
  {
    if($ci->parentIndex != -1)
    {
      $parent = $clusters[$ci->parentIndex];
      $parent->children[] = $cj;
    }
    else
    {
      $parent = $clusters[$cj->parentIndex];
      $parent->children[] = $ci;
    }
    $parent->size++;
    $k = $parent;
  }
  else
  {
    // Create a new cluster
    $parentIndex = max(array_keys($clusters))+1;
    $ci->parentIndex = $parentIndex;
    $cj->parentIndex = $parentIndex;
    $k = new Cluster($parentIndex, [$ci, $cj], $ci->size+$cj->size, $dij/2., $parentIndex);
  }
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
  //dd($newickTree);
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
    //We go through all the children
    for ($i=0; $i < count($tree->children); $i++)
    {
      printNewickTree($tree->children[$i], $tree->depth);
      if($i < count($tree->children)-1)
        echo ",";
    }
    echo ")";
    echo ':'.(number_format($len - $tree->depth, 2));
  }
  else
  {
    //it's a leaf
    echo $tree->children[0].':'.number_format($len, 2);
  }
}
