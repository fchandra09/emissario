<?php

class ReviewModel extends Model
{

	public function getReviews($userID, $reviewType, $recommended = "")
	{
		$sql = "SELECT Review.*,
					Help.Wish_ID,
					User.First_Name AS User_First_Name,
					User.Last_Name AS User_Last_Name,
					Reviewer.First_Name AS Reviewer_First_Name,
					Reviewer.Last_Name AS Reviewer_Last_Name
				FROM Review
				INNER JOIN Help ON Help.ID = Review.Help_ID
				INNER JOIN User ON User.ID = Review.User_ID
				LEFT JOIN User Reviewer ON Reviewer.ID = Review.Created_By
				WHERE ";

		if (strcasecmp($reviewType, "written") == 0)
		{
			$sql .= " Review.Created_By = :user_id";
		}
		else
		{
			$sql .= " Review.User_ID = :user_id";
		}

		if (is_numeric($recommended))
		{
			$sql .= " AND Review.Recommended = :recommended";
		}

		$sql .= " ORDER BY Review.Created_On DESC";

		$parameters = array(":user_id" => $userID);
		if (is_numeric($recommended))
		{
			$parameters[":recommended"] = $recommended;
		}

		$query = $this->db->prepare($sql);
		$query->execute($parameters);

		return $query->fetchAll();
	}

	/* Wishes can only be reviewed if all of the conditions below are satisifed:
	 *		Only the owners can write a review.
	 *		The wish status is Closed.
	 *		Only for accepted helps (requested = 1 and offered = 1).
	 *		Only if the helps have never been reviewed before. */
	public function getValidWishesForReview($userID, $wishID = "")
	{
		$sql = "SELECT Wish.ID,
					Wish.Description,
					Help.ID AS Help_ID,
					Helper.ID AS Helper_ID,
					Helper.First_Name AS Helper_First_Name,
					Helper.Last_Name AS Helper_Last_Name
				FROM Wish
				INNER JOIN Help ON Wish.ID = Help.Wish_ID
				INNER JOIN User Helper ON Helper.ID = Help.User_ID
				WHERE Wish.User_ID = :user_id
					AND Wish.Status = 'Closed'
					AND Help.Requested = 1
					AND Help.Offered = 1
					AND NOT EXISTS (
						SELECT Review.ID
						FROM Review
						WHERE Review.Help_ID = Help.ID
					)";

		if (is_numeric($wishID))
		{
			$sql .= " AND Wish.ID = :wish_id";
		}

		$sql .= " ORDER BY Wish.Created_On";

		$parameters = array(':user_id' => $userID);
		if (is_numeric($wishID))
		{
			$parameters[":wish_id"] = $wishID;
		}

		if (is_numeric($wishID)) {
			return $GLOBALS["beans"]->queryHelper->getSingleRowObject($this->db, $sql, $parameters);
		}
		else
		{
			$query = $this->db->prepare($sql);
			$query->execute($parameters);

			return $query->fetchAll();
		}
	}

	public function insertReview() {
		$sql = "INSERT INTO Review (User_ID, Help_ID, Recommended, Comments, Created_By, Created_On, Modified_On)
				VALUES (:helper_id, :help_id, :recommended, :comments, :created_by, NOW(), NOW())";
	
		$parameters = array(
				":helper_id" => $_POST["helperID"],
				":help_id" => $_POST["helpID"],
				":recommended" => $_POST["recommended"],
				":comments" => $_POST["comments"],
				":created_by" => $GLOBALS["beans"]->siteHelper->getSession("userID")
		);
	
		return $GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

}