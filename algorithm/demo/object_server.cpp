/* COMPILE: g++ objserver.cpp -o objserver -lpthread */

#include <queue>
#include <pthread.h>
#include <unistd.h>
using namespace std;

#define MAX_OBJECTS  5              /* number of objects */
#define MAX_THREADS  10             /* number of threads */
#define TID0_TID     0              /* thread_per_obj thread id */
#define CALL_OID     (MAX_OBJECTS)  /* pseudo object id of call() thread */
#define TID0_OID     0              /* thread_per_obj thread object id */

typedef int THREAD_POLICY;          /* thread usage policy */
#define THREAD_PER_OBJ 0            /* 0: 1 thread per 1 object */
#define THREAD_PER_REQ 1            /* 1: 1 thread per 1 request */

/* Message format, we simplified it in our case. */
struct MESSAGE {
  int source;
  int object_id;
  int thread_id;
  int data;
};

/* Thread information structure. */
struct THREAD {
  int   tid;            /* Thread id in our case */
  int   oid;            /* Object id for the thread */
  pthread_t posix_tid;  /* POSIX thread id(the real Linux thread id) */
};

/* Object structure in our object server. */
class object {
public:
  int  oid;
  void stub(MESSAGE req, MESSAGE &res) {
    res = req;
  }
};

object      objects[MAX_OBJECTS];   /* objects in object server */
THREAD      threads[MAX_THREADS];   /* threads for object adapter */
MESSAGE     mes_pool[MAX_OBJECTS];  /* message buffers for communication */

queue<int>  object_queue;   /* object index queue for object assignment */
queue<int>  thread_queue;   /* thread index queue for thread assignment */

int         pipe_call[2], pipe_tid0[2]; /* pipe for communication */

/* Get message function */
void get_msg (THREAD_POLICY policy, int oid, MESSAGE &data) {
  if (policy == THREAD_PER_OBJ) {
    if (oid == TID0_OID) {
      read(pipe_tid0[0], &data, sizeof(data));
    } else {
      read(pipe_call[0], &data, sizeof(data));
    }
  } else {
    data = mes_pool[oid];
  }
}

/* Put message function */
void put_msg (THREAD_POLICY policy, int oid, MESSAGE &data) {
  if (policy == THREAD_PER_OBJ) {
    if (oid == TID0_OID) {
      write(pipe_tid0[1], &data, sizeof(data));
    } else {
      write(pipe_call[1], &data, sizeof(data));
    }
  } else {
    mes_pool[oid] = data;
  }
}

/* Exit thread, */
void thread_exit(THREAD *thread) {
  thread_queue.push(thread->tid);
  object_queue.push(thread->oid);
  pthread_exit(NULL);
}

/* Object adapter implemented ONE THREAD PER OBJECT policy. */
void * thread_per_object (void *arg) {
  THREAD *thread = (THREAD *)arg;
  MESSAGE req, res;
  while (true) {
    get_msg (THREAD_PER_OBJ, TID0_OID, req);
    req.object_id = thread->oid;
    req.thread_id = thread->tid;
    objects[thread->oid].stub(req, res);
    put_msg (THREAD_PER_OBJ, CALL_OID, res);
  }
  thread_exit(thread);
}

/* Object adapter implemented ONE THREAD PER REQUEST policy. */
void * thread_per_request (void *arg) {
  THREAD *thread = (THREAD *)arg;
  MESSAGE req, res;
  get_msg (THREAD_PER_REQ, thread->oid, req);
  req.object_id = thread->oid;
  req.thread_id = thread->tid;
  objects[thread->oid].stub(req, res);
  put_msg (THREAD_PER_REQ, thread->oid, res);
  thread_exit(thread);
}

/* Helper function to create a thread. */
THREAD * create_thread(THREAD_POLICY policy, MESSAGE *request) {
  if(policy == THREAD_PER_OBJ) {
    threads[TID0_TID].oid = TID0_OID;
    pthread_create(&threads[TID0_TID].posix_tid, NULL, &thread_per_object,
                   &threads[TID0_TID]);
    return &threads[TID0_TID];
  } else {
    int i, j;
    i = thread_queue.front();
    thread_queue.pop();
    j = object_queue.front();
    object_queue.pop();
    threads[i].oid = j;
    put_msg(THREAD_PER_REQ, threads[i].oid, *request);
    pthread_create(&threads[i].posix_tid, NULL, &thread_per_request,
                   &threads[i]);
    return &threads[i];
  }
}

/* Request demultiplexer: If the source of request is an even number, we use
 * the ONE THREAD PER OBJECT object adapter; else, we use the ONE THREAD PER
 * REQUEST object adapter. */
MESSAGE call(MESSAGE &request) {
  THREAD   *thread; 
  MESSAGE  result;
  if (request.source%2 == 0) {
    put_msg(THREAD_PER_OBJ, TID0_OID, request);
    get_msg(THREAD_PER_OBJ, CALL_OID, result);
  } else {
    thread = create_thread(THREAD_PER_REQ, &request);
    pthread_join(thread->posix_tid, NULL);
    get_msg(THREAD_PER_REQ, thread->oid, result);
  }
  return result;
}

/* Initialize object server. */
void init_objserver() {
  int i;
  if (pipe(pipe_call) < 0 || pipe(pipe_tid0) < 0)
    perror("pipe() error");
  for (i=1; i<MAX_THREADS; i++)
    threads[i].tid = i;
  for (i=1; i<MAX_THREADS; i++)
    thread_queue.push(i);
  for (i=1; i<MAX_OBJECTS; i++)
    objects[i].oid = i;
  for (i=1; i<MAX_OBJECTS; i++)
    object_queue.push(i);
  create_thread(THREAD_PER_OBJ, NULL);
}

int main()
{
  /* Test object server. */
  MESSAGE request, result;
  init_objserver();
  printf("### Our object server has 5 objects, and maximum 10 threads. ###\n");
  printf("Request demultiplexing policy is:\n");
  printf("  Request from even source will be served by THREAD PER OBJECT.\n");
  printf("  Request from odd source will be served by THREAD PER REQUEST.\n");
  printf("============= TEST START =============\n");
  for (int i=0; i<30; i++) {
    request.source = i;
    result = call(request);
    printf("Request from %2d was served by thread %d and object %d\n",
           result.source, result.thread_id, result.object_id);
  }
  return 0;
}
