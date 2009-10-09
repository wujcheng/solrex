/* ==========================================================================
 * Problem: 
 *   A sorted increasing integer array A, find A[i] = i.
 *
 * Example:
 *   A[] = {1, 2, 3, 4, 5, 5, 8, 9, 10}
 *   Output: A[5] = 5
 * ==========================================================================
 */

#include <stdio.h>

int find(int *a, unsigned int lft, unsigned int rht)
{
  printf("find (a, %d, %d)\n", lft, rht);
  if (rht < lft) return -1;
  if (a[lft] > lft) {
    if (a[lft] <= rht) lft = a[lft];
    else return -1;
  }
  if (a[rht] < rht ) {
    if (a[rht] >= lft) rht = a[rht];
    else return -1;
  }
  if (a[lft] == lft) return lft;
  if (a[rht] == rht) return rht;
  return find(a, lft+1, rht-1);
}

int main()
{
  int test[] = {1,2,3,4,5,5,1,8,9,11};
  printf("%d\n", find(test, 0, (sizeof(test)/sizeof(int) - 1)));
  return 0;
}
