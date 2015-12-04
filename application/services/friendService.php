<?php

class FriendService extends Service
{

	public function getFriends($userID, $friendType, $search = "")
	{
		return $this->model->getFriends($userID, $friendType, $search);
	}

	public function getFriend($friendID, $userID = "")
	{
		return $this->model->getFriend($friendID, $userID);
	}

	public function deleteFriend($friendID, $userID)
	{
		$this->model->deleteFriend($friendID, $userID);
	}

	public function searchPotentialFriends($search, $userID)
	{
		return $this->model->searchPotentialFriends($search, $userID);
	}

	public function saveFriends()
	{
		foreach (explode(",", $_POST["friendIDs"]) as $friendID) {
			$this->model->insertFriend($friendID);
		}
	}

	public function acceptFriend($friendID, $userID)
	{
		$this->model->acceptFriend($friendID, $userID);
	}

	public function &getShortestPath($userID, $targetUserID)
	{
		$maxDegree = 5;
		$targetNodes = array();
		$root = new Node($userID, "Me");
		$currentNodes = array($root);
		$traversed = array($userID);

		if ($userID == $targetUserID)
		{
			$targetNodes[] = $root;
			return $targetNodes;
		}

		for ($degree = 1; $degree <= $maxDegree; $degree++)
		{
			$nextNodes = array();
			$degreeTraversed = array();

			foreach ($currentNodes as $currentNode) {
				$friends = $this->getFriends($currentNode->getKey(), "friends");

				foreach ($friends as $friend) {
					$childNode = new Node($friend->ID, $friend->First_Name . " " . $friend->Last_Name);
					$childNode->setParent($currentNode);
					$currentNode->addChild($childNode);

					if (!in_array($friend->ID, $traversed)) {
						$nextNodes[] = $childNode;
						if (!in_array($friend->ID, $degreeTraversed))
						{
							$degreeTraversed[] = $friend->ID;
						}
					}

					if ($friend->ID == $targetUserID)
					{
						$targetNodes[] = $childNode;
					}
				}
			}

			if (count($targetNodes) > 0) {
				break;
			}

			$currentNodes = $nextNodes;
			array_push($traversed, $degreeTraversed);
		}

		return $targetNodes;
	}

	public function getGraphDataset(&$targetNodes)
	{
		$nodes = array();
		$edges = array();
		$edgePairs = array();
		$maxDegree = 0;

		foreach ($targetNodes as $targetNode)
		{
			if (!array_key_exists($targetNode->getKey(), $nodes))
			{
				$nodes[$targetNode->getKey()] = $targetNode->getData();
			}

			$degree = 0;
			$childNode = $targetNode;
			while (!is_null($childNode->getParent()))
			{
				$parentNode = $childNode->getParent();
				$degree += 1;

				/* Add node */
				if (!array_key_exists($parentNode->getKey(), $nodes))
				{
					$nodes[$parentNode->getKey()] = $parentNode->getData();
				}

				/* Add edge */
				if (!in_array($parentNode->getKey() . $childNode->getKey(), $edgePairs)) {
					$edge = new stdClass();
					$edge->From = $parentNode->getKey();
					$edge->To = $childNode->getKey();
					$edges[] = $edge;

					$edgePairs[] = $parentNode->getKey() . $childNode->getKey();
					$edgePairs[] = $childNode->getKey() . $parentNode->getKey();
				}

				$childNode = $parentNode;
			}

			if ($degree > $maxDegree)
			{
				$maxDegree = $degree;
			}
		}

		$result = new stdClass();
		$result->Nodes = $nodes;
		$result->Edges = $edges;
		$result->Max_Degree = $maxDegree;
		$result->Max_Branch = count($targetNodes);

		return $result;
	}

	public function getFriendsYouMightKnow($userID)
	{
		$mapResult = $this->mapFriendRecommendations($userID, true);
		$recommendations = $this->reduceFriendRecommendations($mapResult->Recommendation_Map, $userID);

		/* Remove pending friends from recommendations */
		$pendingFriends = $this->model->getPendingFriendIDs($userID);
		foreach ($pendingFriends as $pendingFriend)
		{
			if (array_key_exists($pendingFriend->Friend_ID, $recommendations))
			{
				unset($recommendations[$pendingFriend->Friend_ID]);
			}
		}

		$selectedRecords = array();
		foreach ($recommendations as $recommendedFriendID => $mutualFriendCount)
		{
			$selectedRecords[] = $mapResult->Query_Records[$recommendedFriendID];
		}

		return $selectedRecords;
	}

