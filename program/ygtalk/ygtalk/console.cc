/*
 * Jingle ygtalk example
 * Copyright 2004--2005, Google Inc.
 *
 * Modified by XIONG Qin for adding socket fonction to communicate with
 * other client 

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

#define _CRT_SECURE_NO_DEPRECATE 1

#include <cassert>
#include "talk/base/messagequeue.h"
#include "talk/base/stringutils.h"
#include "console.h"
#include "callclient.h"
#include <sys/types.h> 
#include <sys/socket.h>
#include <netinet/in.h>
#include <stdio.h>
#include "string.h"


Console::Console(talk_base::Thread *thread, CallClient *client, int port) : 
  client_thread_(thread), client_(client), prompt_(std::string("ygtalk")),
  prompting_(true) {

  if(port == 0)
    b_with_ui = false;
  else
    b_with_ui = true;
  //init socket here
  
  if(b_with_ui){

    int sockfd, newsockfd, portno, clilen;
  
    struct sockaddr_in serv_addr, cli_addr;

    sockfd = socket(AF_INET, SOCK_STREAM, 0);
    sock_fd_ = sockfd;
    if (sockfd < 0) 
      printf("ERROR opening socket\n");
    bzero((char *) &serv_addr, sizeof(serv_addr));
    portno = port;
    serv_addr.sin_family = AF_INET;
    serv_addr.sin_addr.s_addr = INADDR_ANY;
    serv_addr.sin_port = htons(portno);
    if (bind(sockfd, (struct sockaddr *) &serv_addr,
	    sizeof(serv_addr)) < 0) 
	    printf("ERROR on binding\n");
    listen(sockfd,50);
    clilen = sizeof(cli_addr);
    printf("waiting from client to accept\n");
    newsockfd = accept(sockfd, 
		      (struct sockaddr *) &cli_addr, 
		      (socklen_t*)(&clilen));
    printf("accepted from client\n");
    if (newsockfd < 0) 
	printf("ERROR on accept\n");
    
    new_sock_fd_ = newsockfd;
  }//if
  

}


void Console::Send(const char* str)
{
  if(b_with_ui){
    int n = write(new_sock_fd_, str, strlen(str));
    if (n < 0) printf("ERROR writing to socket\n");
  }//if
}

void Console::Send(const std::string& str)
{
  if(b_with_ui){
    Send(str.c_str());
  }//if
  
}

std::string Console::Receive()
{

  if(b_with_ui){
    char buffer[256];
    bzero(buffer, 256);
    int n = read(new_sock_fd_,buffer,255);
      if (n < 0) printf("ERROR reading from socket\n");
      else
	  return std::string(buffer);
  }//if
}

void Console::StartConsole() {
  char buffer[256];
  std::string message;
  for (;;) {
//	fgets(buffer, sizeof(buffer), stdin);
    if(b_with_ui){
      bzero(buffer,256);
      int n = read(new_sock_fd_,buffer,255);
      if (n < 0) printf("ERROR reading from socket\n");
  //     printf("Here is the message: %s\n",buffer);
  //     n = write(new_sock_fd_,"I got your message",18);
  //     if (n < 0) printf("ERROR writing to socket\n");
      
      
      std::string str = std::string(buffer);
      int pos = str.find("\n");
      message = str.substr(0, pos);
      client_thread_->Post(this, MSG_INPUT, 
	            new talk_base::TypedMessageData<std::string>(message));
    }//if
    else{
      fgets(buffer, sizeof(buffer), stdin);
      client_thread_->Post(this, MSG_INPUT, 
	            new talk_base::TypedMessageData<std::string>(buffer));
    }//else
    


  }//for
}

void Console::Close()
{
  close(sock_fd_);
//  dostuff(new_sock_fd_);
  close(new_sock_fd_);
}

void Console::OnMessage(talk_base::Message *msg) {
  switch (msg->message_id) {
    case MSG_START:
      StartConsole();
	  break;
	case MSG_INPUT:
	  talk_base::TypedMessageData<std::string> *data = 
	    static_cast<talk_base::TypedMessageData<std::string>*>(msg->pdata);
	  client_->ParseLine(data->data());
	  break;
  }
}


void Console::Print(const char* str) {
  printf("\n%s", str);
  if (prompting_)
    printf("\n(%s) ", prompt_.c_str());
}

void Console::Print(const std::string& str) {
  Print(str.c_str());
}

void Console::Printf(const char* format, ...) {
  va_list ap;
  va_start(ap, format);

  char buf[4096];
  int size = vsnprintf(buf, sizeof(buf), format, ap);
  assert(size >= 0);
  assert(size < static_cast<int>(sizeof(buf)));
  buf[size] = '\0';
  Print(buf);

  va_end(ap);
}
