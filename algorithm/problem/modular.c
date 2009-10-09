/* ==========================================================================
 * Problem: 
 *   Input a big integer number A, output A % 37.
 * ==========================================================================
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#define DIVISOR (37)
const int divisor = DIVISOR;
const int tail_9 = 1000000000%DIVISOR;

int main(int argc, char **argv)
{
  unsigned int len;
  char *input, slice_a[10];
  int slice_i, i, ret, tail;

  if (argc != 2) {
    printf("Usage: moddivisor 141351561515141\n");
    return 0;
  }

  len = strlen(argv[1]);
  input = (char *) malloc((len+1)*sizeof(char));
  strncpy(input, argv[1], len);
  input[len] = '\0';
  printf("%s %% %d\n", input, divisor);

  ret = 0;
  tail = 1;
  slice_a[9] = '\0';
  for (i = len-9; i >= 0; i -= 9) {
    strncpy(slice_a, &input[i], 9);
    slice_i = atoi(slice_a);
    slice_i = ((slice_i % divisor) * tail) % divisor;
    tail = (tail * tail_9) % divisor;
    ret = (slice_i + ret) % divisor;
  }

  if (i<0) {
    i += 9;
    strncpy(slice_a, input, i);
    slice_a[i] = '\0';
    slice_i = atoi(slice_a);
    slice_i = ((slice_i % divisor) * tail) % divisor;
    tail = (tail * tail_9) % divisor;
    ret = (slice_i + ret) % divisor;
  }

  printf("= %d\n", ret);
  return 0;
}

