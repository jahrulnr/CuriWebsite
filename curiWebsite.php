<?php 
	
	/*
		Github : https://github.com/jahrulnr
		Git Files : https://github.com/jahrulnr/CuriWebsite
	*/

	// Global var
	$list_files = [];

	if(empty($_GET['curi']) && !isset($_GET['intip'])){ 
		?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CuriWebsite</title>
</head>
<body style="width: 700px;margin: 3rem auto;">
	<table class="form" border="0">
		<tr>
			<td>Alamat Website</td>
			<td>: <input type="text" name="url" placeholder="http(s)://...." /></td>
		</tr>
		<tr>
			<td>Mode Desktop</td>
			<td>: <input type="checkbox" name="mode_web"/></td>
		</tr>
		<tr>
			<td colspan="2" style="text-align: right;"><button type="submit" id="gas">Gas!</button></td>
		</tr>
		<tr>
			<td colspan="2">
				<small>* Masih banyak bug jika mencuri file yg mengandung<br/> get (?blabla) dan framework seperti CI atau laravel.<br/> Silakan perbaiki jika mau, tapi ku rasa mencuri <br/>sedikit aja sudah cukup.</small>
			</td>
		</tr>
	</table>

	<!-- Proses -->
	<div class="proses" style="margin-top: 1rem; border-bottom: 1px solid black;">Proses akan muncul disini.</div>

	<!-- Hasil -->
	<div class="hasil" style="margin-top: 1rem">
		...
	</div>

	<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
	<script type="text/javascript">
		var __FILE__ = "<?=basename($_SERVER['PHP_SELF'])?>";

		function intip(url_web){
			if($(".proses").html() != "Selesai")
				$.ajax({
					url : __FILE__ + "?intip="+url_web,
					success : function(resp){
						var data = '';
						$.each(resp, function(i, v){
							data += "[<span style='color: green;'>sukses</span>]" + v + "<br/>";
						});
						$(".hasil").html(data);
					}
				});
		}

		$("#gas").click(function(){
			var url_web = $("input[name='url']").val();
			var desktop = $("input[name='mode_web']").val();
			$.ajax({
				url : __FILE__ + "?curi=" + url_web + "&desktop=" + desktop,
				success : function(resp){
					$(".proses").html("Selesai");
				},
				beforeSend : function(xhr){
					$(".proses").html("Tunggu sebentar ...");
				    setInterval('intip("'+url_web+'")', 1000);
				}
			});
		});
	</script>
</body>
</html>
<?php
	}elseif(isset($_GET['intip'])){
		$url = parse_url($_GET['intip'])['host'];
		$files = $url . "/list_files.json";
		if(is_dir($url)){
			header("Content-Type: application/json");
			echo is_file($files) ? file_get_contents($files) : "[]";
			exit;
		}
	}else{
	
		//bug di get query

		//Setting
		@error_reporting(~E_NOTICE & ~E_WARNING);
		@set_time_limit(200);
		@ignore_user_abort();
		$desktop = $_GET['desktop'];
		$situs = $_GET['curi'];

		//setDefine
		$pSitus = parse_url($situs);
		define('cookie', 'cookie.dat');
		define('domain', $pSitus['host']);
		define('desktop', $desktop);

		$start = array(
			'src="',
			"src='",
			'href="',
			"href='"
		);

		$end = array(
			'"', "'",
			'"', "'"
		);

		$cKhusus = array(
			"?",
			"&",
			"<",
			">",
			"|"
		);

		is_dir(domain) ? null : mkdir(domain);
		curiWebsite($situs, $start, $end);
	}

	//Function
	function getURL($url, $info = false){
		$user_agent = desktop ? 
			"Mozilla/5.0 (X11; CrOS x86_64 8172.45.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.64 Safari/537.36" :
			"Mozilla/5.0 (Linux; Android 8.0.0; SM-G960F Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.84 Mobile Safari/537.36";
		$ch = curl_init(); 
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_USERAGENT => $user_agent,
			CURLOPT_COOKIEJAR => cookie,
			CURLOPT_COOKIEFILE => cookie,
			CURLOPT_FOLLOWLOCATION => true
		));
		$output = curl_exec($ch);
		$type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		$type = substr($type, 5, 4);
		curl_close($ch);
		return $info ? $type : $output;
	}

	function quoteReplace($text, $key){
		if (is_array($text)){
			foreach($text as $temp){
				$keywords[] = preg_quote($temp, $key);
			}
		}
		else{
			$keywords[] = preg_quote($text, $key);
		}
		return $keywords;
	}

	function getLink($source, $start, $end){
		$start = quoteReplace($start, "/");
		$end = quoteReplace($end, "/");
		$tmp1 = array();
		$tmp2 = array();
		for($i=0;$i<count($start);$i++){
			$filter = $start[$i] . "(.*?)" . $end[$i];
			preg_match_all("/$filter/", $source, $match);
			if($match[0][0] != null){
				$tmp1 = array_merge($tmp1, $match[1]);
				foreach($tmp1 as $link_tmp){
					$oLink = $link_tmp;
					$uLink = parse_url($link_tmp);
					if(
						$uLink['host'] == domain
						|| substr($link_tmp, 0, 1) == "/"
					){
						$link_tmp = parse_url($link_tmp);
						$link_tmp = $link_tmp['path'];
						$link_tmp = substr($link_tmp, 0, 1) == "/" ? 
							substr($link_tmp, 1) : $link_tmp;
						$link_tmp = empty($link_tmp) ? "index.php" : $link_tmp;
					}
					if (
						!empty($link_tmp) && substr($link_tmp, 0, 1) != "#"
						&& substr($link_tmp, 0, 4) != "http" 
					){
						$link1[] = $oLink;
						$link2[] = $link_tmp;	
					} 
				}
			}
		}
		return array($link1, $link2);
	}

	function saveFile($path, $source){
		global $list_files;

		if(substr($path, 0, 4) == 'data') return;
		$filename = empty($path) ? "index.php" : $path;
		$filename = domain.'/'.$filename;
		$path = dirname($filename);
		$filenameExt = explode("/", $filename);
		$filenameExt = htmlspecialchars($filenameExt[count($filenameExt)-1], ENT_COMPAT,'ISO-8859-1', true);
		$filename = $path . "/" . $filenameExt;
		if(strpos($path, "/") > 0){
			$paths = explode("/", $path);
			$i=0;
			for($i;$i<count($paths)-1; $i++){
				if(!is_dir($paths[$i])){
					mkdir($paths[$i]);
				} 
				$paths[$i+1] = $paths[$i] . '/' . $paths[$i+1];
			}
			if(!is_dir($paths[$i])){
				mkdir($paths[$i]);
			}
		}
		$sFile = fopen($filename, 'w');
		if($sFile){
			fwrite($sFile, $source);
			fclose($sFile);

			// listed
			$list_files[] = $filename;
			file_put_contents(domain."/list_files.json", json_encode($list_files));
		}else
		echo $filename . " [gagal]<br/>";
	}

	function getFile($url, $start, $end){
		$type = getURL($url, true);
		$source = getURL($url);
		$source = str_replace('"/"', '"/index.php"', $source);
		$source = str_replace("'/'", "'/index.php'", $source);
		$getLink = getLink($source, $start, $end);
		$source = str_replace($getLink[0], $getLink[1], $source);
		$url_ = parse_url($url);
		if($url_['path'] != "/"){
			$path = substr($url_['path'], 0, 1) == "/" ? 
				substr($url_['path'], 1) : $url_['path'];
			$path = substr($path, -1) == '/' ?
				$path . "index.php" : $path;
		}
		else{
			$path = "index.php";
		}
		saveFile($path, $source);
		// echo "$path [$type]<br/>";
		return array($source, $path, $type, $getLink[1]);
	}

	function curiWebsite($url, $start, $end){
		$url_ = parse_url($url);
		$url_['path'] = $url_['path'] == "/" ? $url_['path'] . "index.php" : $url_['path'];
		if(!file_exists(substr($url_['path'], 1))){
			$getFile = getFile($url, $start, $end);
			if($getFile[1] != "/"){
				$path = substr($getFile[1], 0, 1) == '/' ?
					substr($getFile[1], 1) : $path;
				$path = substr($path, -1) == '/' ?
					$path . "index.php" : $path;
			}else{
				$path = "index.php";
			}
			if($getFile[2] == "html"){
				foreach($getFile[3] as $File)
					curiWebsite($url_['scheme']."://".$url_['host']."/".$File, $start, $end);
			}
		}
	}
?>

