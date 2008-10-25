#!/bin/bash
# This script can automatically install Windows XP Chinese fonts to Linux on
# a dual OS installed computer.
# Usage: ./WinFonts2Linux.sh
check_answer()
{
	case $1 in
		y|Y|Yes|yes|YES)
			return 0;
			;;
		n|N|No|no|NO)
			return 1;
			;;
		*) echo "Answer either yes or no, default is yes"
			return 2
			;;
	esac
}

LIN_FONTS_DIR=/usr/share/fonts/zh_CN/TrueType
LANG_SEL_FILE=/etc/fonts/language-selector.conf
SEARCH_DIR=/media

echo -n \
"This script is for installing Windows Chinese fonts to Linux on a 
dual-OS-installed computer. And the writer doesn't promise it will work on
your system. Do you want to continue?(y/n)[y]:"
read ANS_STR
: ${ANS_STR:="y"}
check_answer $ANS_STR
ANS=$?
if [ $ANS -ne 0 ]; then
	echo "Exit."
	exit
fi

echo -n \
"There might be license problems, i.e. you have no permission to use Windows
fonts in other program except Windows OS. Do you want to continue?(y/n)[y]:"
read ANS_STR
: ${ANS_STR:="y"}
check_answer $ANS_STR
ANS=$?
if [ $ANS -ne 0 ]; then
	echo "Exit."
	exit
fi

echo -n "You run this script with root account or \"sudo\" command?(y/n)[y]:"
read ANS_STR
: ${ANS_STR:="y"}
check_answer $ANS_STR
ANS=$?
if [ $ANS -ne 0 ]; then
	echo "Exit."
	exit
fi

echo -n \
"You mounted Windows System partation \"C:\\\" to /media directory?(y/n)[y]:"
read ANS_STR
: ${ANS_STR:="y"}
check_answer $ANS_STR
ANS=$?
if [ $ANS -ne 0 ]; then
	echo -n \
"Where did you mount partation C:\\ to?[/media]?"
	read ANS_STR
	: ${ANS_STR:="/media"}
	if [ -d $ANS_STR ]; then
		SEARCH_DIR=$ANS_STR
	else
		echo "$ANS_STR is not a valid path. Exit."
		exit
	fi
fi

echo "Searching for windows's fonts directory..."
echo "That may take a few minutes, please wait."
WIN_FONTS_DIR=$(find $SEARCH_DIR -name "Fonts" -type d)
if [ -d $WIN_FONTS_DIR ]; then
	while true; do
		echo "Windows fonts dir found! Your windows fonts directory is: "
		echo -n "\"$WIN_FONTS_DIR\" Is it right?(y/n)[y]:"
		read ANS_STR
		: ${ANS_STR:="y"}
		check_answer $ANS_STR
		ANS=$?
		if [ $ANS -eq 0 ]; then
			break
		elif [ $ANS -eq 1 ]; then  
			echo "Please enter path to your windows fonts dir?"
			read ANS_STR
			if [ -d $ANS_STR ]; then
				WIN_FONTS_DIR=$ANS_STR
				break
			else
				echo "Your input is not a dir. Exit."
				exit
			fi
		else
			echo "Unrecognized input."
		fi
	done
else
	echo "Can not find windows fonts directory. "
	echo -n "Please enter path to your windows fonts dir:"
	read ANS_STR
	if [ -d $ANS_STR ]; then
		WIN_FONTS_DIR=$ANS_STR
	else
		echo "Your input is not a dir. Exit."
		exit
	fi
fi

echo "Entering $WIN_FONTS_DIR"
cd $WIN_FONTS_DIR

echo "Creating Linux fonts directory: $LIN_FONTS_DIR..."
mkdir -p $LIN_FONTS_DIR
if [ $? -ne 0 ]; then
	echo "Creating Linux fonts dir failed. Exit."
	exit
fi

echo "Copying windows fonts to linux fonts dir... sim*|tahoma|tohomabd.ttf"
cp sim* SimSun18030.ttc tahoma.ttf tahomabd.ttf $LIN_FONTS_DIR
if [ $? -ne 0 ]; then
	echo "Copying windows fonts to linux fonts dir failed. Exit."
	exit
fi

