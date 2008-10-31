#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <argp.h>
#include <unistd.h>
#include "include/libfetion.h"

#define MAX_RETRY 5

/* Version, contact, user defined doc, argument doc strings for argp.h. */
const char *argp_program_version = "sendsms 0.1";
const char *argp_program_bug_address = "<solrex@gmail.com>";
static char doc[] = "Options:";
static char args_doc[] = "MESSAGE";

/* Program options we understand. */
static struct argp_option options[] = {
  {"from",   'f',   "SENDER",    0, "The sender's fetion/phone number." },
  {"passwd", 'p',   "PASSWD",    0, "The sender's password." },
  {"to",     't', "RECEIVER",    0, "The receiver's fetion number." },
  {"verbose",'v',          0,    0, "Print verbose information." },
  { 0 }
};
 
/* Used by |main| to communicate with |parse_opt|. */
typedef struct _args {
  char *from;       /* Sender's phone/fetion number string pointer. */
  char *passwd;     /* Sender's password string pointer. */
  char *to;         /* Receiver's phone/fetion number string pointer. */
  char *message;    /* SMS message body pointer. */
  BOOL  verbose;
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
    case 'v':
      p_args->verbose = TRUE;
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
  PROXY_ITEM proxy;
  char *proxyenv = NULL, *p;
  int ret, i;
  long int uid;

  /* Default option values. */
#if 0
  args.from = "136xxxxxxxx";
  args.passwd = "*********";
#endif
  args.to = NULL;
  args.verbose = FALSE;

  /* Parse our arguments; every option seen by |parse_opt| will be reflected
   * in |args|. */
  argp_parse (&p_argp, argc, argv, 0, 0, &args);

  if (!fx_init()) {                         /* Init libfetion. */
    fprintf(stderr, "FAIL: init().\n");
    return 1;
  } else if (args.verbose == TRUE) {
    fprintf(stderr, "PASS: init().\n");
  }

  /* Read environment variable "http_proxy". */
  if (proxyenv = getenv("http_proxy")) {
    proxyenv = strdup(proxyenv);
    if (strncmp(proxyenv, "http://", 7) == 0) {
      if (p = strchr(proxyenv, '@')) {
        *p = '\0';
        proxy.host = p + 1;
        proxy.name = proxyenv + 7;
        if (p = strchr(proxy.name, ':')) {
          *p = '\0';
          proxy.pwd = p + 1;
        }
      } else {
        proxy.host = proxyenv + 7;
        proxy.name = NULL;
        proxy.pwd = NULL;
      }
      if (p = strchr(proxy.host, ':')) {
        *p = '\0';
        proxy.port = p + 1;
      }
      proxy.type = PROXY_HTTP;
      /* Set http proxy. */
      fx_set_proxy(&proxy);
    }
  }

  fx_set_login_status(FX_STATUS_OFFLINE);   /* Set status offline. */
  for (i=0; i<=MAX_RETRY; i++) {
    ret = fs_login(args.from, args.passwd);
    if (ret) break;
    else sleep(1);
  }
  i++;
  if (!ret) {
    fprintf(stderr, "FAIL: %s login() after %d tries.\n", args.from, i);
    return 2;
  } else if (args.verbose == TRUE) {
    fprintf(stderr, "PASS: %s login() after %d tries.\n", args.from, i);
  }
  /* If "-t" option is ignored, send the MESSAGE to the SENDER-self. */
  if (args.to == NULL) {
    args.to = args.from;
    for (i=0; i<=MAX_RETRY; i++) {
      ret = fs_send_sms_to_self(args.message);
      if (ret) break;
      else sleep(1);
    }
  } else {
    /* If "-t" option is a mobile phone number, use API: 
     * |fs_send_sms_by_mobile_no|. */
    /* FIXME!/Wenbo-20081028: It doesn't work with libfetion 0.81. Maybe a
     * bug exsits, so we can only use fetion num as the value of RECEIVER. */
    if (strncmp(args.to, "13", 2) == 0) {
      for (i=0; i<=MAX_RETRY; i++) {
        ret = fs_send_sms_by_mobile_no(args.to, args.message);
        if (ret) break;
        else sleep(1);
      }
    } else {
      /* If "-t" option is a fetion number, use API: |fs_send_sms|. */
      uid = strtol(args.to, NULL, 10);
      for (i=0; i<=MAX_RETRY; i++) {
        ret = fs_send_sms(uid, args.message);
        if (ret) break;
        else sleep(1);
      }
    }
  }
  i++;
  if (!ret) {
    fprintf(stderr, "FAIL: send_sms() from %s to %s after %d tries.\n",
            args.from, args.to, i);
    return 3; 
  } else if (args.verbose == TRUE) {
    fprintf(stderr, "PASS: send_sms() from %s to %s after %d tries.\n",
            args.from, args.to, i);
  }
  /* Logout, disconnect and release resources. */
  /* FIXME!/Wenbo-20081028: |fx_loginout| doesn't work in this case. */
  //fx_loginout();
  fx_close_network();
  fx_terminate();
  return 0;
}
