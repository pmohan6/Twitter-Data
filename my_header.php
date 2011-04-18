<?php

require_once('twitteroauth.php');


class Twitter
{
	public $stream_url = "http://stream.twitter.com/1/statuses/filter.json?track=basketball";
	public $sample_url = "http://stream.twitter.com/1/statuses/sample.json";
	public $search_url = "http://search.twitter.com/search.json?q=basketball&rpp=100&result_type=mixed";
	public $connection;
	public $consumer_key='DqaxeDCaXrsr0tsb0CompA';
	public $consumer_secret='C5A7QCBjfONah8YtC3SShNyb3rWyCRFKDVbxIEh6q78';
	public $oauth_token='30822083-qKIeiD1COXY9nnsdpejdxjiid12UbgdmDT9fjTyI9';
	public $oauth_token_secret='FxYnwBC6mBPRMPJgxbrIASZdz9yz5zO83OeqL8GvmY';
	//ENTER YOUR TWITTER USERNAME AND PASSWORD
	//You ONLY have to enter your twitter name and password if you are using the Twitter STREAMING API
	//i.e., if you call the Twitter->stream_request() function below.
	public $username = '';
	public $password = '';
	
	function yql_search()
	{
		$ch=curl_init($yql_search_url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, true);
		$data=curl_exec($ch);
		$info=curl_getinfo($ch);
		curl_close($ch);
		print_r($info);
		return $data;
	}
	
	function search()
	{
		$url = $this->search_url;
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		//curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,30);
		$data=curl_exec($curl);
		$info=curl_getinfo($curl);
		print_r($info);
		curl_close($curl);
		return $data;
	}
	
	function stream_request()
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
		curl_setopt($curl, CURLOPT_URL, $this->stream_url);
		curl_setopt($curl, CURLOPT_WRITEFUNCTION, print_stream);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_exec($curl);
	//	$info = curl_getinfo($curl);
	//	curl_close($curl);
	//	print_r($info);
	}
	
	
	function getConnectionWithAccessToken()
	{
		$this->connection = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->oauth_token, $this->oauth_token_secret);
	}

}


?>