echo "Changing font files' access permissions.."
chmod 644 $LIN_FONTS_DIR/*

echo "Entering $LIN_FONTS_DIR"
cd $LIN_FONTS_DIR

echo "Creating an index of scalable font files in $LIN_FONTS_DIR for X..."
mkfontscale
if [ $? -ne 0 ]; then
	echo "Creating an index of scalable font files for X failed. Exit."
	exit
fi

echo "Creating an index of X font files in $LIN_FONTS_DIR"
mkfontdir
if [ $? -ne 0 ]; then
	echo "Creating an index of X font files in $LIN_FONTS_DIR failed. Exit."
	exit
fi

echo "Creating an index of FreeType font files in $LIN_FONTS_DIR"
fc-cache $LIN_FONTS_DIR
if [ $? -ne 0 ]; then
	echo "Creating an index of FreeType font files in $LIN_FONTS_DIR failed. Exit."
	exit
fi

echo "Backing up $LANG_SEL_FILE"
if [ -f $LANG_SEL_FILE ]; then
	cp $LANG_SEL_FILE $LANG_SEL_FILE.bak 
fi

echo "Generating new $LANG_SEL_FILE... for language select"
echo '
<fontconfig>
  <alias>
    <family>serif</family>
      <prefer>
        <family>Bitstream Vera Serif</family>
        <family>SimSun</family>
        <family>DejaVu Serif</family>
        <family>AR PL ShanHeiSun Uni</family>
        <family>AR PL ZenKai Uni</family>
      </prefer>
  </alias>
  <alias>
    <family>sans-serif</family>
      <prefer>
        <family>Bitstream Vera Sans</family>
        <family>SimSun</family>
        <family>DejaVu Sans</family>
        <family>AR PL ShanHeiSun Uni</family>
        <family>AR PL ZenKai Uni</family>
      </prefer>
  </alias>
  <alias>
    <family>monospace</family>
      <prefer>
        <family>DejaVu Sans Mono</family>
        <family>Bitstream Vera Sans Mono</family>
        <family>SimHei</family>
    </prefer>
  </alias>

<match target="font" >
  <test name="family" compare="contains" >
    <string>Song</string>
    <string>Sun</string>
    <string>Kai</string>
    <string>Ming</string>
  </test>
  <test compare="more_eq" target="pattern" name="weight" >
    <int>180</int>
  </test>
  <edit mode="assign" name="embolden" >
    <bool>true</bool>
  </edit>
</match>

<match target="font" >
  <test name="family" compare="contains" >
    <string>Song</string>
    <string>Sun</string>
    <string>Kai</string>
    <string>Ming</string>
  </test>
  <edit name="globaladvance">
    <bool>false</bool>
  </edit>
  <edit name="spacing">
    <int>0</int>
  </edit>
  <edit name="hinting">
    <bool>true</bool>
  </edit>
  <edit name="autohint">
    <bool>false</bool>
  </edit>
  <edit name="antialias" mode="assign">
    <bool>true</bool>
  </edit>
  <test name="pixelsize" compare="less_eq">
    <int>18</int>
  </test>
  <edit name="antialias" mode="assign" >
    <bool>false</bool>
  </edit>
</match>

<match target="pattern">
  <test name="family">
    <string>SimSun</string>
    <string>SimSun-18030</string>
    <string>AR PL ShanHeiSun Uni</string>
    <string>AR PL New Sung</string>
    <string>MingLiU</string>
    <string>PMingLiU</string>
  </test>
  <edit binding="strong" mode="prepend" name="family">
    <string>Tahoma</string>
    <string>Verdana</string>
  </edit>
</match>

<match target="pattern">
  <test name="family">
    <string>宋体</string>
  </test>
  <edit name="family" mode="assign">
    <string>SimSun</string>
  </edit>
</match>
<match target="pattern">
  <test name="family">
    <string>新宋体</string>
  </test>
  <edit name="family" mode="assign">
    <string>SimSun</string>
  </edit>
</match>
<match target="pattern">
  <test name="family">
    <string>仿宋_GB2312</string>
  </test>
  <edit name="family" mode="assign">
    <string>FangSong_GB2312</string>
  </edit>
</match>
<match target="pattern">
  <test name="family">
    <string>楷体_GB2312</string>
  </test>
  <edit name="family" mode="assign">
    <string>KaiTi_GB2312</string>
  </edit>
</match>
<match target="pattern">
  <test name="family">
    <string>黑体</string>
  </test>
  <edit name="family" mode="assign">
    <string>SimHei</string>
  </edit>
</match>
</fontconfig> ' > $LANG_SEL_FILE
echo "Finished. Please quit your X session and relogin."
echo "If you have problems on start X(sometimes happened on Ubuntu 7.04.), please delete $LIN_FONTS_DIR and copy the backuped $LANG_SEL_FILE.bak to $LANG_SEL_FILE. Then do \"fc-cache\" with no options. That may work."
