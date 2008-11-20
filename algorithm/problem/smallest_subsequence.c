/* ===========================================================================
 * Problem:
 *   Given n signed integer numbers, find a subsequence with the smallest sum.
 *   eg. if the numbers are:
 *   48 -53 27 -32 58 25 38
 *   the smallest subsequence is: -53 27 -32
 *
 * Algorithm:
 *   Assume we have a signed integer number array a[n], the sum of prior k
 *   elements can be given as:
 *     s[k] = a[0] + a[1] + ... + a[k]. Then
 *     s[j]-s[i] = a[i] + a[i+1] + ... + a[j], j>i, i,j belongs to {0,...,n};
 *   Problem can be changed to find MIN(s[j] - s[i]) in s[n].
 *   To be continue...
 *
 * Analysis:
 *
 *
 * ===========================================================================
 */

#include <stdio.h>

#define N 7

int smallest_subseq(int a[N])
{
  int s[N], i, j, max, min, maxv, minv;
  
  /* Get s. */
  printf("An array of %d signed numbers:\n", N);
  for (i=0; i<N; i++)    printf("a[%d]=%d, ", i, a[i]);
  s[0] = a[0];
  for (i=1; i<N; i++) {
    s[i] = s[i-1] + a[i];
  }
  /* Find MIN(s[j]-s[i]). */
  max = 1; min = 0;
  for (i=0; i<N; i++) {
    if (s[i] < s[min]) continue;
    for (j=i; j<N; j++) {
      if (s[j] > s[max]) continue;
      if (s[j]-s[i] < s[max]-s[min]) {
        max = j; min = i;
      }
    }
  }
  /* The result. */
  printf("\b\b.\nThe smallest subsequence is:\n");
  for (i = min+1; i<=max; i++)    printf("a[%d]=%d, ", i, a[i]);
  printf("\b\b.\nThe sum of them is %d.\n", s[max] - s[min]);
  return 0;
}

int main()
{
  int a[N] = {48, -53, 27, -32, 58, 25, 38};
  int b[N] = {48, -53, 27, -32, -58, 25, 38};
  smallest_subseq(a);
  smallest_subseq(b);
}
