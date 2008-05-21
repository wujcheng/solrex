/* COMPILE: gcc lamport_snapshot.c -o lamport_snapshot */

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>

#define MAX_LEN (9)

int main(void) 
{ 
  int   p2q[2], q2p[2];
  int   count, state, snapshot, temp;
  pid_t pid; 
  char  mes[MAX_LEN];

  /* Open 2 PIPE for a cyclic communication channel. */
  if (pipe(p2q) < 0 || pipe(q2p) < 0)
    perror("pipe() error");
  /* Fork a new process, then we have 2 processes, one P, the other Q. */
  if ((pid = fork()) < 0) { 
    perror("fork() error"); 
  } else if (pid == 0) {
    /************************* child process(P) ***************************/

    /* Initialize state variables. */
    count = 0;          /* Message receive counter. */
    state = -1;         /* Process state saver. */
    snapshot = 0;       /* Snapshot started? */

    /* Close useless end of PIPEs. */
    close(p2q[0]);
    close(q2p[1]);

    /* P starts sending the message m, writes to PIPE 1. */
    write(p2q[1], "MESSAGE.", MAX_LEN);

    /* Loop start. */
    while (1) {
      read(q2p[0], mes, MAX_LEN);   /* Read from PIPE 2. */

      if (strncmp(mes, "MESSAGE.", 8) == 0) {
        count++;    /* Received m, counter increases. */
      }

      /* If we got a ANS message from Q, record the state. */
      if (strncmp(mes, "ANS", 3) == 0) {
        printf("P got the answer message: \"%s\"\n", mes);
        temp = atoi(mes+3);
        printf("############ Snapshot completed in P ############\n");
        printf("Consistent global states are:\n");
        printf("\tState of process P: %d\n", state);
        printf("\tState of process Q: %d\n", temp);
        fflush(NULL);
        break;
      } else if (mes[8] == 'T') {
        /* Receive the snapshot token 'T' for the first time, sends the
         * observer process its own saved state. We use 5 digits to represent
         * an decimal number, in our case that is enough. */
        if (snapshot == 0) {
          /* IT IS NEVER GOING TO HAPPEN IN OUR CASE. */
          state = count;
          mes[0] = 'A'; mes[1] = 'N'; mes[2] = 'S';
          mes[3] = '0' + (state/10000%10);
          mes[4] = '0' + (state/1000%10);
          mes[5] = '0' + (state/100%10);
          mes[6] = '0' + (state/10%10);
          mes[7] = '0' + (state%10);
          mes[8] = '\0';
          write(q2p[1], mes, MAX_LEN);
          snapshot = 1;
          continue;
        }
      }
      /* Received the snapshot token 'T', spread it. */
      if (snapshot == 1) mes[8] = 'T';
      
      write(p2q[1], mes, MAX_LEN);  /* Send message 'm' to Q. */

      if (count == 101) {           /* Start snapshot when counting to 101. */
        state = count;
        snapshot = 1;
        write(p2q[1], "REQUEST.T", MAX_LEN);
        printf("############# Snapshot started by P #############\n");
        printf("P sent request message with token 'T': \"REQUEST.T\"\n");
      }
    }
  } else {                   
    /************************* parent process(Q) ***************************/

    /* Initialize state variables. */
    count = 0;          /* Message receive counter. */
    state = -1;         /* Process state saver. */
    snapshot = 0;       /* Snapshot started? */

    /* Close useless end of PIPEs. */
    close(p2q[1]);
    close(q2p[0]);

    /* Loop start. */
    while (1) {
      read(p2q[0], mes, MAX_LEN);   /* Read from PIPE 1. */

      if (strncmp(mes, "MESSAGE.", 8) == 0) {
        count++;    /* Received m, counter increases. */
      } 

      /* If we got a ANS message from P, record the state. */
      if (strncmp(mes, "ANS:", 4) == 0) {
        /* IT IS NEVER GOING TO HAPPEN IN OUR CASE. */
      } else if (mes[8] == 'T') {
        /* Receive the snapshot token 'T' for the first time, send the
         * observer process its own saved state. We use 5 digits to represent
         * an decimal number, in our case that is enough. */
        if (snapshot == 0) {
          printf("Q received message with token 'T' for the 1st time.\n");
          state = count;
          mes[0] = 'A'; mes[1] = 'N'; mes[2] = 'S';
          mes[3] = '0' + (state/10000%10);
          mes[4] = '0' + (state/1000%10);
          mes[5] = '0' + (state/100%10);
          mes[6] = '0' + (state/10%10);
          mes[7] = '0' + (state%10);
          mes[8] = '\0';

          printf("Q sent answer to observer P with its state: \"%s\".\n", mes);
          fflush(NULL);

          write(q2p[1], mes, MAX_LEN);
          snapshot = 1;
          break;    /* Process Q's work completed in our case. */
        }
      }

      /* Received the snapshot token 'T', spread it. */
      if (snapshot == 1) mes[8] = 'T';

      write(q2p[1], mes, MAX_LEN);  /* Send message 'm' to Q. */
    }
  } 
  return 0;
} 

