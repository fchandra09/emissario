<?php

class WishModel extends Model
{

	public function getWishes($userID, $wishStatus = "", $search = "")
	{
		$sql = "SELECT Wish.*,
					Country.Country_Name AS Destination_Country_Name
				FROM Wish
				LEFT JOIN Country ON Country.Country_Code = Wish.Destination_Country
				WHERE Wish.User_ID = :user_id";

		if (strcasecmp($wishStatus, "closed") == 0)
		{
			$sql .= " AND Wish.Status = 'Closed'";
		}
		else if (strcasecmp($wishStatus, "not_closed") == 0)
		{
			$sql .= " AND Wish.Status IN ('Open', 'Helped')";
		}

		if (trim($search) != "")
		{
			$sql .= " AND (Wish.Description LIKE :search
						OR Wish.Destination_City LIKE :search
						OR Country.Country_Name LIKE :search)";
		}
		
		$sql .= " ORDER BY Wish.Created_On";

		$parameters = array(":user_id" => $userID);
		if (trim($search) != "")
		{
			$parameters[":search"] = "%" . trim($search) . "%";
		}

		$query = $this->db->prepare($sql);
		$query->execute($parameters);

		return $query->fetchAll();
	}

	public function getWish($wishID, $userID = "", $wishStatus = "")
	{
		$sql = "SELECT Wish.*,
					Country.Country_Name AS Destination_Country_Name
				FROM Wish
				LEFT JOIN Country ON Country.Country_Code = Wish.Destination_Country
				WHERE Wish.ID = :wish_id";

		if (is_numeric($userID)) {
			$sql .= " AND Wish.User_ID = :user_id";
		}

		if ($wishStatus != "") {
			$sql .= " AND Wish.Status = :status";
		}

		$parameters = array(':wish_id' => $wishID);
		if (is_numeric($userID)) {
			$parameters[":user_id"] = $userID;
		}
		if ($wishStatus != "") {
			$parameters[":status"] = $wishStatus;
		}

		return $GLOBALS["beans"]->queryHelper->getSingleRowObject($this->db, $sql, $parameters);
	}

	public function insertWish() {
		$sql = "INSERT INTO Wish (User_ID, Description, Weight, Destination_City, Destination_Country, Compensation, Status, Created_On, Modified_On)
				VALUES (:user_id, :description, :weight, :destination_city, :destination_country, :compensation, 'Open', NOW(), NOW())";

		$parameters = array(
				":user_id" => $_POST["userID"],
				":description" => $_POST["description"],
				":weight" => $_POST["weight"],
				":destination_city" => $_POST["destinationCity"],
				":destination_country" => $_POST["destinationCountry"],
				":compensation" => $_POST["compensation"]
			);

		return $GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

	public function updateWish() {
		$sql = "UPDATE Wish
				SET Description = :description,
					Weight = :weight,
					Destination_City = :destination_city,
					Destination_Country = :destination_country,
					Compensation = :compensation,
					Modified_On = NOW()
				WHERE Wish.ID = :wish_id
					AND Wish.User_ID = :user_id";

		$parameters = array(
				":wish_id" => $_POST["wishID"],
				":user_id" => $_POST["userID"],
				":description" => $_POST["description"],
				":weight" => $_POST["weight"],
				":destination_city" => $_POST["destinationCity"],
				":destination_country" => $_POST["destinationCountry"],
				":compensation" => $_POST["compensation"]
			);

		$GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

	public function deleteWish($wishID, $userID) {
		$sql = "DELETE
				FROM Wish
				WHERE Wish.ID = :wish_id
					AND Wish.User_ID = :user_id
					AND Wish.Status = 'Open'";

		$parameters = array(
				":wish_id" => $wishID,
				":user_id" => $userID
			);

		$GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

	public function updateWishStatus($wishID, $status, $userID = "") {
		$sql = "UPDATE Wish
				SET Status = :status,
					Modified_On = NOW()
				WHERE Wish.ID = :wish_id";

		if (is_numeric($userID))
		{
			$sql .= " AND Wish.User_ID = :user_id";
		}

		if (strcasecmp("Helped", $status) == 0)
		{
			/* Only Open wish can be changed to Helped */
			$sql .= " AND Wish.Status = 'Open'";
		}
		elseif (strcasecmp("Closed", $status) == 0)
		{
			/* Only Helped wish can be changed to Closed */
			$sql .= " AND Wish.Status = 'Helped'";
		}

		$parameters = array(
				":wish_id" => $wishID,
				":status" => $status,
		);
		if (is_numeric($userID)) {
			$parameters[":user_id"] = $userID;
		}

		$GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

	public function getPotentialWishesToHelp($userID)
	{
		$sql = "SELECT Wish.ID,
					Wish.Description,
					Wish.Destination_City,
					Wish_Dest_Country.Country_Name AS Destination_Country_Name,
					Wish.Weight,
					Wish.Compensation,
					Owner.ID AS Owner_ID,
					Owner.First_Name AS Owner_First_Name,
					Owner.Last_Name AS Owner_Last_Name,
					Owner.City AS Owner_City,
					State.State_Name AS Owner_State_Name,
					Country.Country_Name AS Owner_Country_Name,
					CASE WHEN Friend_Summary.Status IS NULL THEN '4 - non_friends' ELSE Friend_Summary.Status END AS Friend_Status
				FROM Wish
				INNER JOIN User Owner ON Owner.ID = Wish.User_ID
				INNER JOIN User Me ON Me.ID = :user_id AND Me.ID <> Owner.ID
				LEFT JOIN State ON State.State_Code = Owner.State AND State.Country_Code = Owner.Country
				LEFT JOIN Country ON Country.Country_Code = Owner.Country
				LEFT JOIN Country Wish_Dest_Country ON Wish_Dest_Country.Country_Code = Wish.Destination_Country
				LEFT JOIN Travel ON Travel.ID = (SELECT T.ID
												FROM Travel T
												WHERE T.User_ID = Me.ID
													AND T.Travel_Date > DATE(NOW())
													AND (T.Destination_Country = Wish.Destination_Country
														OR LOWER(T.Destination_City) = LOWER(Wish.Destination_City))
												ORDER BY CASE
														WHEN T.Destination_Country = Wish.Destination_Country AND T.Origin_Country = Owner.Country THEN 1
														WHEN T.Origin_Country = Wish.Destination_Country AND T.Destination_Country = Owner.Country THEN 1
														ELSE 2
													END,
													CASE
														WHEN LOWER(T.Destination_City) = LOWER(Wish.Destination_City) AND LOWER(T.Origin_City) = LOWER(Owner.City) THEN 1
														WHEN LOWER(T.Origin_City) = LOWER(Wish.Destination_City) AND LOWER(T.Destination_City) = LOWER(Owner.City) THEN 1
														ELSE 2
													END,
													Travel_Date
												LIMIT 1)
				LEFT JOIN (
					SELECT User_ID2 AS Friend_ID, CASE WHEN Pending = 1 THEN '2 - pending_friend' ELSE '1 - friends' END AS Status
					FROM Friend
					WHERE User_ID1 = :user_id
					UNION
					SELECT User_ID1 AS Friend_ID, CASE WHEN Pending = 1 THEN '3 - pending_mine' ELSE '1 - friends' END AS Status
					FROM Friend
					WHERE User_ID2 = :user_id
				) Friend_Summary ON Friend_Summary.Friend_ID = Owner.ID
				WHERE Wish.Status = 'Open'
					AND NOT EXISTS (
						SELECT Help.ID
						FROM Help
						WHERE Help.User_ID = Me.ID
							AND Help.Wish_ID = Wish.ID
					)
				ORDER BY CASE
						WHEN Travel.Destination_Country = Wish.Destination_Country AND Travel.Origin_Country = Owner.Country THEN 1
						WHEN Travel.Origin_Country = Wish.Destination_Country AND Travel.Destination_Country = Owner.Country THEN 1
						ELSE 2
					END,
					CASE
						WHEN LOWER(Travel.Destination_City) = LOWER(Wish.Destination_City) AND LOWER(Travel.Origin_City) = LOWER(Owner.City) THEN 1
						WHEN LOWER(Travel.Origin_City) = LOWER(Wish.Destination_City) AND LOWER(Travel.Destination_City) = LOWER(Owner.City) THEN 1
						ELSE 2
					END,
					Friend_Status,
					CASE WHEN Me.Country = Owner.Country THEN 1 ELSE 2 END,
					CASE WHEN Me.State = Owner.State THEN 1 ELSE 2 END,
					CASE WHEN LOWER(Me.City) = LOWER(Owner.City) THEN 1 ELSE 2 END,
					Travel.Travel_Date
				LIMIT 50";

		$parameters = array(":user_id" => $userID);

		$query = $this->db->prepare($sql);
		$query->execute($parameters);

		return $query->fetchAll();
	}

}
