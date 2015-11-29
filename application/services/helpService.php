<?php

class HelpService extends Service
{

	public function getHelpsForWish($wishID, $wishOwnerID = "")
	{
		return $this->model->getHelpsForWish($wishID, $wishOwnerID);
	}

	public function getHelpsForOthers($userID, $wishStatus = "", $helpStatus = "", $search = "")
	{
		return $this->model->getHelpsForOthers($userID, $wishStatus, $helpStatus, $search);
	}

	public function getHelp($helpID, $userID = "")
	{
		return $this->model->getHelp($helpID, $userID);
	}

	public function getPotentialHelpers($wishID, $userID)
	{
		return $this->model->getPotentialHelpers($wishID, $userID);
	}

	public function insertHelpRequests()
	{
		foreach (explode(",", $_POST["helperIDs"]) as $helperID) {
			$this->model->insertHelpRequest($helperID);
		}
	}

}