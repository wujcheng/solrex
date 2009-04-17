/* sendsms.cpp: defines main().
   Copyright (C) 2008  Solrex Yang <http://solrex.cn>
 
   This file is part of Sendsms.

   Sendsms is freeware: Redistribution and use in source and binary forms, 
   with or without modification, are permitted provided that the following
   conditions are met:
   1. Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
   2. Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.

   THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND
   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
   IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
   ARE DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE
   FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
   DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
   OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
   HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
   LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
   OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
   SUCH DAMAGE. */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <argp.h>
#include <unistd.h>
#include "include/libfetion.h"

#define MAX_LOGIN 5      /* Maximum login retry times. */
#define MAX_SEND  20     /* Maximum send retry times. */
#define MES_LEN   180    /* Standard message length. */
#define L_MES_LEN 1024   /* Long message length. */

/* Version, contact, user defined doc, argument doc strings for argp.h. */
const char *argp_program_version = "sendsms 0.11(2009-01-11)";
const char *argp_program_bug_address = "<http://solrex.cn>";
static char doc[] = "Example:\n  sendsms -f SENDER -p PASSWD -t fetion1,\
fetion2 \"Hey, sendsms is so cool!\"\nOptions:";
static char args_doc[] = "MESSAGE";

/* Program options we understand. */
static struct argp_option options[] = {
  {"from",    'f',   "SENDER",    0, "The sender's FETION/PHONE number." },
  {"passwd",  'p',   "PASSWD",    0, "The sender's password." },
  {"to",      't',"RECEIVERS",    0, 
   "The receivers' FETION/PHONE numbers, using ',' to seperate numbers." },
  {"longsms", 'l',          0,    0,
   "Enable long SMS, up to 1024 chars in 1 message."},
  {"verbose", 'v',          0,    0, "Print verbose information." },
  { 0 }
};
 
/* Used by |main| to communicate with |parse_opt|. */
typedef struct _args {
  char *from;       /* Sender's phone/fetion number string pointer. */
  char *passwd;     /* Sender's password string pointer. */
  char *to;         /* Receiver's phone/fetion number string pointer. */
  char *message;    /* SMS message body pointer. */
  BOOL  longsms;    /* Switch of long SMS option. */
  BOOL  verbose;    /* Switch of verbose mode. */
} ARGUMENTS;

