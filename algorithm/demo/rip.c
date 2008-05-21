/* COMPILE: gcc rip.c -o rip */

#include <stdio.h>
#include <stdlib.h>

#define MIN_ITEMS (128)     /* Minimum memory space for a RIP table. */
#define ALOC_STEP (64)      /* Auto increase step for allocation.    */
#define MAX_ITEMS (4800)    /* Maximum memory space for a RIP table. */
#define MAX_COST  (16)      /* Maximun cost(hops) for a RIP entry.   */

unsigned int _items_;       /* Current RIP table memory space size.  */

/* RIP item in RIP table. */
typedef struct rip_item {
  unsigned int dst;         /* Destination(1, 2, 3, ..., MAX_ITEMS-1) */
  unsigned int next;        /* Next host/router. (1, 2, 3, ..., MAX_ITEMS-1) */
  unsigned int cost;        /* Cost(hops) (1, 2, 3,..., MAX_COST) */
} RIP_ITEM;

/* RIP table format: RIP table is formed by `RIP_ITEM'. However, the first
   entry is not used, reserved for RIP table information. For example:
    0 1 2  // 0: first entry; 1: RIP of host 1; 2: number of entries.
    1 0 0  // 1: destination 1; 0: next router; 0: cost.
    2 2 1  // ...
*/

/* Build an initial RIP table for host `next'. */
RIP_ITEM *build_rip(unsigned int next)
{
  _items_ = MIN_ITEMS;
  RIP_ITEM *table = (RIP_ITEM *)malloc(_items_*sizeof(RIP_ITEM));
  if (table == NULL) {
    perror("Error in build_rip().");
    exit(0);
  }
  table[0].dst = 0; table[0].next = next; table[0].cost = 1;
  table[1].dst = next; table[1].next = 0; table[1].cost = 0;
  return table;
}

/* Insert RIP item `item' into sorted RIP table `table', search started at
   the `s'th entry. Because RIP table here is already sorted, give a location
   `s' can speed up this function. 
   insert_item() can auto allocate/reallocate memory for RIP table, as C++
   STL does. */
RIP_ITEM * insert_item(RIP_ITEM item, RIP_ITEM *table, unsigned int s)
{
  unsigned int i;
  if (table == NULL) {
    printf("Table should not be NULL in insert_item().");
    exit(1);
  } else if ( table[0].cost + 1 >= _items_) {
    /* Reallocate a larger memory for RIP table. */
    _items_ += ALOC_STEP;
    table = (RIP_ITEM *)realloc(table, _items_*sizeof(RIP_ITEM));
    if (table == NULL) {
      perror("RIP table too large, program exit");
      exit(0);
    }
  }
  /* Search after `s' to find a position for `item' in a sorted array. */
  for (s = s<1 ? 1 : s; s <= table[0].cost; s++) {
    if (item.dst == table[s].dst) {
      table[s] = item;
      return table;
    } else if (item.dst < table[s].dst) {
      break;
    }
  }
  /* Insert `item' into `table'. */
  for (i = table[0].cost; i>=s; i--) {
    table[i+1] = table[i];
  }
  table[s] = item;
  table[0].cost++;
  return table;
}

/* Find `item' in sorted `table', search started at `s'th entry(speed up). */
unsigned int find(RIP_ITEM item, RIP_ITEM *const table, int s)
{
  s = s<1 ? 1 : s;
  if (table == NULL || table[0].cost == 0) return 0;
  for (s; s<=table[0].cost; s++) {
    if (item.dst == table[s].dst)
      return s;
    else if (item.dst < table[s].dst)
      return 0;
  }
  return 0;
}

/* Insertion sort for RIP table. */
int isort(RIP_ITEM *const table)
{
  if (table == NULL || table[0].cost == 0) return 0;
  unsigned int i, j;
  RIP_ITEM key;
  for (j=2; j<=table[0].cost; j++) {
    key = table[j];
    i = j - 1;
    while (i > 0 && table[i].dst > key.dst) {
      table[i+1] = table[i];
      i--;
    }
    table[i+1] = key;
  }
  return 0;
}

/* Compare function for build in quick sort. */
int compare(const RIP_ITEM *p, const RIP_ITEM *q)
{
  if (p->dst > q->dst) return 1;
  else if (p->dst == q->dst) return 0;
  else return -1;
}

/* Sort function for RIP table. */
void sort(RIP_ITEM *const table)
{
  if (table[0].cost < 64)
    isort(table);   /* Insertion sort is more efficient for small number. */
  else
    /* When the number of entries is large, we use quick sort. */
    qsort(table, (table[0].cost+1), sizeof(RIP_ITEM), 
          (int (*)(const void *, const void*))compare);
}

/* RIP Algorithm. Arguments information:
    re: Received RIP table.
    lo: Local RIP table. */
