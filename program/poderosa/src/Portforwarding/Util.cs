/*
* Copyright (c) 2005 Poderosa Project, All Rights Reserved.
* $Id: Util.cs,v 1.2 2005/04/20 09:08:28 okajima Exp $
*/
using System;
using System.Drawing;
using System.IO;
using System.Globalization;
using System.Windows.Forms;
using System.Runtime.InteropServices;
using System.Net;
using System.Net.Sockets;
using System.Text;

using Granados.SSHC;
using Granados.PKI;
using Granados.Toolkit;
using Granados.Crypto;

namespace Poderosa.PortForwarding
{
	/// <summary>
	/// Util の概要の説明です。
	/// </summary>
	internal class Util {
		public static bool ParseBool(string value, bool defaultvalue) {
			try {
				return Boolean.Parse(value);
			}
			catch(Exception) {
				return defaultvalue;
			}
		}
		public static byte ParseByte(string value, byte defaultvalue) {
			try {
				return Byte.Parse(value);
			}
			catch(Exception) {
				return defaultvalue;
			}
		}
		public static int ParseInt(string value, int defaultvalue) {
			try {
				return Int32.Parse(value);
			}
			catch(Exception) {
				return defaultvalue;
			}
		}
		public static short ParseShort(string value, short defaultvalue) {
			try {
				return short.Parse(value);
			}
			catch(Exception) {
				return defaultvalue;
			}
		}
		public static ushort ParsePort(string value) {
			ushort p = ushort.Parse(value);
			return p;
		}
		public static ushort ParsePort(string value, ushort def) {
			try {
				ushort p = ushort.Parse(value);
				return p;
			}
			catch(Exception) {
				return def;
			}
		}
		public static ProtocolType ParseProtocol(string value, ProtocolType defaultvalue) {
			if(value=="Udp")
				return ProtocolType.Udp;
			else if(value=="Tcp")
				return ProtocolType.Tcp;
			else
				return defaultvalue;
		}
		public static AuthenticationType ParseAuthType(string value, AuthenticationType defaultvalue) {
			if(value=="Password")
				return AuthenticationType.Password;
			else if(value=="PublicKey")
				return AuthenticationType.PublicKey;
			else
				return defaultvalue;
		}
		public static string AuthTypeDescription(AuthenticationType value) {
			if(value==AuthenticationType.PublicKey)
				return Env.Strings.GetString("Caption.AuthenticationType.PublicKey");
			else
				return Env.Strings.GetString("Caption.AuthenticationType.Password");
		}

		public static void WriteNameValue(TextWriter wr, string name, string value) {
			wr.Write(name);
			wr.Write('=');
			wr.WriteLine(value);
		}
		public static string ConcatStringWithComma(string[] ac) {
			StringBuilder b = new StringBuilder();
			foreach(string a in ac) {
				if(b.Length>0) b.Append(',');
				b.Append(a.ToString());
			}
			return b.ToString();
		}
		public static void Warning(IWin32Window owner, string msg) {
			MessageBox.Show(owner, msg, "Portforwarding", MessageBoxButtons.OK, MessageBoxIcon.Exclamation);
		}
		public static void Warning(IWin32Window owner, string msg, string caption) {
			MessageBox.Show(owner, msg, caption, MessageBoxButtons.OK, MessageBoxIcon.Exclamation);
		}
		public static DialogResult AskUserYesNo(IWin32Window owner, string msg) {
			return MessageBox.Show(owner, msg, "Portforwarding", MessageBoxButtons.YesNo, MessageBoxIcon.Question);
		}

		private static string _message;
		private const int WARNING_MSG = 1;

		public static void InterThreadWarning(string msg) {
			_message = msg;
			Win32.SendMessage(Env.MainForm.Handle, Win32.WM_USER, new IntPtr(WARNING_MSG), IntPtr.Zero);
		}
		public static void CallbackWM_USER(IntPtr wparam, IntPtr lparam) {
			if(wparam.ToInt32()==WARNING_MSG)
				Warning(Env.MainForm, _message);
		}

