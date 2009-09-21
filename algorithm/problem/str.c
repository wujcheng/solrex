#include <stdio.h>
#include <assert.h>
#include <stdlib.h>

char * strcpy(char *dst, const char *src)
{
  char *save = dst;
  assert((src!=NULL) && (dst!=NULL));
  while(*dst++ = *src++) ;
  return save;
}

char * strncpy(char *dst, const char *src, size_t num)
{
  int i;
  assert((dst!=NULL) && (src!=NULL));
  for (i=0; dst[i]=src[i], i<num; i++) ;
  return dst;
}

int strcmp(const char *s1, const char *s2)
{
  assert((s1!=NULL) && (s2!=NULL));
  for(; *s1==*s2; s1++, s2++)
    if (*s1==0) return 0;
  return *(unsigned char *)s1<*(unsigned char *)s2 ? -1 : 1;
}

int strncmp(const char *s1, const char *s2, size_t num)
{
  assert((s1!=NULL) && (s2!=NULL));
  int i;
  for(i=0; i<num && s1[i]==s2[i]; i++) ;
  if (i==num) return 0;
  return *(unsigned char *)s1<*(unsigned char *)s2 ? -1 : 1;
}

int strlen(const char *s)
{
  const char *p=s;
  assert(p!=NULL);
  for(; *p; ++p);
  return (p-s);
}

int strstr(const char *haystack, const char *needle)
{
  int nlen = strlen(needle);
  int hlen = strlen(haystack);
  int * next = (int *) malloc(nlen*sizeof(int));
  if (next == NULL) perror("NULL");
  int i, j;
  next[0] = -1; next[1] = 0; i = 2; j = 0;
  while(i<nlen) {
   if (needle[i-1]==needle[j]) {
     next[i++] = ++j;
   } else if (j>0){
     j = next[j];
   } else {
     next[i++] = 0;
   }
  }
  for(i=0; i<nlen; i++) {
    printf("%d", next[i]);
  }
  printf("\n"); fflush(NULL);
  i = 0; j = 0;
  while (i+j<hlen) {
    if (needle[j]==haystack[i+j]) {
      j++;
      if (j==nlen) return i;
    } else {
      i = i + j - next[j];
      j = j>0 ? next[j] : j;
    }
  }
  return -1;
}

int main()
{
  char a[] = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  char b[] = "ABCDEFGHIJKLMNOPQRSTUVWXYZAAAA";
  char c[] = "                              ";
  char d[] = "ABABABCDDEFFASDGGDBFBFDLFDPCDEFGHHILL";
  printf("a=\"%s\"\nb=\"%s\"\n", a, b);
  printf("strlen(a)=%d\n", strlen(a));
  printf("strcmp(a,b)=%d\n", strcmp(a,b));
  printf("strncmp(a,b,26)=%d\n", strncmp(a,b,26));
  printf("strncmp(a,b,27)=%d\n", strncmp(a,b,27));
  printf("strncmp(a,b,50)=%d\n", strncmp(a,b,50));
  printf("a=\"%s\"\nb=\"%s\"\n", a, b);
  strcpy(b, a);
  printf("strncmp(a,b,50)=%d\n", strncmp(a,b,50));
  printf("a=\"%s\"\nc=\"%s\"\n", a, c);
  strncpy(c, a, 15);
  printf("a=\"%s\"\nc=\"%s\"\n", a, c);
  char e[] = "123112345132143143214321";
  printf("strstr(d, \"AB\")=%d\n", strstr(d, "AB"));
  printf("strstr(d, \"ABAB\")=%d\n", strstr(d, "ABAB"));
  printf("strstr(d, \"DEF\")=%d\n", strstr(d, "DEF"));
  printf("strstr(d, \"ABAB\")=%d\n", strstr(d, "123412345"));
  printf("strstr(d, \"%s\")=%d\n", e, strstr(d, e));
  return 0;
}
