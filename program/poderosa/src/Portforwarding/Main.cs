/*
 Copyright (c) 2005 Poderosa Project, All Rights Reserved.

 $Id: Main.cs,v 1.2 2005/04/20 09:06:03 okajima Exp $
*/
using System;
using System.Text;
using System.Collections;
using System.Globalization;
using System.IO;
using Microsoft.Win32;

using Poderosa.Toolkit;
using Poderosa.Config;
using Poderosa.UI;

namespace Poderosa.PortForwarding
{
	internal class Env {

		public const string VERSION_STRING = "Version 3.0";

		[STAThread]
		public static void Main(string[] args) {
			if(ActivateAnotherInstance()) return;
			LoadEnv();
			Run();
			SaveEnv();
		}

		private static ChannelProfileCollection _channels;
		private static Options _options;
		private static MainForm _form;
		private static Commands _commands;
		private static ConnectionManager _manager;
		private static ConnectionLog _log;
		private static StringResources _strings;
		private static IntPtr _globalMutex;

		private static void LoadEnv() {
			string error_msg = null;
			ReloadStringResource();
			_channels = new ChannelProfileCollection();
			_options = new Options();
			ThemeUtil.Init();

			_globalMutex = Win32.CreateMutex(IntPtr.Zero, 0, "PoderosaPFGlobalMutex");
			bool already_exists = (Win32.GetLastError()==Win32.ERROR_ALREADY_EXISTS);
			if(_globalMutex==IntPtr.Zero) throw new Exception("Global mutex could not open");
			if(Win32.WaitForSingleObject(_globalMutex, 10000)!=Win32.WAIT_OBJECT_0) throw new Exception("Global mutex lock error");

			try {
				OptionPreservePlace place = GetOptionPreservePlace();
				_options.OptionPreservePlace = place;
				string dir = GetOptionDirectory(place);
				if(!Directory.Exists(dir)) Directory.CreateDirectory(dir);
				string configfile = dir + "portforwarding.conf";
				bool options_loaded = false;
				TextReader reader = null;
				try {
					if(File.Exists(configfile)) {
						reader = new StreamReader(File.Open(configfile, FileMode.Open, FileAccess.Read), Encoding.Default);
						ConfigNode parent = new ConfigNode("root", reader).FindChildConfigNode("poderosa-portforwarding");
						if(parent!=null) {	
							_channels.Load(parent);
							_options.Load(parent);
							options_loaded = true;
						}
					}
				}
				catch(Exception ex) {
					error_msg = ex.Message;
				}
				finally {
					if(reader!=null) reader.Close();
					if(!options_loaded) _options.Init();
				}

				//ここまできたら言語設定をチェックし、必要なら読み直し
				if(Util.CurrentLanguage!=_options.Language) {
					System.Threading.Thread.CurrentThread.CurrentUICulture = _options.Language==Language.Japanese? new CultureInfo("ja") : CultureInfo.InvariantCulture;
				}

				_log = new ConnectionLog(dir + "portforwarding.log");
			}
			finally {
				Win32.ReleaseMutex(_globalMutex);
			}
		}
		private static void SaveEnv() {
			if(IsRegistryWritable) {
				RegistryKey g = Registry.CurrentUser.CreateSubKey(REGISTRY_PATH);
				g.SetValue("option-place", EnumDescAttribute.For(typeof(OptionPreservePlace)).GetName(_options.OptionPreservePlace));
			}

			if(Win32.WaitForSingleObject(_globalMutex, 10000)!=Win32.WAIT_OBJECT_0) throw new Exception("Global mutex lock error");

			try {
				string dir = GetOptionDirectory(_options.OptionPreservePlace);
				if(!Directory.Exists(dir)) Directory.CreateDirectory(dir);
				string configfile = dir + "portforwarding.conf";
				TextWriter wr = new StreamWriter(configfile, false);
				ConfigNode root = new ConfigNode("poderosa-portforwarding");
				_channels.Save(root);
				_options.Save(root);
				root.WriteTo(wr);
				wr.Close();
				_log.Close();
			}
			finally {
				Win32.ReleaseMutex(_globalMutex);
			}
			Win32.CloseHandle(_globalMutex);
		}

