#include <stdio.h>
#include <stdlib.h>

//#define PUTBOARD

unsigned int count = 0;

inline int unsafe(const int *b, int y)
{
  int i, t, x = b[y];
  for (i=1; i<=y; i++) {
    t = b[y-i];
    if ( (t == x) ||
         (t == x + i) ||
         (t == x - i) )
      return 1;
  }
  return 0;
}

/* ===================== ALGO1 =====================*/
#ifdef PUTBOARD
void putboard(const int *b, unsigned int n)
{
  int x, y;
  printf("\nSolution #%u:\n", ++count);
  for (y = 0; y < n; y++) {
    for (x = 0; x <n ; x++) {
       if (b[y] == x) printf("|Q");
       else printf("|_");
    }
    printf("|\n");
  }
}
#endif

void queens_nrec(unsigned int n)
{
  int *b = (int *) calloc(n, sizeof(int));
  int y = 0;
  b[0] = -1;
  while (y >= 0) {
    do {
      b[y]++;
    } while ((b[y] < n) && unsafe(b, y));
    if (b[y] < n) {
      if (y < n-1) {
        b[++y] = -1;
      } else {
#ifdef PUTBOARD
        putboard(b, n);
#else
        count++;
#endif
      }
    } else {
      y--;
    }
  }
  printf("We have %u solutions.\n", count);
}

int main(int argc, char **argv)
{
  unsigned int n;
  if (argc != 2 || sscanf(argv[1], "%u", &n) == -1) {
    fprintf(stderr, "Usage: %s n", argv[0]);
    return -1;
  }
  queens_nrec(n);
}
