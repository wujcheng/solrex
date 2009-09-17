#include <stdio.h>

void isort(int a[], int left, int right)
{
  int i, j, t;
  for (i=left; i<=right; i++) {
    for (j=left; j<i; j++) {
      if (a[i]<a[j]) {
        t = a[i]; a[i] = a[j]; a[j] = t;
      }
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
  isort(a, 0, 9);
  for (i=0; i<10; i++) {
    printf("%d ", a[i]);
  }
  printf("\n");
}
