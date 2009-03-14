/* YGtalk: Yet another Gtalk.
   Copyright (C) 2009 Solrex Yang <http://solrex.cn>

   This program is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>. */

#ifndef _CHATCLIENT_H_
#define _CHATCLIENT_H_


#include "talk/xmpp/xmppengine.h"
#include "talk/xmpp/xmpptask.h"

namespace buzz{

class ChatClient : public XmppTask {
  public:
	ChatClient(Task * parent) : XmppTask(parent, XmppEngine::HL_TYPE) 
	{
	}
	virtual ~ChatClient() {}


	sigslot::signal3<const std::string&, //iconset 
				   const std::string&, //par 
				   const std::string&> /*texte*/ SignalTexteRecu;
	void envoyerTexte(const std::string& str_to, const std::string& str_text);

  protected:
	virtual int  ProcessStart();
	virtual bool HandleStanza(const XmlElement * stanza);
		
};
}
#endif

