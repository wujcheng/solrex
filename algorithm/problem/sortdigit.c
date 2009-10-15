/* ===========================================================================
 * Problem: 
 *   Input an integer, output a ineger which has the sorted digits. Such as:
 *   1300515 -> 11355, 2009 -> 29
 * ===========================================================================
 */
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

int compare(const void *p, const void *q)
{
  return *(const char *)p - *(const char *)q;
}

unsigned int sortdigit(unsigned int x)
{
  char a[11];
  sprintf(a, "%d", x);
  qsort(a, strlen(a), sizeof(char), compare);
  return atoi(a);
}

int main()
{
  printf("%u\n", sortdigit(1312515));
  return 0;
}
