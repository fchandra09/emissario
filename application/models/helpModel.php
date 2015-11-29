<?php

class HelpModel extends Model
{

	public function getHelpsForWish($wishID, $wishOwnerID = "")
	{
		$sql = "SELECT Help.*,
					User.First_Name AS Helper_First_Name,
					User.Last_Name AS Helper_Last_Name,
					Review.ID AS Review_ID
				FROM Help
				INNER JOIN Wish ON Wish.ID = Help.Wish_ID
				INNER JOIN User ON User.ID = Help.User_ID
				LEFT JOIN Review ON Review.ID = (SELECT R.ID FROM Review R WHERE R.Help_ID = Help.ID LIMIT 1)
				WHERE Help.Wish_ID = :wish_id";

		if (is_numeric($wishOwnerID)) {
			$sql .= " AND Wish.User_ID = :wish_owner_id";
		}

		$sql .= " ORDER BY Help.Requested DESC, Help.Offered DESC";

		$parameters = array(":wish_id" => $wishID);
		if (is_numeric($wishOwnerID))
		{
			$parameters[":wish_owner_id"] = $wishOwnerID;
		}

		$query = $this->db->prepare($sql);
		$query->execute($parameters);

		return $query->fetchAll();
	}

	public function getHelpsForOthers($userID, $wishStatus = "", $helpStatus = "", $search = "")
	{
		$sql = "SELECT Help.*,
					Wish.Description AS Wish_Description,
					Wish.Destination_City AS Wish_Destination_City,
					Wish.Status AS Wish_Status,
					Country.Country_Name AS Wish_Destination_Country_Name,
					Owner.First_Name AS Wish_Owner_First_Name,
					Owner.Last_Name AS Wish_Owner_Last_Name
				FROM Help
				INNER JOIN Wish ON Wish.ID = Help.Wish_ID
				INNER JOIN User Owner ON Owner.ID = Wish.User_ID
				LEFT JOIN Country ON Country.Country_Code = Wish.Destination_Country
				WHERE Help.User_ID = :user_id
					AND (Wish.Status = 'Open'
						OR (Help.Requested = 1
							AND Help.Offered = 1))";

		if (strcasecmp($wishStatus, "closed") == 0)
		{
			$sql .= " AND Wish.Status = 'Closed'";
		}
		else if (strcasecmp($wishStatus, "not_closed") == 0)
		{
			$sql .= " AND Wish.Status IN ('Open', 'Helped')";
		}

		if (strcasecmp($helpStatus, "accepted") == 0)
		{
			$sql .= " AND Help.Requested = 1
					AND Help.Offered = 1";
		}
		else if (strcasecmp($helpStatus, "offered") == 0)
		{
			$sql .= " AND Help.Requested = 0
					AND Help.Offered = 1";
		}
		else if (strcasecmp($helpStatus, "requested") == 0)
		{
			$sql .= " AND Help.Requested = 1
					AND Help.Offered = 0";
		}

		if (trim($search) != "")
		{
			$sql .= " AND (Wish.Description LIKE :search
						OR Wish.Destination_City LIKE :search
						OR Country.Country_Name LIKE :search
						OR CONCAT(Owner.First_Name, ' ', Owner.Last_Name) LIKE :search)";
		}

		$sql .= " ORDER BY Help.Offered DESC, Help.Requested DESC";

		$parameters = array(":user_id" => $userID);
		if (trim($search) != "")
		{
			$parameters[":search"] = "%" . trim($search) . "%";
		}

		$query = $this->db->prepare($sql);
		$query->execute($parameters);

