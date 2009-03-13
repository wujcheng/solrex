/*
 * XXPtalk example
 * Copyright 2004--2005, Google Inc.
 *
 * Modified by XIONG Qin
 *
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

#ifndef CRICKET_EXAMPLES_CALL_CALLCLIENT_H__
#define CRICKET_EXAMPLES_CALL_CALLCLIENT_H__

#include <map>
#include <string>
#include "talk/base/autodetectproxy.h"
#include "talk/p2p/base/session.h"
#include "talk/p2p/client/httpportallocator.h"
#include "talk/xmpp/xmppclient.h"
#include "talk/examples/login/status.h"
#include "console.h"
#include "chatclient.h"
#include "fileclient.h"

namespace buzz {
class PresencePushTask;
class Status;
}

namespace talk_base {
class Thread;
class NetworkManager;
}

namespace cricket {
class PortAllocator;
class PhoneSessionClient;
class Receiver;
class Call;
class SessionManagerTask;
}

struct RosterItem {
  buzz::Jid jid;
  buzz::Status::Show show;
  std::string status;
    
};

class CallClient: public sigslot::has_slots<> {
public:
  CallClient(buzz::XmppClient* xmpp_client);
  ~CallClient();

  cricket::PhoneSessionClient* phone_client() const { return phone_client_; }

  void PrintRoster();
  void PrintOLRoster();
  void MakeCallTo(const std::string& name);
  void SetConsole(Console *console) {console_ = console;
    _current_sending_fileclient->setConsole(this->console_);
  }
  void ParseLine(const std::string &str);

private:
  typedef std::map<std::string,RosterItem> RosterMap;

  bool b_first_time_send_file_;
  Console *console_;
  buzz::XmppClient* xmpp_client_;
  buzz::ChatClient* _chatclient;
  FileShareClient* _current_waiting_fileclient;
  FileShareClient* _current_sending_fileclient;
  talk_base::Thread* worker_thread_;
  talk_base::NetworkManager network_manager_;
  talk_base::AutoDetectProxy *proxy_detect_;
  cricket::HttpPortAllocator* port_allocator_;
  cricket::SessionManager* session_manager_;
  cricket::SessionManagerTask* session_manager_task_;
  cricket::PhoneSessionClient* phone_client_;
  
  cricket::Call* call_; 
  cricket::Session *session_;
  bool incoming_call_;
  bool incoming_file_;
  bool sending_file_;

  buzz::PresencePushTask* presence_push_;
  RosterMap* roster_;
  RosterMap* file_roster_;
  RosterMap* enligne_roster_;
  RosterMap* all_roster_;

  void OnStateChange(buzz::XmppEngine::State state);
  void OnTexteRecu(const std::string& iconset, const std::string& from, const std::string& texte);
  void SendTexte(const std::string& name, const std::string& texte);
  void SendFile(const std::string& name, const std::string& texte);
  void OnFileReceived(const std::string& from, const std::string& file, const std::string& no_use);
  void OnFileTransferStatue(const std::string& type, const std::string& statue, const std::string& no_use);
  void OnJingleInfo(const std::string &relay_token, const std::vector<std::string> &relay_hosts, 
		    const std::vector<talk_base::SocketAddress> &stun_hosts);
  void OnProxyDetect(talk_base::SignalThread *thread);
  void InitPhone();
//  void InitFileShareClient();
  void OnRequestSignaling();
  void OnCallCreate(cricket::Call* call);
  void OnCallDestroy(cricket::Call* call);
  const std::string strerror(buzz::XmppEngine::Error err);
  void OnSessionState(cricket::Call* call,
                      cricket::Session* session,
                      cricket::Session::State state);

  void InitPresence();
  void OnStatusUpdate(const buzz::Status& status);
};

#endif // CRICKET_EXAMPLES_CALL_CALLCLIENT_H__