RIP_ITEM * rip(RIP_ITEM *const re, RIP_ITEM * lo)
{
  RIP_ITEM temp;
  /* Received RIP table should not be NULL. */
  if (re == NULL || re[0].cost == 0) return 0;

  unsigned int i, j, t = 0;
  /* Sort received RIP table before we use. */
  sort(re);
  
  /* RIP algorithm. */
  for (j=1; j<=re[0].cost; j++) {
    if (re[j].next != re[0].next) {
      temp.dst = re[j].dst;
      temp.next = re[0].next;
      temp.cost = re[j].cost+1;
    } else {
      temp.dst = re[j].dst;
      temp.next = re[j].next;
      temp.cost = re[j].cost+1;
    }
    if (temp.cost >= MAX_COST)
      temp.cost = MAX_COST;  /* That means, destanation unreachable. */
    i = find(temp, lo, 0);   /* Find item in local RIP table.        */
    if (i == 0) {            /* Item not found.                      */
      lo = insert_item(temp, lo, t);   /* Insert it into local RIP table. */
    } else {
      t = i;
      /* Cost is less than local RIP info or entry more authoritative. */
      if (temp.cost < lo[i].cost || lo[i].next == re[0].next)
        lo[i] = temp;        /* Update local RIP table. */
    }
  }
  return lo;
}

/* Print RIP table to standard output. */
void print_rip(RIP_ITEM *const table)
{
  if (table == NULL) {
    printf("RIP table is empty.\n");
    return;
  }
  int i;
  printf("RIP table from host %d", table[0].next);
  printf("(No. of items: %d):\n", table[0].cost);
  printf("\tDestination\t  Next \t  Cost \n");
  for (i=1; i<=table[0].cost; i++) {
    if (table[i].cost < MAX_COST) {
      printf("\t%10d\t%6d\t%6d\n", table[i].dst, table[i].next, table[i].cost);
    } else {
      printf("\t%10d\t     -\t     -\n", table[i].dst);
    }
  }
}

/* RIP test for add an entry. */
void rip_add_test()
{
  printf("\n#################### RIP ADD TEST ####################\n\n");
  printf("\t***** Before RIP algorithm: *****\n");
  RIP_ITEM re_table[] = {{0, 2, 3}, {1, 1, 1}, {2, 0, 0}, {5, 3, 2}};
  printf("Received ");
  print_rip(re_table);
  RIP_ITEM *lo_table = build_rip(1);
  printf("Local ");
  print_rip(lo_table);
  lo_table = rip(re_table, lo_table);
  lo_table[0].next = 1;
  printf("\n\t***** After RIP algorithm: *****\n");
  printf("Local ");
  print_rip(lo_table);
  free(lo_table);
}

/* RIP test for update an entry. */
void rip_update_test()
{
  printf("\n#################### RIP UPDATE TEST ####################\n\n");
  printf("\t***** Before RIP algorithm: *****\n");
  RIP_ITEM re_table[] = {{0, 2, 3}, {1, 1, 1}, {2, 0, 0}, {5, 3, 2}};
  print_rip(re_table);
  RIP_ITEM *lo_table = build_rip(1);
  RIP_ITEM temp = {5, 3, 5};
  lo_table = insert_item(temp, lo_table, 1);
  lo_table[0].next = 1;
  printf("Local");
  print_rip(lo_table);
  lo_table = rip(re_table, lo_table);
  printf("\n\t***** After RIP algorithm: *****\n");
  printf("Local ");
  print_rip(lo_table);
  free(lo_table);
}

/* RIP test for loop condition. */
void rip_loop_test()
{
  printf("\n#################### RIP LOOP TEST ####################\n\n");
  printf("\t***** Initial *****\n");
  RIP_ITEM *table_1 = NULL, *table_2 = NULL;
  table_1 = build_rip(1);
  table_2 = build_rip(2);
  int i;
  
  RIP_ITEM temp = {2, 2, 1};
  insert_item(temp, table_1, 1);
  temp.dst = 1; temp.next = 1; temp.cost = 1;
  insert_item(temp, table_2, 1);
  temp.dst = 3; temp.next = 3; temp.cost = 1;
  insert_item(temp, table_2, 1);
  print_rip(table_1);
  print_rip(table_2);
  
  printf("\n\t***** Round 0 *****\n");
  table_1 = rip(table_2, table_1);
  temp.dst = 3; temp.next = 3; temp.cost = MAX_COST;
  table_2 = insert_item(temp, table_2, 1);
  print_rip(table_1);
  print_rip(table_2);
  for (i=1; (table_1[3].cost != MAX_COST) || (table_2[3].cost != MAX_COST);
       i++) {
    printf("\n\t***** Round %d *****\n", i);
    table_2 = rip(table_1, table_2);
    table_1 = rip(table_2, table_1);
    print_rip(table_1);
    print_rip(table_2);
  }
}

/* RIP verify tests. */
void rip_verify()
{
  rip_add_test();
  rip_update_test();
  rip_loop_test();
}

int main(int argc, char **argv)
{
  rip_verify();
  return 0;
}

