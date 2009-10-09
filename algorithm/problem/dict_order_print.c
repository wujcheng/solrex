/* ==========================================================================
 * Problem: 
 *   Print 1 to input A, in dictionary order.
 *
 * Example:
 *   Input 120, output:
 * 1
 * 10
 * 100
 * 101
 * 102
 * …
 * 109
 * 11
 * 110
 * 111
 * 112
 * …
 * 119
 * 12
 * 120
 * 13
 * ..
 * 19
 * 2
 * 20
 * 21
 * …
 * 29
 * 3
 * …
 * 4
 * …
 * 9
 * 90
 * 91
 * …
 * 99
 * ==========================================================================
 */

#include <stdio.h>
#include <stdlib.h>

void print1m(int m, int n) 
{ 
  int i;
  if (m > n)  return; 
  for (i = 0; i < 10; i++) { 
    if((m+i > 0)  && (m+i <= n)) { 
      printf("%d\n", m + i); 
      print1m((m + i) * 10, n); 
    } 
  } 
} 

void print1(int n) 
{ 
  print1m(0, n); 
}

int next(int a,int n){
  if (a*10 <= n) return a*10;
  if (a%10 < 9) return a + 1;
  ++a;
  while(a%10 == 0) a /= 10;
  return a;
}

void print2(int n){
  int a = 1;
  int i = 1;
  printf("%d\n", a);
  while (i<n){
    a = next(a, n);
    if (a <= n){
      printf("%d\n", a);
      i++;
    }
  }
}



int main(int argc, char ** argv)
{
  if (argc != 2) {
    printf("Usage: ./a.out 132\n");
    return 0;
  }
  print1(atoi(argv[1]));
}

