#include <stdio.h>
#include <assert.h>

int partition(int a[], int left, int right, int pivot)
{
  int i, t, pIndex;
  assert(a != NULL);
  t = a[pivot];
  a[pivot] = a[right];
  a[right] = t;
  pIndex = left;
  for (i = left; i < right; i++) {
    if (a[i] <= a[right]) {
      if (i != pIndex) {
        t = a[i];
        a[i] = a[pIndex];
        a[pIndex] = t;
      }
      pIndex++;
    }
  }
  t = a[pIndex];
  a[pIndex] = a[right];
  a[right] = t;
  return pIndex;
}

void qsort(int a[], int left, int right)
{
  int pivot;
  assert(a != NULL);
  if (right > left) {
    pivot = partition(a, left, right, left);
    qsort(a, left, pivot - 1);
    qsort(a, pivot + 1, right);
  }
}

void qsort_3way(int a[], int lo, int hi)
{
  if (hi <= lo) return;
  int lt = lo, gt = hi, i = lt;
  int v = a[lo], t;
  while (i <= gt) {
    if (a[i] < v) {
      t = a[i]; a[i] = a[lt]; a[lt] = t;
      ++i; ++lt;
    } else if (a[i] > v) {
      t = a[i]; a[i] = a[gt]; a[gt] = t;
      --gt;
    } else i++;
  }
  qsort_3way(a, lo, lt - 1);
  qsort_3way(a, gt + 1, hi);
}

int main()
{
  int a[10], i;
  for (i = 0; i < 10; i++) {
    a[i] = 71 * (i + 1) % 100;
    printf("%d ", a[i]);
  }
  printf("\n");
  qsort_3way(a, 0, 9);
  for (i = 0; i < 10; i++) {
    printf("%d ", a[i]);
  }
  printf("\n");
}
