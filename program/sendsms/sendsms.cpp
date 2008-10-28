#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <argp.h>
#include "include/libfetion.h"

/* Version, contact, user defined doc, argument doc strings for argp.h. */
const char *argp_program_version = "sendsms 0.1";
const char *argp_program_bug_address = "<solrex@gmail.com>";
static char doc[] = "Options:";
static char args_doc[] = "MESSAGE";

/* Program options we understand. */
static struct argp_option options[] = {
  {"from",   'f', "SENDER",    0, "The sender's fetion/phone number." },
  {"passwd", 'p', "PASSWD",    0, "The sender's password." },
  {"to",     't', "RECEIVER",  0, "The receiver's fetion number." },
  { 0 }
};
 
/* Used by |main| to communicate with |parse_opt|. */
typedef struct _args {
  char *from;       /* Sender's phone/fetion number string pointer. */
  char *passwd;     /* Sender's password string pointer. */
  char *to;         /* Receiver's phone/fetion number string pointer. */
  char *message;    /* SMS message body pointer. */
} ARGUMENTS;

/* Parse a single option. */
static error_t
parse_opt (int key, char *arg, struct argp_state *state)
{
  /* Get the input argument from |argp_parse|, which we know is a pointer to
   * our |ARGUMENTS| structure. */
  ARGUMENTS *p_args = (ARGUMENTS *)state->input;
  /* Parse option key. */
  switch (key) {
    case 'f':
      p_args->from = arg;
    break;
    case 'p':
      p_args->passwd = arg;
    break;
    case 't':
      p_args->to = arg;
    break;
    case ARGP_KEY_ARG:   /* We have only one(none-option) argument: MESSAGE. */
      if (state->arg_num > 1)   
        argp_usage (state);
      p_args->message = arg;
    break;
    case ARGP_KEY_NO_ARGS:      /* The MESSAGE argument can not be ignored. */
      argp_usage (state);
    break;
    case ARGP_KEY_END:
      if (state->arg_num < 1)   /* The MESSAGE argument can not be ignored. */
        argp_usage(state);
    break;

    default:
      return ARGP_ERR_UNKNOWN;
  }
  return 0;
}

/* |argp| structure used by |argp_parse| function. */
static struct argp p_argp = {options, parse_opt, args_doc, doc};

int main(int argc, char** argv)
{
  ARGUMENTS args;
  int ret;
  long int uid;

  /* Default option values. */
#if 0
  args.from = "136xxxxxxxx";
  args.passwd = "*********";
#endif
  args.to = NULL;

  /* Parse our arguments; every option seen by |parse_opt| will be reflected
   * in |args|. */
  argp_parse (&p_argp, argc, argv, 0, 0, &args);

  if (!fx_init()) {                         /* Init libfetion. */
    fprintf(stderr, "Failed to init.\n");
    return 1;
  }
  ret = fs_login(args.from, args.passwd);   /* Login with id and passwd. */
  if (!ret) {
    fprintf(stderr, "Failed to login.\n");
    return ret;
  }
  /* If "-t" option is ignored, send the MESSAGE to the SENDER-self. */
  if (args.to == NULL) {
    ret = fs_send_sms_to_self(args.message);
  } else {
    /* If "-t" option is a mobile phone number, use API: 
     * |fs_send_sms_by_mobile_no|. */
    /* FIXME!/Wenbo-20081028: It doesn't work with libfetion 0.81. Maybe a
     * bug exsits, so we can only use fetion num as the value of RECEIVER. */
    if (strncmp(args.to, "13", 2) == 0) {
      ret = fs_send_sms_by_mobile_no(args.to, args.message);
    } else {
      /* If "-t" option is a fetion number, use API: |fs_send_sms|. */
      uid = strtol(args.to, NULL, 10);
      ret = fs_send_sms(uid, args.message);
    }
  }
  if (!ret) {
    fprintf(stderr, "Failed to send.\n");
    return ret;
  }
  /* Logout, disconnect and release resources. */
  /* FIXME!/Wenbo-20081028: |fx_loginout| doesn't work in this case. */
  //fx_loginout();
  fx_close_network();
  fx_terminate();
  return 0;
}