		public static string SelectPrivateKeyFileByDialog(Form parent) {
			OpenFileDialog dlg = new OpenFileDialog();
			dlg.CheckFileExists = true;
			dlg.Multiselect = false;
			dlg.Title = Env.Strings.GetString("Caption.Util.SelectPrivateKey");
			dlg.Filter = "Key Files|*";
			if(Util.ShowModalDialog(parent, dlg)==DialogResult.OK)
				return dlg.FileName;
			else
				return null;
		}

		public static string GetProfileTypeString(ChannelProfile prof) {
			if(prof is LocalToRemoteChannelProfile)
				return Env.Strings.GetString("Caption.Util.Local");
			else
				return Env.Strings.GetString("Caption.Util.Remote");
		}
		public static string GetProfileStatusString(ChannelProfile prof) {
			return Env.Strings.GetString(Env.Connections.IsConnected(prof)? "Caption.Util.Connected" : "Caption.Util.Disconnected");
		}

		public static IPAddress ResolveHost(string hostname) {
			try {
				return IPAddress.Parse(hostname);
			}
			catch(FormatException) {
				return Dns.Resolve(hostname).AddressList[0];
			}
		}

		public static IPAddress ChannelProfileToListeningAddress(ChannelProfile prof) {
			if(prof.UseIPv6)
				return prof.AllowsForeignConnection? IPAddress.IPv6Any : IPAddress.IPv6Loopback;
			else
				return prof.AllowsForeignConnection? IPAddress.Any : IPAddress.Loopback;
		}
		public static Language CurrentLanguage {
			get {
				return CultureInfo.CurrentUICulture.Name.StartsWith("ja")? Language.Japanese : Language.English;
			}
		}

		public static DialogResult ShowModalDialog(Form parent, Form dialog) {
			parent.Enabled = false;
			DialogResult r = dialog.ShowDialog(parent);
			parent.Enabled = true;
			dialog.Dispose();
			return r;
		}
		public static DialogResult ShowModalDialog(Form parent, CommonDialog dialog) {
			parent.Enabled = false;
			DialogResult r = dialog.ShowDialog(parent);
			parent.Enabled = true;
			dialog.Dispose();
			return r;
		}

	}

	internal class SSHUtil {
		public static CipherAlgorithm ParseCipherAlgorithm(string t) {
			if(t=="AES128")
				return CipherAlgorithm.AES128;
			else if(t=="Blowfish")
				return CipherAlgorithm.Blowfish;
			else if(t=="TripleDES")
				return CipherAlgorithm.TripleDES;
			else
				throw new Exception("Unknown CipherAlgorithm " + t);
		}
		public static CipherAlgorithm[] ParseCipherAlgorithm(string[] t) {
			CipherAlgorithm[] ret = new CipherAlgorithm[t.Length];
			int i = 0;
			foreach(string a in t) {
				ret[i++] = ParseCipherAlgorithm(a);
			}
			return ret;
		}
		public static CipherAlgorithm[] ParseCipherAlgorithmList(string value) {
			return ParseCipherAlgorithm(value.Split(','));
		}


		public static PublicKeyAlgorithm ParsePublicKeyAlgorithm(string t) {
			if(t=="DSA")
				return PublicKeyAlgorithm.DSA;
			else if(t=="RSA")
				return PublicKeyAlgorithm.RSA;
			else
				throw new Exception("Unknown CipherAlgorithm " + t);
		}
		public static PublicKeyAlgorithm[] ParsePublicKeyAlgorithm(string[] t) {
			PublicKeyAlgorithm[] ret = new PublicKeyAlgorithm[t.Length];
			int i = 0;
			foreach(string a in t) {
				ret[i++] = ParsePublicKeyAlgorithm(a);
			}
			return ret;
		}
		public static PublicKeyAlgorithm[] ParsePublicKeyAlgorithmList(string value) {
			return ParsePublicKeyAlgorithm(value.Split(','));
		}
		public static string[] FormatPublicKeyAlgorithmList(PublicKeyAlgorithm[] value) {
			string[] ret = new string[value.Length];
			int i=0;
			foreach(PublicKeyAlgorithm a in value)
				ret[i++] = a.ToString();
			return ret;
		}

