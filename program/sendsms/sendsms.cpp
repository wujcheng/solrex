#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <argp.h>
#include "include/libfetion.h"

const char *argp_program_version = "sendsms 0.1";
const char *argp_program_bug_address = "<solrex@gmail.com>";

/* Program documentation. */
static char doc[] = "Options:";

/* A description of the arguments we accept. */
static char args_doc[] = "MESSAGE";

/* The options we understand. */
static struct argp_option options[] = {
  {"from",   'f', "SENDER",    0, "The sender's fetion/phone number." },
  {"passwd", 'p', "PASSWD",    0, "The sender's password." },
  {"to",     't', "RECEIVER",  0, "The receiver's fetion/phone number." },
  { 0 }
};
 
/* Used by main to communicate with parse_opt. */
typedef struct _args {
  char *from;
  char *passwd;
  char *to;
  char *message;
} ARGUMENTS;

/* Parse a single option. */
static error_t
parse_opt (int key, char *arg, struct argp_state *state)
{
/* Get the input argument from argp_parse, which we know is a pointer to our
 * arguments structure. */
  ARGUMENTS *p_args = (ARGUMENTS *)state->input;
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
    case ARGP_KEY_ARG:
      if (state->arg_num > 1)   /* We have only one argument: MESSAGE. */
        argp_usage (state);
      p_args->message = arg;
    break;
    case ARGP_KEY_NO_ARGS:      /* The MESSAGE argument can not be ignored. */
      argp_usage (state);
    break;
    case ARGP_KEY_END:
      if (state->arg_num < 1)    /* The MESSAGE argument can not be ignored. */
        argp_usage(state);
    break;

    default:
      return ARGP_ERR_UNKNOWN;
  }
  return 0;
}

static struct argp p_argp = {options, parse_opt, args_doc, doc};

int main(int argc, char** argv)
{
  ARGUMENTS args;
  int ret;

  /* Default values. */
  //args.from = "136xxxxx";
  //args.passwd = "xxxx";
  args.to = NULL;

 /* Parse our arguments; every option seen by parse_opt will be reflected
  * in arguments. */
  argp_parse (&p_argp, argc, argv, 0, 0, &args);

  if (!fx_init()) {
    fprintf(stderr, "Failed to init.\n");
    return 1;
  }
  ret = fs_login(args.from, args.passwd);
  if (!ret) {
    fprintf(stderr, "Failed to login.\n");
    return ret;
  }
  if (args.to == NULL) {
    ret = fs_send_sms_to_self(args.message);
  } else {
    if (strncmp(args.to, "13", 2) == 0) {
      ret = fs_send_sms_by_mobile_no(args.to, args.message);
    } else {
      long int uid = strtol(args.to, NULL, 10);
      ret = fs_send_sms(uid, args.message);
    }
  }
  if (!ret) {
    fprintf(stderr, "Failed to send.\n");
    return ret;
  }
  //fx_loginout();
  fx_close_network();
  fx_terminate();
  return 0;
}
