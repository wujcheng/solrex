/* ===========================================================================
 * Problem: 
 *   Construct 3 NOT gates from only 2 NOT gates(and *no* XOR gates).
 *
 *   Assume that a logical blackbox has three Boolean inputs x, y, z and
 *   three Boolean outputs X, Y, Z where the outputs are defined as:
 *     X = ~x
 *     Y = ~y
 *     Z = ~z
 *   Note that ~ stands for a NOT gate. Please realize this blackbox using
 *   only two NOT gates, and as many as possible AND and OR gates.
 *
 * Algorithm I:
 *   Internal Nodes:
 *   r = (x & y) | (x & z) | (y & z);
 *   R = ~r;
 *   s = (R & (x | y | z)) | (x & y & z);
 *   S = ~s;
 *
 *   Equations for Outputs:
 *   X = (R & S) | (R & s & (y | z)) | (r & S & (y & z));
 *   Y = (R & S) | (R & s & (x | z)) | (r & S & (x & z));
 *   Z = (R & S) | (R & s & (x | y)) | (r & S & (x & y));
 *
 * Analysis I:
 *   We create 4 internal signals first: r, R, s and S. What equations above
 *   say is that signal `r' will be 1 if two or three of the inputs are 1.
 *   Meanwhile, signal `s' will be 1 if only one input is 1 or if all three
 *   inputs are 1. The end result is that the two-bit word formed from `r'
 *   and `s' tells us how many 1's we have:
 *   | r s | Means  |    | x y z | r s |    | x y z | r s |
 *   |++++++++++++++|    |+++++++++++++|    |+++++++++++++|
 *   | 0 0 | 0 Ones |    | 0 0 0 | 0 0 |    | 1 0 0 | 0 1 |
 *   | 0 1 | 1 One  |    | 0 0 1 | 0 1 |    | 1 0 1 | 1 0 |
 *   | 1 0 | 2 Ones |    | 0 1 0 | 0 1 |    | 1 1 0 | 1 0 |
 *   | 1 1 | 3 Ones |    | 0 1 1 | 1 0 |    | 1 1 1 | 1 1 |
 *
 *   Thus now that we have the signals r and s (and their inverse
 *   counterparts R and S), it's easy(?) to construct any Boolean function of
 *   x, y, and z, using only AND and OR gates:
 *     X = (R & S) | (R & s & (y | z)) | (r & S & (y & z))
 *   Proof:
 *    1> (x, y, z) are all 0s, (R & S) = ~(r | s) = 1, obviously X=Y=Z=1;
 *    2> (x, y, z) has at least one 1s, R & S = 0, will be ignored, hence we
 *       have:
 *         X = (R & s & (y | z)) | (r & S & (y & z))
 *    2.1> (x, y, z) has more than one 1s, R = ~r = 0, (R & s & (y | z)) = 0,
 *         will be ignored, hence we have:
 *           X = S & (y & z)
 *    2.1.1> (x, y, z) has three 1s, S = ~s = 0, obviously X=Y=Z=0;
 *    2.1.2> (x, y, z) has two 1s, S = ~s = 1, will be ignored, we have:
 *             X = y & z
 *    2.1.2.1> (y, z) has one 1, x = 1, X = y & z = 1 & 0 = 0
 *    2.1.2.2> (y, z) has two 1s, x = 0, X = y & z = 1 & 1 = 1
 *    2.2> (x, y, z) has one 1, r = 0, (r & S & (y & z)) = 0, will be ignored,
 *         we have:
 *           X = y | z
 *    2.2.1> (y, z) has one 1, x = 0, X = y | z = 1 | 0 = 1
 *    2.2.2> (y, z) has no 1s, x = 1, X = y | z = 0 | 0 = 0
 *   QED.
 *
 * Algorithm II:
 *   Internal Nodes:
 *   _2or3_1s = ((x & y) | (x & z) | (y & z));
 *   _0or1_1s = !(_2or3_1s);
 *   _1_1     = _0or1_1s & (x | y | z);
 *   _1or3_1s = _1_1 | (x & y & z);
 *   _0or2_1s = !(_1or3_1s);
 *   _0_1s    = _0or2_1s & _0or1_1s;
 *   _2_1s    = _0or2_1s & _2or3_1s;
 *
 *   Equations for Outputs:
 *   X = _0_1s | (_1_1 & (y | z)) | (_2_1s & (y & z));
 *   Y = _0_1s | (_1_1 & (x | z)) | (_2_1s & (x & z));
 *   Z = _0_1s | (_1_1 & (x | y)) | (_2_1s & (x & y));
 *
 * Analysis II:
 *   Almost the same as Analysis I.
 * ===========================================================================
 */

