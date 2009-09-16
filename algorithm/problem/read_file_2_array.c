#include <stdio.h>
#include <stdlib.h>

char ** read_file_2_array(const char * fname)
{
  FILE * fp = fopen(fname, "r");
  if (fp == NULL)   return NULL;
  int lines = 1, n = 1000;
  char **ret = (char **) malloc(lines*sizeof(char *));
  if (ret == NULL) return NULL;
  char *p = (char *) malloc(n*sizeof(char));
  if (p == NULL) return NULL;
  while (getline(&p, &n, fp) != EOF) {
    ret[lines-1] = p;
    lines++;
    ret = (char **) realloc(ret, lines*sizeof(char *));
    if (ret == NULL) return NULL;
    p = (char *) malloc(n*sizeof(char));
    if (p == NULL) return NULL;
  }
  return ret;
}

int main()
{
  char **a = read_file_2_array("atoi.c"); 
  while (*a != NULL) {
    printf("%s", *a);
    a++;
  }
}
