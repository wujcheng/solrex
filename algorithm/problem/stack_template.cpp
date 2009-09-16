#include <iostream>
#include <vector>
using namespace std;

template <typename T> class Stack 
{
private:
  vector < T > data;
public:

  void push(const T &t) { data.push_back(t);}
  void pop() {  data.pop_back(); }
  T&   top() { return data.back(); }

  void clear() { data.clear(); }
  bool is_empty() { return data.empty();}   
};

int main()
{
  Stack <int> s;
  s.push(1);
  s.push(2);
  cout << s.top() << endl;
  s.pop();
  cout << s.top() << endl;
  s.push(4);
  s.push(5);
  cout << s.top() << endl;
  s.clear();
  cout << s.is_empty() << endl;
  return 0;
}
