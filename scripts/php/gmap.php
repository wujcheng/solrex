<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Google Sitemap Generator</title>
</head>
<body id="top">
<h1>Google Sitemap Generator</h1>
<pre>
Note:
1. Put me in the root directory.
2. Before run it, modify these variables please.
LIMITLIENPARFICHIER
LIMITLIENINDEXE
$ExtensionsAutorises
$DossiersInterdits
$FichiersInterdits
3. It will generate several xml files for a large site.
4. sitemap(x).xml will be put in root dir.
</pre>

<h2>URL List</h2>

<?php
define('LIMITLIENPARFICHIER',1000); // MAX URL Number in one file
define('LIMITLIENINDEXE',50000); // MAX URL Number

$racine='http://'.$_SERVER['SERVER_NAME'];
$compressionGZ = false;
$Goption=2;
$ExtensionsAutorises= array('php','php3','html','htm', 'shtml', 'ppt', 'pdf'); // Sitemaped file type.
$DossiersInterdits = array('images','javascript','themes','_streifer', 'dcount'); // Ignored dirs
$FichiersInterdits = array('test.php','admin.php','header.php','footer.php','error.php','confige.php','gmap.php','[0-9]{3}\.[shtml, php]'); // Ignored files

function Dossier_Autoris($DossierCourant){
  global $DossiersInterdits;
  return Est_Autoris($DossierCourant, $DossiersInterdits);
}

function Fichier_Autoris($FichierCourant){
  global $FichiersInterdits;
  return Est_Autoris($FichierCourant, $FichiersInterdits);
}

function Extension_Autoris($ExtensionCourante){
  global $ExtensionsAutorises;
  return !Est_Autoris($ExtensionCourante,$ExtensionsAutorises);
}

function Est_Autoris($DossierCourant,$Interdits){
  global $Goption;
  $drapeau = true;
  while ($drapeau && list(,$Dossier)=each($Interdits) ){
    if ( ComparaisonFichier($DossierCourant,$Dossier,$Goption))
      $drapeau = false;
  }
  reset($Interdits);
  return $drapeau;
}

function ComparaisonFichier($DossierCourant,$Dossier,$option=0){
  switch ($option){
    case 0:
      return ($DossierCourant == $Dossier);
      break;
    case 1:
      $pos = strpos($mystring, $findme);
      if ($pos === false) {
        return false;
      } else {
        return true;
      }
      break;
    case 2:
      return ereg($Dossier,$DossierCourant);
      break;
  }
}

function getextension($fichier){
  $bouts = explode('.', $fichier);
  return array_pop($bouts);
}

function GetDirContents($dir){
  global $racine;
  $i = 0;
  ini_set('max_execution_time',10);
  if (!is_dir($dir)){
    die ('PROBLEME: '.$dir.'!');
  }
  $files = array();
  if ($root = @opendir($dir)){
    while ($file = readdir($root)){
      if($file == '.' || $file == '..'){
        continue;
      }
      if(is_dir($dir.'/'.$file) && Dossier_Autoris($file)){
        $files = array_merge($files, GetDirContents($dir.'/'.$file));
        $i = count($files) + 1;
      }else{
        $extension = getextension($file);
        if (Extension_Autoris($extension) && Fichier_Autoris($file)){
          echo '<strong>',$dir,'</strong>/', htmlentities($file),'<br />',"\r\n";
          $files[$i]['lien'] = utf8_encode($racine.substr($dir,1).'/'.$file);
          $modi_fich = filemtime($dir.'/'.$file);
          $files[$i]['date'] = date('Y-m-d', $modi_fich);
          $i++;
        }
      }
    }
  }
  return $files;
}

$myfiles = GetDirContents('.');
$nbliens = count($myfiles);
echo '<span class="italic">'.$nbliens.' lines will be written in sitemap file.</span><br />',"\r\n";

if ($nbliens>LIMITLIENPARFICHIER){
  // utilisation de la norme pour les sites souhaitant rfrencs plus de 1000 liens
  // un fichier sitemap à gnrer en plus
  $numfichier=1;
  echo '<h2>Generate sitemapXX.xml by step.</h2>',"\r\n";
}else {
  $numfichier='';
  echo '<h2>Generate sitemap.xml succeeded!</h2>',"\r\n";
}

