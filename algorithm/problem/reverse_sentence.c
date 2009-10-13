/* ===========================================================================
 * Problem: 
 *   Reverse the words in a sentence, exclude punctuations(at the end of
 *   sentence).
 *   i.e.
 * i:The Irish National Liberation Army announces an end to its armed campaign.
 * o:campaign armed its to end an announces Army Liberation National Irish The.
 * ===========================================================================
 */
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

void reverse(char *str, size_t lft, size_t rht)
{
  char a;
  if (str == NULL) return;
  while(lft < rht) {
    a = str[lft]; str[lft] = str[rht]; str[rht] = a;
    ++lft;
    --rht;
  }
}

void reverse_sentence(char *str)
{
  if (str == NULL) return;
  size_t len = strlen(str);
  reverse(str, 0, len-2);
  char *p, *q;
  p = str;
  while(q = strchr(p, ' ')) {
    reverse(p, 0, q-p-1);
    p = q+1;
  }
  reverse(p, 0, &str[len-1]-p-1);
}

int main()
{
  char a[] = "The Irish National Liberation Army announces an end to its\
 armed campaign.";
  printf("%s\n", a);
  reverse_sentence(a);
  printf("%s\n", a);
}
