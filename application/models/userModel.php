<?php

class UserModel extends Model
{

	public function getUser($userID)
	{
		$sql = "SELECT User.*, Country.Country_Name, State.State_Name
				FROM User
				LEFT JOIN Country ON Country.Country_Code = User.Country
				LEFT JOIN State ON State.State_Code = User.State
				WHERE User.ID = :user_id";

		$parameters = array(":user_id" => $userID);

		return $GLOBALS["beans"]->queryHelper->getSingleRowObject($this->db, $sql, $parameters);
	}

	public function insertUser() {
		$sql = "INSERT INTO User (First_Name, Last_Name, Email, Password, City, State, Country, Phone, Created_On, Modified_On)
				VALUES (:first_name, :last_name, :email, :password, :city, :state, :country, :phone, NOW(), NOW())";

		$parameters = array(
				":first_name" => $_POST["firstName"],
				":last_name" => $_POST["lastName"],
				":email" => $_POST["email"],
				":password" => password_hash($_POST["password"],PASSWORD_DEFAULT),
				":city" => $_POST["city"],
				":state" => $_POST["state"],
				":country" => $_POST["country"],
				":phone" => $_POST["phone"]
			);

		return $GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

	public function updateLogin() {
		$sql = "UPDATE User
				SET Email = :email,";
		if ($_POST["password"] != "") {
			$sql .= "Password = :password,";
		}
		$sql .= "Modified_On = NOW()
				WHERE User.ID = :user_id";

		$parameters = array(
				":user_id" => $_POST["userID"],
				":email" => $_POST["email"]
			);
		if ($_POST["password"] != "") {
			$parameters[":password"] = password_hash($_POST["password"],PASSWORD_DEFAULT);
		}

		$GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

	public function updateProfile() {
		$sql = "UPDATE User
				SET First_Name = :first_name,
					Last_Name = :last_name,
					City = :city,
					State = :state,
					Country = :country,
					Phone = :phone,
					Modified_On = NOW()
				WHERE User.ID = :user_id";

		$parameters = array(
				":user_id" => $_POST["userID"],
				":first_name" => $_POST["firstName"],
				":last_name" => $_POST["lastName"],
				":city" => $_POST["city"],
				":state" => $_POST["state"],
				":country" => $_POST["country"],
				":phone" => $_POST["phone"]
			);

		$GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

	public function getLoginInfo($email)
	{
		$sql = "SELECT ID, Email, Password, First_Name
				FROM User
				WHERE Email = :email";

		$parameters = array(":email" => $email);

		return $GLOBALS["beans"]->queryHelper->getSingleRowObject($this->db, $sql, $parameters);
	}

	public function insertPasswordReset($resetKey) {
		$sql = "INSERT INTO Password_Reset (Email, Reset_Key, Used, Created_On, Modified_On)
				VALUES (:email, :reset_key, 0, NOW(), NOW())";

		$parameters = array(
				":email" => $_POST["email"],
				":reset_key" => password_hash($resetKey,PASSWORD_DEFAULT)
		);

		return $GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

	public function getPasswordReset($resetID) {
		$sql = "SELECT *
				FROM Password_Reset
				WHERE ID = :reset_id
					AND Used = 0
					AND DATE_ADD(Created_On, INTERVAL 30 MINUTE) > NOW()";

		$parameters = array(":reset_id" => $resetID);

		return $GLOBALS["beans"]->queryHelper->getSingleRowObject($this->db, $sql, $parameters);
	}

	public function resetPassword($email) {
		$sql = "UPDATE User
				SET Password = :password,
					Modified_On = NOW()
				WHERE User.Email = :email";

		$parameters = array(
				":email" => $email,
				":password" => password_hash($_POST["password"],PASSWORD_DEFAULT)
		);

		$GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

	public function usePasswordReset($resetID) {
		$sql = "UPDATE Password_Reset
				SET Used = 1,
					Modified_On = NOW()
				WHERE Password_Reset.ID = :reset_id";
	
		$parameters = array(":reset_id" => $resetID);

		$GLOBALS["beans"]->queryHelper->executeWriteQuery($this->db, $sql, $parameters);
	}

}