/* Parse a single option. */
static error_t
parse_opt (int key, char *arg, struct argp_state *state)
{
  /* Get the input argument from |argp_parse|, which we know is a pointer to
   * our |ARGUMENTS| structure. */
  ARGUMENTS *p_args = (ARGUMENTS *)state->input;
  int read_count;
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
    case 'l':
      p_args->longsms = TRUE;
    break;
    case 'v':
      p_args->verbose = TRUE;
    break;
    case ARGP_KEY_ARG:   /* No more than one(none-option) argument: MESSAGE. */
      if (state->arg_num > 1)   
        argp_usage (state);
      /* Read MESSAGE from command line. */
      p_args->message = (char *) malloc(L_MES_LEN*sizeof(char));
      if (p_args->message) {
        strncpy (p_args->message, arg, L_MES_LEN);
        p_args->message[L_MES_LEN-1] = '\0';
      }
    break;
    case ARGP_KEY_NO_ARGS: 
      /* If MESSAGE is not given in command, read MESSAGE from stdin. */
      p_args->message = (char *) malloc(L_MES_LEN*sizeof(char));
      if (p_args->message) {
        read_count = fread (p_args->message, sizeof(char), L_MES_LEN, stdin);
        p_args->message[read_count] = '\0';
      }
    break;
    case ARGP_KEY_END:
      if (state->arg_num > 1) /* No more than one argument: MESSAGE. */
        argp_usage (state);
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
  char *proxyenv = NULL, *p, *q;
  int ret, i, mes_len;
  long int uid;

  /* Default option values. */
  args.from = NULL;
  args.passwd = NULL;
  args.to = NULL;
  args.verbose = FALSE;
  args.longsms = FALSE;

  /* Parse our arguments; every option seen by |parse_opt| will be reflected
   * in |args|. */
  argp_parse (&p_argp, argc, argv, 0, 0, &args);

  if (args.from == NULL || args.passwd == NULL || args.message == NULL) {
    fprintf(stderr, "ERROR: SENDER, PASSWD and MESSAGE is needed.\n");
    return 1;
  }

  mes_len = args.longsms ? L_MES_LEN : MES_LEN;
  ret = strlen(args.message);
  if ((ret<1) || (ret>=mes_len)) {
    fprintf(stderr, "ERROR: Argument MESSAGE is too long or too short.\n");
    return 1;
  }
  ret = strlen(args.from);
  if ((ret!=9) && (ret!=11)) {
    fprintf(stderr, "ERROR: Option value SENDER has wrong bits.\n");
    return 1;
  }
  if (args.to != NULL) {
    ret = strlen(args.to);
    if (ret<9) {
      fprintf(stderr, "ERROR: Option value RECEIVER has wrong bits.\n");
      return 1;
    }
  }

  if (!fx_init()) {                         /* Init libfetion. */
    fprintf(stderr, "FAIL: init().\n");
    return 1;
  } else if (args.verbose == TRUE) {
    fprintf(stderr, "PASS: init().\n");
  }

  /* Read environment variable "http_proxy". */
  if ((proxyenv = getenv("http_proxy"))) {
    proxyenv = strdup(proxyenv);
    if (strncmp(proxyenv, "http://", 7) == 0) {
      if ((p = strchr(proxyenv, '@'))) {
        *p = '\0';
        proxy.host = p + 1;
        proxy.name = proxyenv + 7;
        if ((p = strchr(proxy.name, ':'))) {
          *p = '\0';
          proxy.pwd = p + 1;
        }
      } else {
        proxy.host = proxyenv + 7;
        proxy.name = NULL;
        proxy.pwd = NULL;
      }
      if ((p = strchr(proxy.host, ':'))) {
        *p = '\0';
        proxy.port = p + 1;
      }
      proxy.type = PROXY_HTTP;
      /* Set http proxy. */
      fx_set_proxy(&proxy);
    }
  }

  fx_set_login_status(FX_STATUS_OFFLINE);   /* Set status offline. */
  fx_set_longsms(args.longsms);             /* Set long SMS mode. */
  for (i=1; i<=MAX_LOGIN; i++) {
    ret = fs_login(args.from, args.passwd);
    if (ret) break;
    else sleep(1);
  }
  i>MAX_LOGIN ? i-- : i ;
  if (!ret) {
    fprintf(stderr, "FAIL: %s login() after %d tries.\n", args.from, i);
    return 2;
  } else if (args.verbose == TRUE) {
    fprintf(stderr, "PASS: %s login() after %d tries.\n", args.from, i);
  }
  /* If "-t" option is ignored, send the MESSAGE to the SENDER-self. */
  if (args.to == NULL) {
    args.to = args.from;
    for (i=1; i<=MAX_SEND; i++) {
      ret = fs_send_sms_to_self(args.message);
      if (ret) break;
      else sleep(1);
    }
    i>MAX_SEND ? i-- : i ;
    if (!ret) {
      fprintf(stderr, "FAIL: send_sms() from %s to %s after %d tries.\n",
              args.from, args.to, i);
      return 3; 
    } else if (args.verbose == TRUE) {
      fprintf(stderr, "PASS: send_sms() from %s to %s after %d tries.\n",
              args.from, args.to, i);
    }
  } else {
    /* If "-t" option is a mobile phone number, use API: 
     * |fs_send_sms_by_mobile_no|. */
    p = strdup(args.to);
    q = strchr(p, ',');
    while (p != NULL) {
      if (q != NULL)    *q = '\0';
      if (strlen(p) == 11 && (strncmp(p, "13", 2) == 0 || strncmp(p, "15", 2) == 0)) {
        for (i=1; i<=MAX_SEND; i++) {
          ret = fs_send_sms_by_mobile_no(p, args.message);
          if (ret) break;
          else sleep(1);
        }
      } else if (strlen(p) == 9){
        /* If "-t" option is a fetion number, use API: |fs_send_sms|. */
        uid = strtol(p, NULL, 10);
        for (i=1; i<=MAX_SEND; i++) {
          ret = fs_send_sms(uid, args.message);
          if (ret) break;
          else sleep(1);
        }
      } else {
        fprintf(stderr, "FAIL: unsupported receiver number %s.\n", p);
        i = 0;
      }
      i>MAX_SEND ? i-- : i ;
      if (!ret) {
        fprintf(stderr, "FAIL: send_sms() from %s to %s after %d tries.\n",
                args.from, p, i);
        return 3; 
      } else if (args.verbose == TRUE) {
        fprintf(stderr, "PASS: send_sms() from %s to %s after %d tries.\n",
                args.from, p, i);
      }
      if (q != NULL) {
        p = q+1;
        q = strchr(p, ',');
      } else {
        p = NULL;
      }
    }
  }
  /* Logout, disconnect and release resources. */
  fx_loginout();
  fx_close_network();
  //fx_terminate();
  if (args.verbose == TRUE) {
    fprintf(stderr, "PASS: terminate().\n");
  }
  free (args.message);
  return 0;
}
