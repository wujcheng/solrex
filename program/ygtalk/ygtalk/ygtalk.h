/*  Copyright 2009 XIONG Qin
    
    This file is part of XXPalk.

    XXPalk is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    XXPalk is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with XXPalk.  If not, see <http://www.gnu.org/licenses/>.
*/
/*
#ifndef _XXPCLIENT_H_
#define _XXPCLIENT_H_

#include "chatclient.h"
#include "callclient.h"



class XXPClient : public CallClient{
  
  private:
    buzz::ChatClient* _chatclient;
    CallClient* _callclient;
    buzz::XmppClient* _xmpp_client;
  
  public:
    XXPClient(buzz::XmppClient* xmpp_client): CallClient(xmpp_client){
      _callclient = this;
      _xmpp_client = xmpp_client;
//      _chatclient = new buzz::ChatClient(xmpp_client);
    }
  
  ~XXPClient(){

    if(_chatclient != NULL)
	delete _chatclient;
  }

  
  private:
    void InitPresence(){
	CallClient::InitPresence();
	_chatclient = new buzz::ChatClient(this->_xmpp_client);
//	_chatclient->SignalTexteRecu.connect(this, &CallClient::OnTexteRecu);
    }

    void OnTexteRecu(const std::string& iconset, const std::string& from, const std::string& texte){
	std::string str;
	str += from;
	str += std::string("(");
	str += iconset;
	str += std::string(") said: ");
	str += texte;
	console_->Print(str);
    }
};
*/
#endif