#include <stdio.h>

typedef unsigned int BOOL;

int main()
{
  int i, fail;
  BOOL x, y, z, X, Y, Z;
  BOOL r, R, s, S;
  BOOL _2or3_1s, _0or1_1s, _1_1,  _1or3_1s, _0or2_1s, _0_1s, _2_1s;

  /* ==================== Algorithm I ==================== */
  printf("Algorithm I:\n");
  fail = 0;
  for (i=0; i<8; i++) {
    /* Init x, y, z. */
    x = i & 1;
    y = (i>>1) & 1;
    z = (i>>2) & 1;
    /* Internal nodes. */
    r = (x & y) | (x & z) | (y & z);
    //R = !r;                               /* #1 NOT gate. */
    R = ~r & 1;                             /* #1 NOT gate. */
    s = (R & (x | y | z)) | (x & y & z);
    //S = !s;                               /* #2 NOT gate. */
    S = ~s & 1;                             /* #2 NOT gate. */
    /* Output. */
    X = (R & S) | (R & s & (y | z)) | (r & S & (y & z));
    Y = (R & S) | (R & s & (x | z)) | (r & S & (x & z));
    Z = (R & S) | (R & s & (x | y)) | (r & S & (x & y));

    if ((x == X) | (y == Y) | (z == Z)){
      fail ++;
      printf("FAIL: ");
    } else {
      printf("PASS: ");
    }
    printf("xyz = %u%u%u, XYZ = %u%u%u\n", x, y, z, X, Y, Z);
  }
  if (fail != 0) {
    printf("%d TEST FAILED!\n", fail);
  } else if (!fail) {
    printf("ALL TEST PASSED!\n");
  }

  /* ==================== Algorithm II ==================== */
  printf("Algorithm II:\n");
  fail = 0;
  for (i=0; i<8; i++) {
    /* Init x, y, z. */
    x = i & 1;
    y = (i>>1) & 1;
    z = (i>>2) & 1;
    /* Internal nodes. */
    _2or3_1s = ((x & y) | (x & z) | (y & z));
    //_0or1_1s = !(_2or3_1s);               /* #1 NOT gate. */
    _0or1_1s = ~(_2or3_1s) & 1;             /* #1 NOT gate. */
    _1_1     = _0or1_1s & (x | y | z);
    _1or3_1s = _1_1 | (x & y & z);
    //_0or2_1s = !(_1or3_1s);               /* #2 NOT gate. */
    _0or2_1s = ~(_1or3_1s) & 1;             /* #2 NOT gate. */
    _0_1s    = _0or2_1s & _0or1_1s;
    _2_1s    = _0or2_1s & _2or3_1s;
    /* Output. */
    X = _0_1s | (_1_1 & (y | z)) | (_2_1s & (y & z));
    Y = _0_1s | (_1_1 & (x | z)) | (_2_1s & (x & z));
    Z = _0_1s | (_1_1 & (x | y)) | (_2_1s & (x & y));

    if ((x == X) | (y == Y) | (z == Z)){
      fail ++;
      printf("FAIL: ");
    } else {
      printf("PASS: ");
    }
    printf("xyz = %u%u%u, XYZ = %u%u%u\n", x, y, z, X, Y, Z);
  }
  if (fail != 0) {
    printf("%d TEST FAILED!\n", fail);
  } else if (!fail) {
    printf("ALL TEST PASSED!\n");
  }
  return 0;
}