		public static string SimpleEncrypt(string plain) {
			byte[] t = Encoding.ASCII.GetBytes(plain);
			if((t.Length % 16)!=0) {
				byte[] t2 = new byte[t.Length + (16 - (t.Length % 16))];
				Array.Copy(t, 0, t2, 0, t.Length);
				for(int i=t.Length+1; i<t2.Length; i++) //残りはダミー
					t2[i] = t[i % t.Length];
				t = t2;
			}

			byte[] key = Encoding.ASCII.GetBytes("- BOBO VIERI 32-");
			Rijndael rijndael = new Rijndael();
			rijndael.InitializeKey(key);

			byte[] e = new byte[t.Length];
			rijndael.encryptCBC(t, 0, t.Length, e, 0);

			return Encoding.ASCII.GetString(Base64.Encode(e));
		}
		public static string SimpleDecrypt(string enc) {
			byte[] t = Base64.Decode(Encoding.ASCII.GetBytes(enc));
			byte[] key = Encoding.ASCII.GetBytes("- BOBO VIERI 32-");
			Rijndael rijndael = new Rijndael();
			rijndael.InitializeKey(key);

			byte[] d = new byte[t.Length];
			rijndael.decryptCBC(t, 0, t.Length, d, 0);

			return Encoding.ASCII.GetString(d); //パディングがあってもNULL文字になるので除去されるはず
		}
	}


	internal class Win32 {
		[DllImport("kernel32.dll", CharSet=CharSet.Auto)]
		public static extern IntPtr CreateMutex(IntPtr lpSecurityAttribute, int initialOwner, string name);
		[DllImport("kernel32.dll")]
		public static extern bool CloseHandle(IntPtr handle);
		[DllImport("kernel32.dll")]
		public static extern bool ReleaseMutex(IntPtr handle);
		[DllImport("kernel32.dll")]
		public static extern int WaitForSingleObject(IntPtr hHandle, uint dwMilliseconds);
		public const int WAIT_OBJECT_0 = 0;

		[DllImport("kernel32.dll")]
		public static extern int GetLastError();

		[DllImport("user32.dll", CharSet=CharSet.Auto)]
		public static extern IntPtr FindWindowEx(
			IntPtr hwndParent,      // handle to parent window
			IntPtr hwndChildAfter,  // handle to child window
			string lpszClass,    // class name
			string lpszWindow    // window name
			);
		[DllImport("user32.dll", ExactSpelling=false, CharSet=CharSet.Auto)]
		public static extern unsafe int GetWindowText(IntPtr hwnd, char* buf, int size);

		[DllImport("user32.dll")]
		public static extern int SendMessage(IntPtr hWnd, int msg, IntPtr wParam, IntPtr lParam);

		[DllImport("user32.dll")]
		public static extern int ShowWindow(IntPtr hWnd, int cmd);
		public const int SW_SHOW = 5;
		public const int SW_RESTORE = 9;
		[DllImport("user32.dll")]
		public static extern int SetForegroundWindow(IntPtr hWnd);

		public const int ERROR_ALREADY_EXISTS = 183;

		public const int WM_NOTIFY = 0x4E;
		public const int WM_USER = 0x400;
		public const int TCN_FIRST = -550;
		public const int TCN_SELCHANGING = (TCN_FIRST - 2);

		[StructLayout(LayoutKind.Sequential)]
			public struct NMHDR { 
			public IntPtr hwndFrom; 
			public uint idFrom; 
			public int code; 
		}
	}
}
