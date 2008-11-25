/* ===========================================================================
 * Problem[1]:
 *   Sort a directed graph with no cycles, such as:
 *       -->B<-----E<---------------I
 *      /   |      |  |\`          /
 *     /   \|/    \|/   \         /
 *    /     *      *     \       /
 *   A----->F      D      G     /
 *          |     / \     |    /
 *         \|/   /   \   \|/  /
 *          *   /     \   *  /
 *          H<--       -> C<-
 *
 *   If the graph contains an edge from A to B, then A is B's predecessor
 *   and B is A's successor. The algorithm must order the nodes such that
 *   all predecessors appear before their successors; here is one of many
 *   possible orderings.
 *     A G I E B D C F H
 *
 *   The algorithm must cope with the possibility that the input graph
 *   contains a cycle and therefor cannot be sorted.
 *
 * Algorithm I: [2]
 *   Choose a node T with no predecessors, write T to the output, and then
 *   remove from the graph all edges emanating from T.
 * 
 * Algorithm II: [1]
 *   as each (pred, succ) pair is read
 *     increment pred count of succ
 *     append succ to successors of pred
 *   at the end of the input file
 *     initialize queue to empty
 *     for each node i
 *       if pred count of i is zero then append i to queue
 *     while queue isn't empty do
 *       delete t from front of queue; print t
 *       for each successor s of t
 *         decrement pred count of s
 *         if that goes to zero then append x to queue
 *     if some nodes were not output then report a cycle
 *
 *   For each node the algorithm stores the number of predecessors the node
 *   has and the set of its successors. The iterative step of the algorithm
 *   chooses a node whose predecessor count is zero, writes it on the output,
 *   and decrements the predecessor count of all its successors. It must be
 *   careful, though, to remember the order in which the counts went to zero;
 *   it uses a queue of nodes for that task. (If the queue becomes empty
 *   before all nodes have a predecessor count of zero, then the program
 *   reports that the graph contains a cycle.)
 *
 * Analysis:
 *   The above two algorithms show us a rule Pike offered in his famous
 *   ``Notes on Programming in C''[3]:
 *
 *   Rule 5. Data dominates. If you've chosen the right data structures
 *   and organized things well, the algorithms will almost always be self
 *   evident.  Data structures, not algorithms, are central to programming.
 *   (See Brooks ``The Mythical Man-Month'' p. 102.)
 *
 *   In C, we have few build in data types. We have no `container' as in C++,
 *   `dictionary' as in Python, `associative array' as in Awk etc. So the
 *   bellow C implement of these 2 algorithms is ugly and dirty, not as
 *   elegant as in other languages.
 *
 * [1] J. Bentley, More Programming Pearls, Addison-Wesley, pp.20-23, 1988.
 * [2] D. Knuth, The Art of Computer Programming, volume 1: Fundamental
 * Algorithms.
 * [3] R. Pike, Notes on Programming in C,
 *     http://www.lysator.liu.se/c/pikestyle.html
 * ===========================================================================
 */

#include <stdio.h>
#include <stdlib.h>

typedef int NTYPE;
#define HAVE_PRE  1
#define NO_PRE    2
#define DELETED   0

typedef struct _edge {
  char pred;
  char succ;
} EDGE;
typedef char NODE;

/* ==================== Algorithm I(Knuth) ==================== */
int knuth(NODE nodes[], EDGE edges[], int num_nodes, int num_edges)
{  
  NTYPE *node_type = (NTYPE *) malloc(num_nodes*sizeof(NTYPE));
  NODE  *out       = (NODE *) malloc((num_nodes+1)*sizeof(NODE));

  int i, k=0;

  for (i=0; i<num_nodes; i++) node_type[i] = NO_PRE;

  for (k=0; k<num_nodes; k++) {
    for (i=0; i<num_edges; i++) {
      if (edges[i].succ != DELETED)
        node_type[edges[i].succ - 'A'] = HAVE_PRE;
    }
    for (i=0; i<num_nodes; i++) {
      if (node_type[i] == NO_PRE) {
        node_type[i] = DELETED;
        out[k] = nodes[i];
        break;
      }
    }
    if (i == num_nodes) break;
    for (i=0; i<num_edges; i++)
      if (edges[i].pred == out[k]) {
        edges[i].pred = DELETED;
        edges[i].succ = DELETED;
      }
    for (i=0; i<num_nodes; i++) 
      node_type[i] = node_type[i] ? NO_PRE : DELETED;
  }
  out[k] = '\0';
  printf("Algorithm I(Knuth)\n  The sorted topology is: %s\n", out);
  free(node_type);
  free(out);
  return 0;
}

/* ==================== Algorithm II(Bentley) ==================== */
int bentley(NODE nodes[], EDGE edges[], int num_nodes, int num_edges)
{
  /* Allocate memory. */
  int  *node_predc = (int *) malloc(num_nodes*sizeof(int));
  int  *node_succc = (int *) malloc(num_nodes*sizeof(int));
  NODE *node_succv = (NODE *) malloc(num_nodes*(num_nodes-1)*sizeof(NODE));
  NODE *queue      = (NODE *) malloc((num_nodes+1)*sizeof(NODE));
  int i, k;

  /* Initializing. */
  for (i=0; i<num_nodes; i++) {
    node_predc[i] = node_succc[i] = 0;
  }

  for (i=0; i<num_edges; i++) {
    node_predc[edges[i].succ - 'A']++;
    k = edges[i].pred - 'A';
    node_succv[k*(num_nodes-1) + node_succc[k]++] = edges[i].succ;
  }
  NODE *head = queue;
  NODE *tail = queue;
  for (i=0; i<num_nodes; i++) {
    if (node_predc[i] == 0) *tail++ = nodes[i];
  }
  while (head != tail) {
    for (i=0; i<node_succc[*head - 'A']; i++) {
      k = node_succv[(*head - 'A')*(num_nodes-1) + i] - 'A';
      node_predc[k]--;
      if (node_predc[k] == 0) {
        *tail++ = nodes[k];
      }
    }
    head++;
  }
  *tail = '\0';
  printf("Algorithm II(Bentley)\n  The sorted topology is: %s\n", queue);
  free(node_predc);
  free(node_succc);
  free(node_succv);
  free(queue);
}

int main()
{
  NODE nodes[] = {'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'};
  EDGE edges_I[] = {
    {'A', 'B'}, {'A', 'F'}, {'B', 'F'}, {'F', 'H'}, {'E', 'B'}, {'E', 'D'},
    {'D', 'H'}, {'D', 'C'}, {'G', 'E'}, {'G', 'C'}, {'I', 'E'}, {'I', 'C'},
  };
  EDGE edges_II[] = {
    {'A', 'B'}, {'A', 'F'}, {'B', 'F'}, {'F', 'H'}, {'E', 'B'}, {'E', 'D'},
    {'D', 'H'}, {'D', 'C'}, {'G', 'E'}, {'G', 'C'}, {'I', 'E'}, {'I', 'C'},
  };
  knuth(nodes, edges_I, sizeof(nodes)/sizeof(NODE),
        sizeof(edges_I)/sizeof(EDGE));
  bentley(nodes, edges_II, sizeof(nodes)/sizeof(NODE),
          sizeof(edges_II)/sizeof(EDGE));
  return 0;
}