		private static void Run() {
			_commands = new Commands();
			_manager = new ConnectionManager();
			_form = new MainForm();
			_form.ShowInTaskbar = _options.ShowInTaskBar;
			_form.Location = _options.FramePosition.Location;
			_form.Size = _options.FramePosition.Size;
			_form.WindowState = _options.FrameState;
			_form.RefreshAllProfiles();
			System.Windows.Forms.Application.Run(_form);
		}

		public static void UpdateOptions(Options opt) {
			_form.ShowInTaskbar = opt.ShowInTaskBar;
			if(_options.Language!=opt.Language) { //言語のリロードが必要なとき
				System.Threading.Thread.CurrentThread.CurrentUICulture = opt.Language==Language.Japanese? new CultureInfo("ja") : CultureInfo.InvariantCulture;
				ReloadStringResource();
				_form.ReloadLanguage();
				Granados.SSHC.Strings.Reload();
			}
			_options = opt;
		}
		public static void ReloadStringResource() {
			_strings = new StringResources("Portforwarding.strings", typeof(Env).Assembly);
			EnumDescAttribute.AddResourceTable(typeof(Env).Assembly, _strings);
		}

		public static Options Options {
			get {
				return _options;
			}
		}
		public static StringResources Strings {
			get {
				return _strings;
			}
		}

		public static ChannelProfileCollection Profiles {
			get {
				return _channels;
			}
		}
		public static MainForm MainForm {
			get {
				return _form;
			}
		}
		public static Commands Commands {
			get {
				return _commands;
			}
		}
		public static ConnectionManager Connections {
			get {
				return _manager;
			}
		}
		public static ConnectionLog Log {
			get {
				return _log;
			}
		}

		public static string GetOptionDirectory(OptionPreservePlace p) {
			if(p==OptionPreservePlace.InstalledDir) {
				string t = AppDomain.CurrentDomain.BaseDirectory;
				if(Environment.UserName.Length>0) t += Environment.UserName + "\\";
				return t;
			}
			else
				return Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData) + "\\Poderosa\\";
		}


		private const string REGISTRY_PATH = "Software\\Poderosa Networks\\Portforwarding";

		public static bool IsRegistryWritable {
			get {
				try {
					RegistryKey g = Registry.CurrentUser.CreateSubKey(REGISTRY_PATH);
					if(g==null)
						return false;
					else
						return true;
				}
				catch(Exception) {
					return false;
				}
			}
		}

		private static OptionPreservePlace GetOptionPreservePlace() {
			RegistryKey g = Registry.CurrentUser.OpenSubKey(REGISTRY_PATH, false);
			if(g==null)
				return OptionPreservePlace.InstalledDir;
			else
				return (OptionPreservePlace)EnumDescAttribute.For(typeof(OptionPreservePlace)).FromName((string)g.GetValue("option-place"), OptionPreservePlace.InstalledDir);
		}

		public const string WindowTitle = "SSH PortForwarding Gateway";

		private static bool ActivateAnotherInstance() {
			//find target
			unsafe {
				IntPtr hwnd = Win32.FindWindowEx(IntPtr.Zero,IntPtr.Zero,null,null);
				while(hwnd!=IntPtr.Zero) {
					char* buf = stackalloc char[256];
					Win32.GetWindowText(hwnd, buf, 256);
					string name = new string(buf);
					if(name==WindowTitle) {
						Win32.SetForegroundWindow(hwnd);
						Win32.ShowWindow(hwnd, Win32.SW_RESTORE);
						return true;
					}
					hwnd = Win32.FindWindowEx(IntPtr.Zero,hwnd,null,null);
				}
			}
			return false;
		}
	}
}
