#include <stdio.h>

/* We use a n+1 elements array a[n+1] for convenience. a[0] is used to store
 * the return value, thus is not part of the permutation.  */
int test_perm(int *a, int n)
{
  int i, j;
  if (a == NULL)  return 0;     /* Test input */
  a[0] = 1;
  for (i = 1; i <= n; ++i)      /* Test input */
    if (a[i] < 1 || a[i] > n) { /* Is a[i] in the range 1~n? */
      a[0] = 0;
      return a[0];
    }

  for (i = 1; i <= n; ++i)
    if (a[i] > 0) {
      j = i;
      while (a[j] > 0) {        /* Follow the cycle */
        a[j] = -a[j];
        j = -a[j];
      }
      if (j != i)  a[0] = 0;    /* Test the cycle */
    }

  for (i = 1; i <= n; ++i)
    a[i] = a[i] > 0 ? a[i] : -a[i];

  return a[0];
}

int main()
{
  int a[] = { 0, 3, 5, 4, 3, 1 };
  printf("%d\n", test_perm(a, 5));
  return 0;
}
