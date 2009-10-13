#include <stdio.h>

void merge(int a[], int b[], int left, int middle, int right)
{
  printf("merge(a,b,left=%d,middle=%d, right=%d)\n", left, middle, right);
  int i, j, k;
  i = left; j = middle+1; k=left;
  if(a[middle+1]>=a[middle]) return;
  while((middle-i)>=0 && (right-j)>=0) {
    if(a[i]<=a[j]) {
      b[k++] = a[i++];
    } else {
      b[k++] = a[j++];
    }
  }
  if(middle-i>=0) {
    for(i; i<=middle; i++, k++)  b[k] = a[i];
  } else {
    for(i=j; i<=right; i++, k++)  b[k] = a[i];
  }
  for(i=left; i<=right; i++) {
    a[i]=b[i];
  }
}


void msort(int a[], int b[], int left, int right)
{
  if (right-left <= 0) return;
  int middle = (left + right)/2;
  msort(a, b, left, middle);
  msort(a, b, middle+1, right);
  return merge(a, b, left, middle, right);
}


int main()
{
  int a[10], i, b[10];
  for (i=0; i<10; i++) {
    a[i] = 71*(i+1)%100;
    printf("%d ", a[i]);
  }
  printf("\n");
  msort(a, b, 0, 9);
  int _i;
  for (_i=0; _i<10; _i++) {
    printf("%d ", a[_i]);
  }
  printf("\n");
}
