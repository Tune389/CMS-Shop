 <?
	include("../inc/auth.php");
 	//$handle = fopen('../debug/error_log', "w");
	//fwrite($handle, ""); 				
	//fclose($handle);
 //Parameter auslesen für allegemeine Settings
 	if (isset($_GET['s'])) $style = $_GET['s'];
	else $style = "default";
	if (isset($_GET['l'])) $language = $_GET['l']; 
	else $language = "de";
	if (isset($_GET['do'])) $do = $_GET['do'];
	else $do = "";
	if (isset($_GET['action'])) $action = $_GET['action'];
	else $action = "";
	if (isset($_GET['show'])) $show = $_GET['show'];
	else $show = "";

//Allegmeine Pfade setzten durch Resultat der Parameter 
	$error = "";
	$path['include'] = "../inc/";
	$path['style'] = "../style/".$style."/";
	$path['css'] = $path['style']."_css/";
	$path['js'] = $path['style']."_js/";
	$path['style_index'] = $path['style']."index.html";
	$path['images'] = $path['include']."images/"; 
	$path['plugins'] = "../plugins/"; 
	$path['pages'] = "../pages/"; 
	$path['panels'] = "../panels/"; 
	$file['functions'] = $path['include']."functions.php";
	$file['auth'] = $path['include']."auth.php";
	$file['init'] = $path['include']."init.php";
	$path['lang'] = $path['include']."language/";
	$file['mysql'] = $path['include']."mysql.php";
	
	//Loading functions and init
	include($file['mysql']);
	includeFile($file['functions']);	
	includeFile($file['init']);
	
	//Loading Language
	includeFile($path['lang']."global.php");	
	
	$lang_dir = opendir($path['lang'].$language);
	
	//debug("loading language");
	//Loading the panels
	if ( $language != 'de') $language = 'de'; //ToDo Language aus Settings (database)
	
	while ($lang_file = readdir($lang_dir)) 
	{
		if ($lang_file != ".." && $lang_file != ".") 
		{
			//debug("loading $lang_file");
			//Import panel function
			includeFile($path['lang'].$language."/".$lang_file);
		}
	} 
	closedir($lang_dir);

	function includeFile($file)
	{
		$r = true;
		if (file_exists ( $file )) 
		{
			return include($file);
		}
		else 
		{
			error("File not found -".$file);
			$r = false;
		}
		return $r;
	}
	function getFile($file)
	{
		$r = true;
		if (file_exists ( $file )) 
		{
			return file_get_contents($file);
		}
		else 
		{
			error("File not found -".$file);
			$r = false;
		}
		return $r;
	}
	
	function error($this_error)
	{
		global $error;
		$error .= $this_error."<br>";
	}
	
	function errorDisplay()	
	{
		global $error;
		return $error;
	}
 ?>