#include <stdio.h>

int atoi(const char *str)
{
  int i = 0;
  char sign = '+';
  while (*str == ' ') str++;
  if (*str == '-' || *str == '+')
    sign = *str++;
  while(isdigit(*str)) {
    i = i*10 + *str - '0';
    str++;
  }
  return sign == '-' ? -i : i;
}

int main()
{
  char a[] = "214532525";
  char b[] = "-214532525";
  printf("%d, %d", atoi(a), atoi(b));
}