if ($compressionGZ)
{
  $open='gzopen';
  $write='gzwrite';
  $close='gzclose';
  $GZ='.gz';
} else {
  $open='fopen';
  $write='fwrite';
  $close='fclose';
  $GZ='';
}

$CurLiens=0;
while ($CurLiens<$nbliens && $CurLiens<LIMITLIENINDEXE )
{
  if ($fp = $open('./sitemap'.$numfichier.'.xml'.$GZ, 'w')){
    $write($fp,'<?xml version="1.0" encoding="UTF-8"?>'."\r\n");
    $write($fp,'<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">'."\r\n");
    $Limite = $CurLiens + LIMITLIENPARFICHIER;
    while ($CurLiens< $Limite && $CurLiens<LIMITLIENINDEXE && list(,$file)=each($myfiles)){
      if (strpos($file['lien'], 'index')) {
        $priority = 0.8;
      } else { 
        $priority = 0.7;
      }
      $write($fp,'<url> '."\n".'  <loc>'.$file['lien'].'</loc>'."\n");
      $write($fp,'  <lastmod>'.$file['date'].'</lastmod>'."\n");
      $write($fp,'  <changefreq>daily</changefreq>');
      $write($fp,'  <priority>'.$priority.'</priority>'."\n".'</url>'."\n");
      $CurLiens ++;
    }
    $write($fp, '</urlset>');
    $close($fp);
    echo '<a href="./sitemap'.$numfichier.'.xml'.$GZ.'" target="_blank">Generate sitemap'.$numfichier.'.xml'.$GZ.'</a><br />',"\r\n";
  }else{
    echo 'sitemap'.$numfichier.'.xml',"\r\n"
      ,'<br /><br /><textarea rows="30" cols="100">',"\r\n"
      ,'<?xml version="1.0" encoding="UTF-8"?>',"\r\n"
      ,'<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">',"\r\n";
    $Limite = $CurLiens + LIMITLIENPARFICHIER;
    while ($CurLiens< $Limite  && $CurLiens<LIMITLIENINDEXE && list(,$file)=each($myfiles)){
      if (strpos($file['lien'], 'index')) {
        $priority = 0.8;
      } else { 
        $priority = 0.5;
      }
      echo '<url> '."\r\n".' <loc>'.$file['lien'].'</loc> '."\r\n ";
      echo '<lastmod>'.$file['date'].'</lastmod>'."\r\n";
      echo '<changefreq>monthly</changefreq>'."\r\n";
      echo '<priority>'.$priority.'</priority></url>'."\r\n";
      $CurLiens ++;
    }
    echo  '</urlset></textarea><br />';
  }
  $numfichier++;
}

if ($numfichier!=1)
{
  echo '<h2>Generate main sitemap file...</h2>';
  if ($fp = fopen('./sitemap.xml', 'w+')){
    fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>'."\r\n");
    fwrite($fp, '<sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84">'."\r\n");
    $date=date('Y-m-d');
    for($k=1;$k<$numfichier;$k++){
      fwrite($fp, '<sitemap>'."\r\n");
      fwrite($fp,'<loc>'.$racine.'/sitemap'.$k.'.xml'.$GZ.'</loc>'."\r\n");
      fwrite($fp, '<lastmod>'.$date.'</lastmod>'."\r\n");
      fwrite($fp,'</sitemap>'."\r\n");
    }
    fwrite($fp, '</sitemapindex>'."\r\n");
    fclose($fp);
    echo '<a href="./sitemap.xml" target="_blank">Generate sitemap.xml</a><br />',"\r\n";
  } else {
    echo '<br /><br /><textarea rows="30" cols="100">',"\r\n"
      ,'<?xml version="1.0" encoding="UTF-8"?>',"\r\n"
      ,'<sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84">',"\r\n";
    $date=date("Y-m-d");
    for($k=1;$k<$numfichier;$k++){
      echo '<sitemap>',"\r\n"
       ,'<loc>',$racine,'/sitemap',$k,'.xml</loc>',"\r\n"
       ,'<lastmod>',$date,'</lastmod>',"\r\n"
       ,'</sitemap>',"\r\n";
    }
    echo '</sitemapindex>',"\r\n";
  }
}
?>
<span class="italic">All done!</span>
</body>
</html>
