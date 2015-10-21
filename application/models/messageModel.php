<?php

class MessageModel extends Model
{

	public function getMessages($userID)
	{
		$sql = "SELECT Message.*,
					Sender.First_name AS Sender_First_Name,
					Sender.Last_name AS Sender_Last_Name,
					Recipient.First_Name AS Recipient_First_Name,
					Recipient.Last_Name AS Recipient_Last_Name,
					DATE_FORMAT(Message.Created_On, '%m/%d/%Y %r') AS Formatted_Created_On
				FROM Message
				INNER JOIN User Sender ON Sender.ID = Message.Sender_ID
				INNER JOIN User Recipient ON Recipient.ID = Message.Recipient_ID
				WHERE Message.Recipient_ID = :user_id
					OR Message.Sender_ID = :user_id";

		$parameters = array(":user_id" => $userID);

		$query = $this->db->prepare($sql);
		$query->execute($parameters);

		return $query->fetchAll();
	}

	public function getMessage($messageID, $userID = "")
	{
		$sql = "SELECT Message.*,
					Sender.First_name AS Sender_First_Name,
					Sender.Last_name AS Sender_Last_Name,
					Recipient.First_Name AS Recipient_First_Name,
					Recipient.Last_Name AS Recipient_Last_Name,
					DATE_FORMAT(Message.Created_On, '%m/%d/%Y %r') AS Formatted_Created_On
				FROM Message
				INNER JOIN User Sender ON Sender.ID = Message.Sender_ID
				INNER JOIN User Recipient ON Recipient.ID = Message.Recipient_ID
				WHERE Message.ID = :message_id";
		if (is_numeric($userID)) {
			$sql .= " AND (Message.Recipient_ID = :user_id
						OR Message.Sender_ID = :user_id)";
		}

		$parameters = array(':message_id' => $messageID);
		if (is_numeric($userID)) {
			$parameters[":user_id"] = $userID;
		}

		return $GLOBALS["beans"]->queryHelper->getSingleRowObject($this->db, $sql, $parameters);
	}

	public function getRecipients($userID)
	{
		$sql = "SELECT User.ID, User.First_Name, User.Last_Name
				FROM (
					SELECT Sender_ID AS Recipient_ID
					FROM Message
					WHERE Message.Recipient_ID = :user_id
					UNION
					SELECT Recipient_ID
					FROM Message
					WHERE Sender_ID = :user_id
					UNION
					SELECT User_ID2 AS Recipient_ID
					FROM Friend
					WHERE User_ID1 = :user_id
					UNION
					SELECT User_ID1 AS Recipient_ID
					FROM Friend
					WHERE User_ID2 = :user_id
				) Recipient
				INNER JOIN User ON User.ID = Recipient.Recipient_ID
				ORDER BY User.First_Name, User.Last_Name";

		$parameters = array(":user_id" => $userID);

		$query = $this->db->prepare($sql);
		$query->execute($parameters);

		return $query->fetchAll();
	}

	public function insertMessage() {
		$sql = "INSERT INTO Message (Sender_ID, Recipient_ID, Title, Content, Created_On, Modified_On)
				VALUES (:sender_id, :recipient_id, :title, :content, NOW(), NOW())";

		$parameters = array(
				":sender_id" => $_POST["userID"],
				":recipient_id" => $_POST["recipientID"],
				":title" => $_POST["title"],
				":content" => $_POST["content"]
		);

		return $GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

}
