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
 *     s[j]-s[i] = a[i+1] + ... + a[j], j>i, i,j belongs to {0,...,n-1};
 *   Problem can be changed to find MIN(s[j] - s[i]) in s[n]. This can be done
 *   in one scan through s[n].
 *   1. I = 0; J = 1; max = 0;
 *   2. for j = 1, 2, ..., n-1
 *   3.   if s[j]-s[max] < s[J]-s[I]
 *   4.     (I,J) := (max, j)
 *   5.   max = s[j] > s[I] ? j : max;
 *
 *   Proof: 
 *     If s[J] - s[I] == MIN(s[j] -s[i]) satisfied, we must have:
 *     s[I] >= s[i] ie. s[I] = MAX(s[i]), for arbitrary i < J and i >= 0;
 *     s[J] <= s[i] ie. s[J] = MIN(s[i]) for arbitrary i > I and i < n.
 *     That is what our algorithm does.
 *     Step 5 makes sure s[max] is the MAX(s[i]) for i<=j;
 *     Step 4 makes sure s[I] is the MAX(s[i]) for i<=J;
 *     Step 3 makes sure s[J] - s[I] is the minimal s[j] -s[i], also s[J]
 *     is the MIN(s[i]) for i>I.
 *     So we found (I, J) which makes  s[J] - s[I] minimal.
 *     QED.
 *
 * Analysis:
 *   The most important and useful idea of this algorithm is converting a
 *   sum of subarray(a[i+1]+...+a[j]) to the difference of two elements
 *   (s[j]-s[i]), which are the prior sums of the original array
 *   (s[k]=a[0]+...+a[k]).
 *
 * ===========================================================================
 */

#include <stdio.h>
#include <stdlib.h>

int smallest_subseq(int a[], unsigned int len)
{
  int i, I, J, max;
  int *s = (int *)malloc(len*sizeof(int));

  /* Get prior sum array s[len]. */
  printf("An array of %d signed numbers:\n", len);
  for (i=0; i<len; i++) {
    if (i && (i%7 == 0)) printf("\n");
    printf("a[%d]=%d, ", i, a[i]);
  }
  s[0] = a[0];
  for (i=1; i<len; i++) {
    s[i] = s[i-1] + a[i];
  }
  /* Find s[J]-s[I] = MIN(s[j]-s[i]), J>I. */
  J = 1; I = 0; max = 0;
  for (i=1; i<len; i++) {
    if (s[i]-s[max] < s[J]-s[I]) {
      I = max;
      J = i;
     }
    max = s[i] > s[I] ? i : max;
  }
  /* The result. */
  printf("\b\b.\nThe smallest subsequence is:\n");
  for (i = I+1; i<=J; i++) {
    if ((i-I-1) && ((i-I-1)%7 == 0)) printf("\n");
    printf("a[%d]=%d, ", i, a[i]);
  }
  printf("\b\b.\nThe sum of them is %d.\n", s[J] - s[I]);
  free(s);
  return 0;
}

int main()
{
  int a[] = {48, -53, 27, -32, 58, 25, 38};
  int b[] = {48, -53, 27, -32, -58, 25, -38, 99, -100, 24, 57};
  smallest_subseq(a, sizeof(a)/4);
  smallest_subseq(b, sizeof(b)/4);
  return 0;
}
