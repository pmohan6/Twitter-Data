<?php
require 'my_header.php';

$twitter = new Twitter();

$file_name = "twitterdata.json";
$file_handle = fopen($file_name, 'r');
$relevant_tweets = array();
$tree = array();
$global_keys = array();

/*
$search_data_json = $twitter->search();
$search_data_temp = json_decode($search_data_json, true);

if(array_key_exists("results", $search_data_temp))
{
	$search_data = $search_data_temp["results"];
}
else
{
	$search_data = $search_data_temp;
}

for($i = 0; $i < count($search_data); $i++)
{
	$id = $search_data[$i]["id"];

	$twitter_status_url = "https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20twitter.status%20where%20id%3D'$id'%3B&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
//	$twitter_status_url = "https://query.yahooapis.com/v1/yql?q=select%20*%20from%20twitter.status%20where%20id%3D'$id'%3B&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
	
	$curl = curl_init($twitter_status_url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_POST, true);
	$yql_response = curl_exec($curl);
	$info = curl_getinfo($curl);
	curl_close($curl);
//	print_r($info);
//	print"<br>";
	
	$yql_response = json_decode($yql_response, true);
	$data = $yql_response["query"]["results"]["status"];
	//print_r($data);

	$data["children"] = array();
	$data["parent"] = $data["in_reply_to_status_id"];
	
	if($data["in_reply_to_status_id"] != null || $data["retweet_count"] > 0)
	{
		array_push($relevant_tweets, $data);
	}
	else
	{
		$data["type"] = "root";
		$global_keys[$data["id"]] = true;
		array_push($tree, $data);
	}
}*/
//To use the above code (twitter search api and yql twitter.status table)....comment out the following for loop and uncomment everything above this line.
for($i = 0; $i < count(file($file_name)); $i++)
{
	$line = fgets($file_handle);
	$data = json_decode($line, true);
	$data["children"] = array();
	$data["parent"] = $data["in_reply_to_status_id"];

	if($data["in_reply_to_status_id"] != null || $data["retweet_count"] > 0)
	{
		array_push($relevant_tweets, $data);
	}
	else
	{
		$data["type"] = "root";
	//	$global_keys[$data["id"]] = true;
		array_push($tree, $data);
	}
}


fclose($file_handle);
	
$twitter->getConnectionWithAccessToken();

while(!empty($relevant_tweets))
{
	$relevant_tweet = array_pop($relevant_tweets);
	if($relevant_tweet["retweet_count"] > 0)
	{
		$relevant_tweet = recursive_retweet($relevant_tweet);

		if($relevant_tweet["in_reply_to_status_id"] == null)
		{
//			$global_keys[$relevant_tweet["id"]] = true;
			array_push($tree, $relevant_tweet);
		}
	}
	if($relevant_tweet["in_reply_to_status_id"] != null)
	{
	//	$global_keys[$relevant_tweet["id"]] = true;
		array_push($tree, recursive_reply($relevant_tweet));
	}
}

print "\n\nTREE in array format\n\n";
$final_tree = array();
for($i = 0; $i < count($tree); $i++)
{
	$final_tree[$i]["id"] = $tree[$i]["id"];
	$final_tree[$i]["type"] = $tree[$i]["type"];
	$final_tree[$i]["children"] = $tree[$i]["children"];
	$final_tree[$i]["name"] = $tree[$i]["id_str"];
	$final_tree[$i]["parent"] = $tree[$i]["parent"];
	$final_tree[$i]["data"] = $tree[$i];
}

//print_r($final_tree);

print "\n\nTREE in json format\n\n";
print_r(json_encode($final_tree));

function recursive_reply($node)
{
	global $twitter;
	global $relevant_tweets;
	global $global_keys;

	$node_id = $node["id"];

	if($node["in_reply_to_status_id"] != null)
	{
		$parent_id = $node["in_reply_to_status_id"];
		$node["parent"] = $parent_id;
		
		//Getting the parent node using "in_reply_to_status_id"
		//Replace the following line if you want to use YQL twitter.status table.
		$parent = $twitter->connection->get("statuses/show/$parent_id");
		
		/*(if(!array_key_exists($parent["id"], $global_keys))
		{
			$global_keys[$parent["id"]] = true;
			
		}*/
		
		$node["type"] = "reply";
	
		if(!array_key_exists("error", $parent))
		{
			$parent["children"] = array();
			array_push($parent["children"], $node_id);
			array_push($relevant_tweets, $parent);
		}
		else
		{	//Uncomment the following line to look for errors from twitter api.
			//print $parent["error"]."\n";
		}
	}	
	
	return $node;

}

function recursive_retweet($node)
{
	global $twitter;
	global $global_keys;
	global $relevant_tweets;
	global $tree;
	
	$node_id = $node["id"];
	if($node["retweet_count"]>0)
	{
		//Getting Retweets for a particular tweet.
		//Replace the following line if you want to use YQL twitter.retweets table.
		$retweets =	$twitter->connection->get("statuses/retweets/$node_id");
		if(!array_key_exists("error", $retweets))
		{
			//print_r($retweets);
			for($i = 0; $i < count($retweets); $i++)
			{
				$retweets[$i]["type"] = "retweet";
				$retweets[$i]["parent"] = $node_id;
				array_push($node["children"], $retweets[$i]["id"]);
				array_push($tree, $retweets[$i]);
			}			
		}
		else
		{	//Uncomment the following line to look for errors from twitter api.
			//print $retweets["error"]."\n";
		}
	}
	return $node;
}


?>