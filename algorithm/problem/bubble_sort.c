#include <stdio.h>


void bbsort(int a[], int left, int right)
{
  int swap, i, t;
  do {
    swap = 0;
    for (i=left; i<right; i++) {
      if (a[i]>a[i+1]) {
        t = a[i]; a[i] = a[i+1]; a[i+1] = t;
        swap = 1;
      }
    }
  } while (swap);
}

int main()
{
  int a[10], i;
  for (i=0; i<10; i++) {
    a[i] = 71*(i+1)%100;
    printf("%d ", a[i]);
  }
  printf("\n");
  bbsort(a, 0, 9);
  for (i=0; i<10; i++) {
    printf("%d ", a[i]);
  }
  printf("\n");
}
