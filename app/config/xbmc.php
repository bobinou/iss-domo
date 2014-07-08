<?php

	/**
	* Fix the XBMC url and port and options available
	* You can Enable or Disable Movies or Songs
	*/

$xbmc_url = 'http://192.168.0.26:8080/jsonrpc';
$xbmc_movies = 1;
$xbmc_songs = 1;

//Test if XBMC is UP
$ch = curl_init($xbmc_url);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);
$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if (200==$retcode) {
        $xbmc_status = 1;
} else {
        $xbmc_status = 0;
}

return array(

	'xbmc_url' => $xbmc_url,
	'xbmc_movies' => $xbmc_movies,
	'xbmc_songs' => $xbmc_songs,
	'xbmc_status' => $xbmc_status,
	
    'args-movies' => array(
        'request' => '{"jsonrpc":"2.0","method":"VideoLibrary.GetMovies","id":"libMovies"}'
    ),
	'args-songs' => array(
        'request' => '{"jsonrpc":"2.0","method":"AudioLibrary.GetSongs","id":"libSongs"}'
    ),
	'clear_playlist' => array(
        'request' => '{"jsonrpc":"2.0","id":0,"method":"Playlist.Clear","params":{"playlistid":0}}'
    ),
	'play_playlist' => array(
        'request' => '{"jsonrpc": "2.0","id": 2,"method":"Player.Open","params":{"item":{"playlistid":0}}}'
    ),
	'what_is_playing' => array(
        'request' => '{"jsonrpc":"2.0","method":"Player.GetItem","params":{"properties":["title","streamdetails"],"playerid":0},"id":"AudioGetItem"}'
    )

);
