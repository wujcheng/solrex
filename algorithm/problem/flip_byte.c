#include <stdio.h>

unsigned char flip(unsigned char x)
{
  x = (x & 0x0f) << 4 | (x & 0xf0) >>4;
  x = (x & 0x33) << 2 | (x & 0xcc) >>2;
  x = (x & 0x55) << 1 | (x & 0xaa) >>1;
  return x;
}

int main()
{
  printf("%x\n", flip(0x93));
}
