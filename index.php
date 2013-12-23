<?php

//Config
define("TINYSONG_API_KEY", "6f268490f2878d92b8e91bcfd93bfa2e");

//Help
if(@$argv[1]=="--help" || @$argv[1]=="-h" || @$argv[1]=="help" || !@$argv[1]){
    die("\n Usage: ".$argv[0]." [HTTP Playlist link | HTTP Song link]\n");
}

if($argv[1]){
	//Song
	if(strstr($argv[1], "/track/")){
		$trackId = substr($argv[1], strpos($argv[1], "/track/")+7, 22);
		$data = spotyGetSongByTrackId($trackId);
		if($data){
			if($data['artist'] && $data['name']){
				$search = $data['artist']." ".$data['name'];
				$data = grooveFindSong($search);
				print_r($data);
				if($data->SongID){
					exit;
				}else{
					die("\n Song not found on GrooveShark\n");
				}
			}
		}
	//Playlist
	}else{
		die("\n Playlist detected!\n");
	}
}

function curl($url, $post="", $header=""){
    echo "\n <curl> ".$url."\n";
    $ch = curl_init($url);
    if($header){
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.41 Safari/537.36');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    if($post){
    	curl_setopt($ch, CURLOPT_POST , true);
    	@curl_setopt($ch, CURLOPT_POSTFIELDS , $post);
    }
    curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $exec = curl_exec($ch);
    curl_close($ch);
    return $exec;
}

function spotyGetSongByTrackId($trackId){
	$res = curl("http://ws.spotify.com/lookup/1/?uri=spotify:track:".$trackId);
	if($res){
		$xml = simplexml_load_string($res);
		$data['artist'] = (string)$xml->artist->name;
		$data['name'] = (string)$xml->name;
		$data['album'] = (string)$xml->album->name;
		return $data;
	}
}

function grooveFindSong($string){
	$string = urlencode($string);
	$res = curl("http://tinysong.com/b/".$string."?format=json&key=".TINYSONG_API_KEY);
	if($res){
		return json_decode($res);
	}
}