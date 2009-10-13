#include <stdio.h>
#include <assert.h>

void * memcpy(void *dst, const void *src, size_t count)
{
  char *from =  (char *)src;
  char *to = (char *)dst;
  assert((src!=NULL) && (dst!=NULL));
  assert((to>=from+count) || (from>=to+count));
  while(count--) *to++ = *from++;
  return dst;
}

int main()
{
  char a[] = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  char b[] = "ABCDEFGHIJKLMNOPQRSTUVWXYZAAAA";
  char c[] = "                              ";
  printf("a=\"%s\"\nc=\"%s\"\n", a, c);
  memcpy(c, a, 26*sizeof(char));
  printf("a=\"%s\"\nc=\"%s\"\n", a, c);
  return 0;
}
