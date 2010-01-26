/*
 Copyright (c) 2005 Poderosa Project, All Rights Reserved.

 $Id: ConfigParser.cs,v 1.2 2005/04/20 09:06:03 okajima Exp $
*/
using System;
using System.IO;
using System.Collections;

namespace Poderosa.PortForwarding
{
	internal class Section {
		private string _name;
		private Hashtable _data;
		private ArrayList _childSections;

		public string Name {
			get {
				return _name;
			}
		}
		public IDictionaryEnumerator GetPairEnumerator() {
			return _data.GetEnumerator();
		}
		public string this[string name] {
			get {
				return (string)_data[name];
			}
		}
		public Hashtable NameValueData {
			get {
				return _data;
			}
		}
		public string GetValue(string name, string defval) {
			object t = _data[name];
			return t==null? defval : (string)t;
		}
		public bool HasChild {
			get {
				return _childSections.Count>0;
			}
		}
		public IEnumerable Children {
			get {
				return _childSections;
			}
		}

		public Section FindChildSection(string name) {
			foreach(Section s in _childSections) {
				if(s.Name==name) return s;
			}
			return null;
		}

		public Section(string name, TextReader reader) {
			_name = name;
			_data = new Hashtable();
			_childSections = new ArrayList();

			string line = ReadLine(reader);
			while(line!=null) {
				int e = line.IndexOf('=');
				if(e!=-1) {
					string name0 = Normalize(line.Substring(0, e));
					string value = e==line.Length-1? "" : Normalize(line.Substring(e+1));
					_data[name0] = value;
				}
				else if(line.StartsWith("section")) {
					string[] v = line.Split(' ');
					foreach(string t in v) {
						if(t.Length>0 && t!="section") {
							_childSections.Add(new Section(t, reader));
							break;
						}
					}
				}
				else if(line.StartsWith("}") || line.StartsWith("end section")) {
					break;
				}
				line = ReadLine(reader);
			}
		}

		private static string ReadLine(TextReader reader) {
			string line = reader.ReadLine();
			return Normalize(line);
		}
		private static string Normalize(string s) {
			int i=0;
			if(s==null) return null;
			do {
				if(i==s.Length) return "";
				char ch = s[i++];
				if(ch!=' ' && ch!='\t') return s.Substring(i-1);
			} while(true);
		}
	}
			

}
