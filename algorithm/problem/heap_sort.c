#include <stdio.h>

void heapify(int a[], int left, int right)
{
  int i, t;
  t = a[left];
  for (i=2*left+1; i<=right; i=2*i+1) {
    if (i<right && a[i+1]>a[i]) i++;
    if (t>=a[i]) break;
    a[left] = a[i];
    left = i;
  }
  a[left] = t;
}

void hsort(int a[], int left, int right)
{
  int i, t;
  for (i=(right+left)/2; i>=left; i--) {
    heapify(a, i, right);
  }
  for (i=right; i>=left; i--) {
    t = a[i];
    a[i] = a[left];
    a[left] = t;
    heapify(a, left, i-1); 
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
  hsort(a, 0, 9);
  int _i;
  for (_i=0; _i<10; _i++) {
    printf("%d ", a[_i]);
  }
  printf("\n");
}
