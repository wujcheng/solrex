#include <stdio.h>

void ssort(int a[], int left, int right)
{
  int i, j, t, k;
  for (i=left; i<right; i++) {
    k = i;
    for (j=i+1; j<=right; j++) {
      k = a[j]<a[k] ? j : k;
    }
    if (k!=i) {
      t = a[i]; a[i] = a[k]; a[k] = t;
    }
  }
}

int main()
{
  int a[10], i;
  for (i=0; i<10; i++) {
    a[i] = 71*(i+1)%100;
    printf("%d ", a[i]);
  }
  printf("\n");
  ssort(a, 0, 9);
  for (i=0; i<10; i++) {
    printf("%d ", a[i]);
  }
  printf("\n");
}
