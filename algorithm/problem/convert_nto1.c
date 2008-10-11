/* ===========================================================================
 * Problem:
 *   How many steps needed at least to convert an integer n to 1 based on 
 *   the rules bellow?
 *   * If n is even, divid n by 2;
 *   * If n is odd, add/subtract n by 1.
 *
 * Algorithm I: (Bit Operation)
 *   WHILE n != 1
 *     IF n = 3    n := n - 1
 *     ELSE IF n mod 2 = 0    n := n/2
 *     ELSE IF n mod 4 = 1    n := n - 1
 *     ELSE IF n mod 4 = 3    n := n + 1
 *
 * Analysis I(1): (Probability Theory)
 *   We represent n as a binary number. The hard part of this problem is
 *   how to decide +1 or -1 when n is odd? When n is odd, we consider bit 1
 *   of n(because bit 0 of n is already 1):
 *   * If bit 1 of n is 1: Add 1 we get 00(ignore carry bit), 3 steps to
 *     eliminate 2 bits; Sub 1 we get 10, 4 steps to eliminate 2 bits. Consider
 *     the carry bit, if bit 2 of n is 0, then add 1 will increase 1 step.
 *     However, count in the possibility, add 1 only introduce 0.5 more step.
 *     3.5 is better than 4, so we should add 1.
 *   * If bit 1 of n is 0: Add 1 we get 10(ignore carry bit), 4 steps to
 *     eliminate 2 bits; Sub 1 we get 00, 3 steps to eliminate 2 bits. Consider
 *     the carry bit, if bit 2 of n is 0, then add 1 will decrease 1 step.
 *     However, count in the possibility, add 1 only introduce 0.5 less step.
 *     3 is better than 3.5, so we should sub 1.
 *   * If n equals 3. There is no bit 2, and we want 1 instead of 0, so sub
 *     1 should be better.
 *
 * Analysis I(2): (Dynamic Programming)
 *   Let f(n) denote the fewest steps needed to conver n to 1. Hence we get
 *   state transition equations:
 *     f(2k) = f(k) + 1;
 *     f(2k+1) = min(f(k), f(k+1)) + 2;
 *   And we have f(2k) <= f(2k+1) and f(2k+1) >= f(2k+2) hold for k>=1.
 *   (can be proofed by Mathematical Induction)
 *   Then we get:
 *     f(4k+1) = min(f(2k), f(2k+1)) + 2
 *             = f(2k) + 2 = f(4k) + 1
 *     f(4k+3) = min(f(2k+1), f(2k+2)) + 2
 *             = f(2k+2) + 2 = f(4k+4) + 1
 *   k = 0 is a special case, intuitively f(1) = 0, f(2) = 1, f(3) = 2.
 *   QED
 *
 * Algorithm II: (Recursive Function)
 *   FUNC(n):
 *     IF n = 1 RETURN 0
 *     IF n mod 2 = 0 RETURN 1 + FUNC(n/2)
 *     RETURN 1 + min(FUNC((n+1)/2), FUNC((n-1)/2)
 *
 * Analysis II:
 *   The resursive function is very self-explanatory. If we use an array of
 *   size 2*log(n) to store the intermadiate result, the computational 
 *   complexity of this algorithm can be reduced to O(log(n)).
 *
 * Compile and Run:
 *   $ gcc convert_nto1.c
 *   $ ./a.out
 *   91->92->46->23->24->12->6->3->2->1
 *   For n=91, algorithm I get 1 in 9 steps.
 *   For n=91, algorithm II get 1 in 9 steps.
 *
 * ===========================================================================
 */

#include <stdio.h>

int algo1(unsigned int n)
{
  int count = 0;
  while (n != 1) {
    count++;
    printf("%d->", n);
    if (n == 3)    n--;
    else if (n % 2 == 0)    n/=2;
    else if (n % 4 == 1)    n--;
    else n++;
  }
  printf("%d\n", n);
  return count;
}

int algo2(unsigned int n)
{
  if (n == 1)    return 0;
  if (n % 2 == 0)    return (1 + algo2(n/2));
  int a = 2 + algo2((n+1)/2);
  int b = 2 + algo2((n-1)/2);
  return (a < b ? a : b);
}

int main(int argc, char **argv)
{
  int t, count;
  if (argc > 1) t = atoi(argv[1]);
  else t = 91;
  count = algo1(t);
  printf("For n=%d, algorithm I get 1 in %d steps.\n", t, count);
  count = algo2(t);
  printf("For n=%d, algorithm II get 1 in %d steps.\n", t, count);
  return 0;
}