	public function &mapFriendRecommendations($userID, $recursive = false)
	{
		$recommendationMap = array();
		$queryRecords = array();
		$friends = $this->getFriends($userID, "friends");
		$friendCount = count($friends);

		for ($i = 0; $i < $friendCount; $i++)
		{
			$queryRecords[$friends[$i]->ID] = $friends[$i];

			$map = new stdClass();
			$map->User_ID = $userID;
			$map->Recommended_Friend_ID = $friends[$i]->ID;
			$map->Mutual_Friend_ID = -1;
			$recommendationMap[] = $map;

			$map = new stdClass();
			$map->User_ID = $friends[$i]->ID;
			$map->Recommended_Friend_ID = $userID;
			$map->Mutual_Friend_ID = -1;
			$recommendationMap[] = $map;

			for ($j = $i + 1; $j < $friendCount; $j++)
			{
				$map = new stdClass();
				$map->User_ID = $friends[$i]->ID;
				$map->Recommended_Friend_ID = $friends[$j]->ID;
				$map->Mutual_Friend_ID = $userID;
				$recommendationMap[] = $map;

				$map = new stdClass();
				$map->User_ID = $friends[$j]->ID;
				$map->Recommended_Friend_ID = $friends[$i]->ID;
				$map->Mutual_Friend_ID = $userID;
				$recommendationMap[] = $map;
			}

			if ($recursive)
			{
				$recursiveResult = $this->mapFriendRecommendations($friends[$i]->ID, false);
				$recommendationMap = array_merge($recommendationMap, $recursiveResult->Recommendation_Map);
				$queryRecords = $queryRecords + $recursiveResult->Query_Records;
			}
		}

		$result = new stdClass();
		$result->Recommendation_Map = $recommendationMap;
		$result->Query_Records = $queryRecords;

		return $result;
	}

	public function reduceFriendRecommendations(&$recommendationMap, $userID)
	{
		/* Associative array of associative array. [User ID: [Recommended user ID: mutual count]]
		 * For example: ["1": ["3": 3, "5": 2]
		 * 				 "2": ["7": 4, "5": 1]]*/
		$reducedRecommendations = array();

		/* Associative array of array. [User ID: [Do not recommend user ID]]
		 * For example: ["1": ["4", "7", "10"],
		 * 				 "2": ["3", "4"]] */
		$doNotRecommend = array();

		foreach ($recommendationMap as $map)
		{
			$personID = $map->User_ID;
			$recommendedFriendID = $map->Recommended_Friend_ID;
			$mutualFriendID = $map->Mutual_Friend_ID;

			unset($recommendationArray);
			unset($doNotRecommendArray);

			if (array_key_exists($personID, $reducedRecommendations))
			{
				$recommendationArray = &$reducedRecommendations[$personID];
			}
			else {
				$recommendationArray = array();
				$reducedRecommendations[$personID] = &$recommendationArray;
			}

			if (array_key_exists($personID, $doNotRecommend))
			{
				$doNotRecommendArray = &$doNotRecommend[$personID];
			}
			else {
				$doNotRecommendArray = array();
				$doNotRecommend[$personID] = &$doNotRecommendArray;
			}

			if (($mutualFriendID != -1) && (!in_array($recommendedFriendID, $doNotRecommendArray)))
			{
				if (array_key_exists($recommendedFriendID, $recommendationArray))
				{
					$recommendationArray[$recommendedFriendID]++;
				}
				else
				{
					$recommendationArray[$recommendedFriendID] = 1;
				}
			}

			/* Already friends so do not recommend anymore */
			elseif ($mutualFriendID == -1)
			{
				if (!in_array($recommendedFriendID, $doNotRecommendArray))
				{
					$doNotRecommendArray[] = $recommendedFriendID;
				}

				if (array_key_exists($recommendedFriendID, $recommendationArray))
				{
					unset($recommendationArray[$recommendedFriendID]);
				}
			}
		}

		$recommendations = $reducedRecommendations[$userID];
		arsort($recommendations);

		return $recommendations;
	}

}