		return $query->fetchAll();
	}

	public function getHelp($helpID, $userID = "")
	{
		$sql = "SELECT Help.*,
					Wish.Description AS Wish_Description,
					Wish.Destination_City AS Wish_Destination_City,
					Wish.Status AS Wish_Status,
					Wish.Weight AS Wish_Weight,
					Wish.Compensation AS Wish_Compensation,
					DATE_FORMAT(Wish.Max_Date, '%m/%d/%Y') AS Wish_Max_Date,
					Country.Country_Name AS Wish_Destination_Country_Name,
					Owner.ID AS Wish_Owner_ID,
					Owner.First_Name AS Wish_Owner_First_Name,
					Owner.Last_Name AS Wish_Owner_Last_Name,
					Review.Recommended AS Review_Recommended,
					Review.Comments AS Review_Comments
				FROM Help
				INNER JOIN Wish ON Wish.ID = Help.Wish_ID
				INNER JOIN User Owner ON Owner.ID = Wish.User_ID
				LEFT JOIN Country ON Country.Country_Code = Wish.Destination_Country
				LEFT JOIN Review ON Review.Help_ID = Help.ID
				WHERE Help.ID = :help_id";

		if (is_numeric($userID)) {
			$sql .= " AND Help.User_ID = :user_id";
		}

		$parameters = array(":help_id" => $helpID);
		if (is_numeric($userID)) {
			$parameters[":user_id"] = $userID;
		}

		return $GLOBALS["beans"]->queryHelper->getSingleRowObject($this->db, $sql, $parameters);
	}

	public function getPotentialHelpers($wishID, $userID)
	{
		$sql = "SELECT User.ID,
					User.First_Name,
					User.Last_Name,
					User.City,
					State.State_Name,
					Country.Country_Name,
					Travel.Destination_City,
					DATE_FORMAT(Travel.Travel_Date, '%m/%d/%Y') AS Formatted_Travel_Date,
					Dest_Country.Country_Name AS Destination_Country_Name,
					CASE WHEN Friend_Summary.Status IS NULL THEN '4 - non_friends' ELSE Friend_Summary.Status END AS Friend_Status,
					CASE WHEN Review_Summary.Review_Count > 0 THEN ROUND(Review_Summary.Recommended_Count * 100 / Review_Summary.Review_Count) ELSE 0 END AS Recommendation_Score
				FROM User
				INNER JOIN User Me ON Me.ID = :user_id AND Me.ID <> User.ID
				INNER JOIN Wish ON Wish.ID = :wish_id
				LEFT JOIN State ON State.State_Code = User.State AND State.Country_Code = User.Country
				LEFT JOIN Country ON Country.Country_Code = User.Country
				LEFT JOIN Travel ON Travel.ID = (SELECT T.ID
												FROM Travel T
												WHERE T.User_ID = User.ID
													AND T.Travel_Date > DATE(NOW())
												ORDER BY CASE WHEN T.Destination_Country = Wish.Destination_Country THEN 1 ELSE 2 END,
													CASE WHEN LOWER(T.Destination_City) = LOWER(Wish.Destination_City) THEN 1 ELSE 2 END,
													Travel_Date
												LIMIT 1)
				LEFT JOIN Country Dest_Country ON Dest_Country.Country_Code = Travel.Destination_Country
				LEFT JOIN (
					SELECT User_ID2 AS Friend_ID, CASE WHEN Pending = 1 THEN '2 - pending_friend' ELSE '1 - friends' END AS Status
					FROM Friend
					WHERE User_ID1 = :user_id
					UNION
					SELECT User_ID1 AS Friend_ID, CASE WHEN Pending = 1 THEN '3 - pending_mine' ELSE '1 - friends' END AS Status
					FROM Friend
					WHERE User_ID2 = :user_id
				) Friend_Summary ON Friend_Summary.Friend_ID = User.ID
				LEFT JOIN (
					SELECT User_ID, SUM(CASE WHEN Recommended = 1 THEN 1 ELSE 0 END) AS Recommended_Count, COUNT(*) AS Review_Count
					FROM Review
					GROUP BY User_ID
				) Review_Summary ON Review_Summary.User_ID = User.ID
				WHERE NOT EXISTS (
					SELECT Help.ID
					FROM Help
					WHERE Help.User_ID = User.ID
						AND Help.Wish_ID = Wish.ID
				)
				ORDER BY CASE WHEN Travel.Destination_Country = Wish.Destination_Country THEN 1 ELSE 2 END,
					CASE WHEN LOWER(Travel.Destination_City) = LOWER(Wish.Destination_City) THEN 1 ELSE 2 END,
					Friend_Status,
					CASE WHEN Me.Country = User.Country THEN 1 ELSE 2 END,
					CASE WHEN Me.State = User.State THEN 1 ELSE 2 END,
					CASE WHEN LOWER(Me.City) = LOWER(User.City) THEN 1 ELSE 2 END,
					Recommendation_Score DESC,
					Travel.Travel_Date
				LIMIT 50";

		$parameters = array(
				":wish_id" => $wishID,
				":user_id" => $userID
		);

		$query = $this->db->prepare($sql);
		$query->execute($parameters);

		return $query->fetchAll();
	}

	public function insertHelpRequest($helperID) {
		$sql = "INSERT INTO Help (Wish_ID, User_ID, Requested, Offered, Created_By, Created_On, Modified_On)
				VALUES (:wish_id, :helper_id, 1, 0, :user_id, NOW(), NOW())";

		$parameters = array(
				":wish_id" => $_POST["wishID"],
				":helper_id" => $helperID,
				":user_id" => $GLOBALS["beans"]->siteHelper->getSession("userID")
		);

		return $GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

	public function acceptHelpRequest($helpID) {
		$sql = "UPDATE Help
				SET Offered = 1,
					Modified_On = NOW()
				WHERE Help.ID = :help_id";
	
		$parameters = array(":help_id" => $helpID);

		$GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

	public function acceptHelpOffer($helpID) {
		$sql = "UPDATE Help
				SET Requested = 1,
					Modified_On = NOW()
				WHERE Help.ID = :help_id";

		$parameters = array(":help_id" => $helpID);

		$GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

}
