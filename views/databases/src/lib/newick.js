/**
 * This is an helper for Unweighted Pair-Group Method with Arithmetic mean (UPGMA) algorithm
 * It is a clustering method to build a newick tree from a distance matrix
 * More info: http://www.southampton.ac.uk/~re1u06/teaching/upgma/
 * (this code is an adaptation of the code below)
 * Python implementation: http://wwwabi.snv.jussieu.fr/jompo/Public/moduleISV/Documents/GE/upgma.html
 */

import { distanceGeno } from './query'

function dist (cluster1, cluster2) {
  let d = 0
  for (let strain1 of cluster1.strains) {
    for (let strain2 of cluster2.strains) {
      d += distanceGeno(strain1.data, strain2.data)
    }
  }
  return d / (cluster1.size + cluster2.size)
}

// Simple Class to represent a cluster
class Cluster {
  constructor (strain = null, depth = 0) {
    this.strains = strain ? [strain] : []
    this.size = strain ? 1 : 0
    this.depth = depth
    this.children = []
    this.parent = null
  }

  addChild (cluster) {
    this.children.push(cluster)
    this.strains = this.strains.concat(cluster.strains)
    this.size += cluster.size
    cluster.parent = this
  }
}

// Create the array of clusters
function makeClusters (species) {
  const clusters = []
  for (let specimen of species) {
    clusters.push(new Cluster(specimen))
  }
  return clusters
}

// Find the minimum distance
function findMin (clusters) {
  let min = 1e4
  let iMin = 0
  let jMin = 0
  for (let i = 0; i < clusters.length - 1; i++) {
    for (let j = i + 1; j < clusters.length; j++) {
      let d = dist(clusters[i], clusters[j])
      if (d <= min) {
        iMin = i
        jMin = j
        min = d
      }
    }
  }
  return [iMin, jMin, min]
}

// Regroup the different cluster
function reGroup (clusters) {
  let [i, j, d] = findMin(clusters)
  let cluster1 = clusters[i]
  let cluster2 = clusters[j]
  let parent
  if (d === 0 && (cluster1.parent || cluster2.parent)) {
    if (cluster1.parent) {
      cluster1.parent.addChild(cluster2)
      parent = cluster1.parent
    } else {
      cluster2.parent.addChild(cluster1)
      parent = cluster2.parent
    }
  } else {
    // Create a new cluster
    parent = new Cluster(null, d / 2)
    parent.addChild(cluster1)
    parent.addChild(cluster2)
  }
  // Remove clusters
  clusters.splice(clusters.indexOf(cluster1), 1)
  clusters.splice(clusters.indexOf(cluster2), 1)
  // Insert the new cluster
  clusters.push(parent)
  if (clusters.length === 1) {
    return clusters[0]
  } else {
    return reGroup(clusters)
  }
}

export function getNewickTree (strains) {
  let baseClusters = makeClusters(strains)
  let tree = reGroup(baseClusters)
  let newickTree = printNewickTree(tree, tree.depth)
  console.log(newickTree)
  // dd(newickTree)
  return newickTree + ';'
}

// Return a tree to the Newick format
function printNewickTree (tree, len) {
  let str = ''
  if (tree.size > 1) {
    // it's an internal node
    str += '('
    // We go through all the children
    for (let i = 0; i < tree.children.length; i++) {
      str += printNewickTree(tree.children[i], tree.depth)
      if (i < tree.children.length - 1) str += ','
    }
    str += ')'
    str += ':' + (len - tree.depth).toFixed(2)
  } else {
    // it's a leaf
    str += tree.strains[0].name + ':' + len.toFixed(2)
  }
  return str
}
