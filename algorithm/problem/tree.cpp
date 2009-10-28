#include <iostream>
#include <stack>
#include <queue>
#include <cstdlib>
#include <ctime>

using namespace std;

typedef struct _tNode {
  int data;
  struct _tNode *left, *right;
} tNode;

void pre_order(tNode *root)
{
  if (root == NULL) return;
  printf("%d ", root->data);
  pre_order(root->left);
  pre_order(root->right);
}

void pre_order_nrec(tNode *root)
{
  if (root == NULL) return;
  tNode *p;
  stack <tNode *> s;
  s.push(root);
  while (!s.empty()) {
    p = s.top();
    s.pop();
    while (p != NULL) {
      printf("%d ", p->data);
      s.push(p->right);
      p = p->left;
    }
  }
}

void post_order(tNode *root)
{
  if (root == NULL) return;
  post_order(root->left);
  post_order(root->right);
  printf("%d ", root->data);
}

void post_order_nrec(tNode *root)
{
  if (root == NULL) return;
  stack < pair<tNode *, bool> > s;
  bool visited = true;
  tNode *p = root;
  while (p != NULL) {
    s.push(make_pair(p, false));
    p = p->left;
  }
  while (!s.empty()) {
    p = s.top().first;
    visited = s.top().second;
    s.pop();
    if (visited == false) {
      s.push(make_pair(p, true));
      p = p->right;
      while (p != NULL) {
        s.push(make_pair(p, false));
        p = p->left;
      }
    } else {
      printf("%d ", p->data);
    }
  }
}
void in_order(tNode *root)
{
  if (root == NULL) return;
  in_order(root->left);
  printf("%d ", root->data);
  in_order(root->right);
}

void in_order_nrec(tNode *root)
{
  if (root == NULL) return;
  tNode *p;
  stack <tNode *> s;
  p = root;
  while (p || !s.empty()) {
    if (p) {
      s.push(p);
      p = p->left;
    } else { 
      p = s.top();
      s.pop();
      printf("%d ", p->data);
      p = p->right;
    }
  }
}

int bst_insert(tNode **root, int key)
{
  tNode *node = (tNode *) calloc(1, sizeof(tNode));
  tNode *p, *q;
  if (node == NULL) return -1;
  node->data = key;
  node->left = NULL;
  node->right = NULL;
  if (*root == NULL) {
    *root = node;
    return 0;
  }
  p = *root;
  while (p != NULL) {
    if (key == p->data) return 0;
    else {
      q = p;
      p = (key > p->data) ? p->right : p->left;
    }
  } 
  if (key > q->data) {
    q->right = node;
  } else {
    q->left = node;
  }
  return 0;
}

int main(int argc, char **argv)
{
  int i;
  srand((unsigned int)time(NULL));
  tNode *root = NULL;
  
  for (i=0; i<20; i++) {
    bst_insert(&root, random()%100);
  }
  pre_order(root);
  printf("\n");
  pre_order_nrec(root);
  printf("\n");
  in_order(root);
  printf("\n");
  in_order_nrec(root);
  printf("\n");
  post_order(root);
  printf("\n");
  post_order_nrec(root);
  printf("\n");
}


