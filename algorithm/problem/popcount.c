/* ===========================================================================
 * Problem: 
 *   The fastest way to count how many 1s in a 32-bits integer.
 *
 * Algorithm:
 *   The problem equals to calculate the Hamming weight of a 32-bits integer,
 *   or the Hamming distance between a 32-bits integer and 0. In binary cases,
 *   it is also called the population count, or popcount.[1]
 *
 *   The best solution known are based on adding counts in a tree pattern
 *   (divide and conquer). Due to space limit, here is an example for a
 *   8-bits binary number A=01101100:[1]
 * | Expression            | Binary   | Decimal | Comment                    |
 * | A                     | 01101100 |         | the original number        |
 * | B = A & 01010101      | 01000100 | 1,0,1,0 | every other bit from A     |
 * | C = (A>>1) & 01010101 | 00010100 | 0,1,1,0 | remaining bits from A      |
 * | D = B + C             | 01011000 | 1,1,2,0 | # of 1s in each 2-bit of A | 
 * | E = D & 00110011      | 00010000 | 1,0     | every other count from D   |
 * | F = (D>>2) & 00110011 | 00010010 | 1,2     | remaining counts from D    |
 * | G = E + F             | 00100010 | 2,2     | # of 1s in each 4-bit of A |
 * | H = G & 00001111      | 00000010 | 2       | every other count from G   |
 * | I = (G>>4) & 00001111 | 00000010 | 2       | remaining counts from G    |
 * | J = H + I             | 00000100 | 4       | No. of 1s in A             |
 * Hence A have 4 1s.
 *
 * [1] http://en.wikipedia.org/wiki/Hamming_weight
 *
 * ===========================================================================
 */
#include <stdio.h>

typedef unsigned int UINT32;
const UINT32 m1  = 0x55555555;  // 01010101010101010101010101010101
const UINT32 m2  = 0x33333333;  // 00110011001100110011001100110011
const UINT32 m4  = 0x0f0f0f0f;  // 00001111000011110000111100001111
const UINT32 m8  = 0x00ff00ff;  // 00000000111111110000000011111111
const UINT32 m16 = 0x0000ffff;  // 00000000000000001111111111111111
const UINT32 h01 = 0x01010101;  // the sum of 256 to the power of 0, 1, 2, 3

/* This is a naive implementation, shown for comparison, and to help in 
 * understanding the better functions. It uses 20 arithmetic operations
 * (shift, add, and). */
int popcount_1(UINT32 x)
{
  x = (x & m1) + ((x >> 1) & m1);
  x = (x & m2) + ((x >> 2) & m2);
  x = (x & m4) + ((x >> 4) & m4);
  x = (x & m8) + ((x >> 8) & m8);
  x = (x & m16) + ((x >> 16) & m16);
  return x;
}

/* This uses fewer arithmetic operations than any other known implementation
 * on machines with slow multiplication. It uses 15 arithmetic operations. */
int popcount_2(UINT32 x)
{
  x -= (x >> 1) & m1;             //put count of each 2 bits into those 2 bits
  x = (x & m2) + ((x >> 2) & m2); //put count of each 4 bits into those 4 bits 
  x = (x + (x >> 4)) & m4;        //put count of each 8 bits into those 8 bits 
  x += x >> 8;           //put count of each 16 bits into their lowest 8 bits
  x += x >> 16;          //put count of each 32 bits into their lowest 8 bits
  return x & 0x1f;
}

/* This uses fewer arithmetic operations than any other known implementation
 * on machines with fast multiplication. It uses 12 arithmetic operations, 
 * one of which is a multiply. */
int popcount_3(UINT32 x)
{
  x -= (x >> 1) & m1;             //put count of each 2 bits into those 2 bits
  x = (x & m2) + ((x >> 2) & m2); //put count of each 4 bits into those 4 bits 
  x = (x + (x >> 4)) & m4;        //put count of each 8 bits into those 8 bits 
  return (x * h01) >> 24;  // left 8 bits of x + (x<<8) + (x<<16) + (x<<24)
}

int main()
{
  int i = 0x1ff12ee2; 
  printf("i = %d = 0x%x\n", i, i);
  printf("popcount_1(%d) = %d\n", i, popcount_1(i));
  printf("popcount_2(%d) = %d\n", i, popcount_2(i));
  printf("popcount_3(%d) = %d\n", i, popcount_3(i));
  return 0;
}
