#include <stdio.h>
#include <stdlib.h>
#include <string.h>

char ** text_2_array(const char *filename)
{
  char *p, **array;
  int lines;
  if(filename == NULL) return NULL;

  FILE *fp = fopen(filename, "r");
  if(fp == NULL) return NULL;

  /* Get file size. */
  fseek(fp, 0L, SEEK_END);
  long int f_size = ftell(fp);
  fseek(fp, 0L, SEEK_SET);

  /* Allocate space for file content. */
  char *buf = (char *) calloc(f_size, sizeof(char));
  if(buf == NULL) return NULL;

  fread(buf, sizeof(char), f_size, fp);
  fclose(fp);

  /* Get number of lines. */
  for(p=strchr(buf, '\n'), lines=1; p!=NULL; p=strchr(p, '\n'), lines++) {
    if(*p == '\n') p++;
  }

  /* Allocate space for array; split file buffer to lines by change '\n' to
     '\0'. */
  array = (char **) calloc(lines+1, sizeof(char*));
  array[0] = buf;
  for(p=strchr(buf, '\n'), lines=1; p!=NULL; p=strchr(p, '\n')) {
    if(*p == '\n') *p++ = '\0';
    if(p != NULL) array[lines++] = p;
  }
  /* Add a terminate NULL pointer. */
  array[lines] = NULL;
  return array;
}

int main()
{
  char **a = text_2_array("text_2_array.c");
  while(*a != NULL) {
    printf("%s\n", *a);
    a++;
  }
  return 0;
}
