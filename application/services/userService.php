<?php

class UserService extends Service
{

	public function getUser($userID)
	{
		return $this->model->getUser($userID);
	}

	public function getLoginInfo($email)
	{
		return $this->model->getLoginInfo($email);
	}

	public function insertUser()
	{
		$this->model->insertUser();
	}

	public function sendForgetEmail()
	{
		$errorMessage = "Email is not registered.";
		$loginInfo = $this->model->getLoginInfo($_POST["email"]);

		if (is_numeric($loginInfo->ID))
		{
			$resetKey = $GLOBALS["beans"]->resourceService->getRandomUID();
			$resetID = $this->model->insertPasswordReset($resetKey);

			$message = "Hi " . $loginInfo->First_Name . ",\r\n\r\n";
			$message .= "Please use the following link to reset your password:\r\n";
			$message .= URL_WITH_INDEX_FILE . "user/reset/" . $resetID . "/" . $resetKey . "\r\n\r\n";
			$message .= "This link is valid for the next 30 minutes.\r\n\r\n";
			$message .= "Thank you,\r\n";
			$message .= "The team at Emissario";

			mail($loginInfo->Email, "Emissario Password Reset", $message, "From: fchandr2@illinois.edu");

			$errorMessage = "";
		}

		return $errorMessage;
	}

	public function updateLogin()
	{
		$this->model->updateLogin();
	}

	public function updateProfile()
	{
		$this->model->updateProfile();
	}

	public function login()
	{
		$errorMessage = "Invalid email or password.";
		$loginInfo = $this->model->getLoginInfo($_POST["email"]);
		
		if (strcasecmp($_POST["email"],$loginInfo->Email) == 0)
		{
			if (password_verify($_POST["password"],$loginInfo->Password))
			{
				$_SESSION["userID"] = $loginInfo->ID;
				$errorMessage = "";
			}
		}
		
		return $errorMessage;
	}
	
	public function logout()
	{
		// Unset all of the session variables.
		$_SESSION = array();
		
		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
					$params["path"], $params["domain"],
					$params["secure"], $params["httponly"]
			);
		}
		
		// Finally, destroy the session.
		session_destroy();
	}

	public function getResetInfo($resetID, $resetKey)
	{
		$passwordReset = $this->model->getPasswordReset($resetID);

		$resetInfo = new stdClass();
		$resetInfo->Valid = false;
		$resetInfo->Email = $passwordReset->Email;

		if (is_numeric($passwordReset->ID))
		{
			if (password_verify($resetKey, $passwordReset->Reset_Key))
			{
				$resetInfo->Valid = true;
			}
		}

		return $resetInfo;
	}

	public function resetPassword()
	{
		$passwordReset = $this->model->getPasswordReset($_POST["resetID"]);

		if (is_numeric($passwordReset->ID))
		{
			if (password_verify($_POST["resetKey"], $passwordReset->Reset_Key))
			{
				$this->model->resetPassword($passwordReset->Email);
				$this->model->usePasswordReset($passwordReset->ID);
			}
		}
	}

}