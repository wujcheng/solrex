#include <stdio.h>
#include <stdlib.h>

/* Print a matrix following a diagonal, index only. */

int d_print(int N, int direct)
{
  static int i = 0, j = 0, k = 0, d = 0;
  if (d == 0) {                         // Start point
    d = direct;
  } else if (i != k && j != k) {        // In the middle of matrix
    i = d > 0 ? i - 1 : i + 1;
    j = d < 0 ? j - 1 : j + 1;
  } else {                              // Edges
    if (d > 0) {
      if (j == k) {
        if (k < (N - 1)) {
          k = ++j;
          d = -d;
        } else {
          ++i;
          d = -d;
        }
      } else {
        ++j;
        --i;
      }
    } else {
      if (i == k) {
        if (k < (N - 1)) {
          k = ++i;
          d = -d;
        } else {
          ++j;
          d = -d;
        }
      } else {
        --j;
        ++i;
      }
    }
  }
  if (i == N || j == N) {
    i = 0;
    j = 0;
    k = 0;
    d = 1;
    return 0;
  } else {
    printf("(%d, %d, %d)\n", i, j, d);
    fflush(NULL);
    return 1;
  }
}

int main(int argc, char **argv)
{
  int N;
  if (argc != 2 || sscanf(argv[1], "%d", &N) == -1) {
    printf("Usage: %s N\n", argv[0]);
    return -1;
  }
  while (d_print(N, 1));
  return 0;
}
