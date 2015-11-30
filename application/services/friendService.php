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

	public function &getShortestPath($userID, $targetUserID) {
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

}