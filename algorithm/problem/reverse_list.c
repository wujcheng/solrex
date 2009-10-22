/* Using iteration to reverse list. */
#include <stdio.h>
#include <stdlib.h>

const int N=16;

typedef struct _Node {
  int n;
  struct _Node *next;
} Node;


Node * reverse_iter(Node *head)
{
  if (head == NULL) return head;
  Node *p = head->next;
  head->next = NULL;
  if (p != NULL) {
    Node *q = reverse_iter(p);
    p->next = head;
    return q;
  } else {
    return head;
  }
}

int main()
{
  int i;
  Node *p=NULL, *head=NULL;
  for (i=0; i<N; i++) {
    p = malloc(sizeof (Node));
    p->n = i;
    if (head==NULL) head = p;
    else {
      p->next = head;
      head = p;
    }
  }
  for (p=head, i=0; p!=NULL; p=p->next, i++) {
    printf("%d\t", p->n);
    if (((i+1)%8) == 0) printf("\n");
  }
  printf("\n");
  head = reverse_iter(head);
  for (p=head, i=0; p!=NULL; p=p->next, i++) {
    printf("%d\t", p->n);
    if (((i+1)%8) == 0) printf("\n");
  }
  return 0;
}
