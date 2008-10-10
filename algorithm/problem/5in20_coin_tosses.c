/* ==========================================================================
 * Problem: 
 *   Out of 20 coin tosses, what is the probability of getting at 
 *   least once a row of 5 consecutive heads?
 *
 * Algorithm:
 *   We denote 1 as a head, 0 as a tail, 20 coin tosses can be marked as
 *   a 20 bit binary number sequence, e.g. 00110....11. Let f(n) denote the
 *   number of different sequences which have at least one row of 5 
 *   consecutive 1s after n coin tosses. Then f(20)/2^20 should be the
 *   answer of this problem.
 *
 *   Recurrence equation:
 *     f(n+1) = f(n)*2 + 2^(n-5) - f(n-5), where f(0~4) = 0, f(5) = 1.
 *
 *   Proof: (similar Mathematical Induction)
 *     * Initialization: We have f(0~4) = 0, f(5) = 1 intuitively.
 *     * Maintenance: After n tosses, we have f(n) sequences which have at
 *       least one row of 5 consecutive 1s. No matter what the result of the
 *       last toss(n+1) is, these sequences still hold. So we have f(n)*2
 *       sequences. Assume a n bit sequence haven't been counted in f(n),
 *       after the n+1 toss, if and only if its last 5 bit is 01111 and the
 *       last toss gives 1, it can be counted in f(n+1). The number of these
 *       n bit sequences is 2^(n-5) - f(n-5). Hence we get:
 *         f(n+1) = f(n)*2 + 2^(n-5) - f(n-5)
 *     * Termination: 20 is a finate number, after n+1 hit 20, algorithm 
 *       will terminate.
 *
 * Answer:
 *   f(20) = 262008
 *   P = f(20)/2^20 = 0.24987030029296875
 *
 * Compile and Run:
 *   $ gcc 5in20_coin_tosses.c
 *   $ ./a.out
 *   f(20)= 262008
 *   $ echo "scale=20;262008/2^20" | bc
 *   .24987030029296875000
 *
 * ==========================================================================
 */
#include <stdio.h>

int main()
{
  int i, j, f0, f1, f[5];
  f0 = 1;
  for(i=0; i<5; i++) f[i] = 0;
  for(i=5; i<20; i++) {
    f1 = f0*2 + (1<<(i-5)) - f[0];
    for(j=0; j<4; j++) f[j] = f[j+1];
    f[4] = f0;
    f0 = f1;
  }
  printf("f(%d)= %d\n", i, f0);
}
