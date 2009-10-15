#include <stdio.h>
#include <stdlib.h>
#ifdef __unix__
#include <unistd.h>
#include <sys/times.h>
#else
#include <time.h>
#endif

//#define PUTBOARD

int count = 0;

inline int unsafe(const int *b, int y)
{
  register int i, t, x = b[y];
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
void putboard(const int *b, int n)
{
  int x, y;
  printf("\nSolution #%d:\n", ++count);
  for (y = 0; y < n; y++) {
    for (x = 0; x <n ; x++) {
       if (b[y] == x) printf("|Q");
       else printf("|_");
    }
    printf("|\n");
  }
}
#endif

void queens_nrec(int n)
{
  int *b = (int *) calloc(n, sizeof(int));
  register int y = 0;
  count = 0;
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
    } else y--;
  }
  printf("\nThere are %d solutions for N=%d.\n", count, n);
}

void check(int *b, int n, int y)
{
  if (y == n) {
#ifdef PUTBOARD
        putboard(b, n);
#else
        count++;
#endif
    return;
  }
  b[y] = -1;
  while ((++b[y])<n) {
    if (unsafe(b, y) == 0) check(b, n, y+1);
  }
}

void queens_rec(int n)
{
  int *b = (int *) calloc(n, sizeof(int));
  count = 0;
  check(b, n, 0);
  printf("\nThere are %d solutions for N=%d.\n", count, n);
}

int main(int argc, char **argv)
{
  int n;

#ifdef __unix__
  struct tms tmsstart, tmsend;
  clock_t start, end;
  long clktck = sysconf(_SC_CLK_TCK);
#endif

  if (argc != 2 || sscanf(argv[1], "%d", &n) == -1) {
    fprintf(stderr, "Usage: %s n", argv[0]);
    return -1;
  }
  
#ifdef __unix__
  start = times(&tmsstart);
#endif

  queens_rec(n);

#ifdef __unix__
  end = times(&tmsend);
  printf("queens_nrec() run %7.3f s\n", (end-start)/ (double) clktck);
  start = times(&tmsstart);
#endif

  queens_nrec(n);

#ifdef __unix__
  end = times(&tmsend);
  printf("queens_rec() run %7.3f s\n", (end-start)/ (double) clktck);
#endif

  return 0;
}
