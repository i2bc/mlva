<?php
/**
 * Simple Class to represent a cluster
 */
class Cluster
{
  public $id, $data, $size, $height;
  function __construct($id, $data, $size, $height)
  {
    $this->id = $id;
    $this->data = $data;
    $this->size = $size;
    $this->height = $height;
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
  $min = false;
  $i_min = 0;
  $j_min = 0;
  $n = count($clusters);
  foreach($clusters as $i => $cluster1)
  {
    foreach($clusters as $j => $cluster2)
    {
      if($j > $i)
      {
        $tmp = $dist[$j][$i];
        if(!$min)
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
  //Compute new distance values and insert them
  array_push($dist, []);
  for ($m=0; $m < $k->id; $m++)
  {
    array_push($dist[$k->id], 0);
  }
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

function test()
{
  $species = [ "A", "B", "C", "D", "E" ];
  $matr = [ [ 0., 4., 5., 5., 2. ],
           [ 4., 0., 3., 5., 6. ],
           [ 5., 3., 0., 2., 5. ],
           [ 5., 5., 2., 0., 3. ],
           [ 2., 6., 5., 3., 0. ] ];
  $clu = makeClusters($species);
  $tree = reGroup($clu, $matr);
  ob_start();
  printNewickTree($tree, $tree->height);
  $newickTree = ob_get_contents();
  ob_end_clean();
}
function test2()
{
  $species = [ "Turtle", "Man", "Tuna", "Chicken",
              "Moth", "Monkey", "Dog" ];
  $matr = [ [], [ 19 ], [ 27, 31 ],
           [ 8, 18, 26 ], [ 33, 36, 41, 31 ],
           [ 18, 1, 32, 17, 35 ], [ 13, 13, 29, 14, 28, 12 ] ];
  $clu = makeClusters($species);
  $tree = reGroup($clu, $matr);
  printNewickTree($tree, $tree->height);
}

function getNewickTree($keys, $matrixDistance)
{
  $baseClusters = makeClusters($keys);
  $tree = reGroup($baseClusters, $matrixDistance);
  ob_start();
  printNewickTree($tree, $tree->height);
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
    printNewickTree($tree->data[0], $tree->height);
    echo ",";
    printNewickTree($tree->data[1], $tree->height);
    echo '):'.(number_format($len - $tree->height, 2));// ou max($len - $tree->height,0)
  }
  else
  {
    //it's a leaf
    echo $tree->data[0].':'.number_format($len, 2);
  }
}
