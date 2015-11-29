<?php

class User
{

	public function index()
	{
		$userID = $GLOBALS["beans"]->siteHelper->getSession("userID");
		$user = $GLOBALS["beans"]->userService->getUser($userID);

		require APP . 'views/_templates/header.php';
		require APP . 'views/user/index.php';
		require APP . 'views/_templates/footer.php';
	}

	public function signUp()
	{
		$countries = $GLOBALS["beans"]->resourceService->getCountries();

		require APP . 'views/_templates/header.php';
		require APP . 'views/user/signUp.php';
		require APP . 'views/_templates/footer.php';
	}

	public function forget()
	{
		require APP . 'views/_templates/header.php';
		require APP . 'views/user/forget.php';
		require APP . 'views/_templates/footer.php';
	}

	public function reset($resetID, $resetKey)
	{
		$resetInfo = $GLOBALS["beans"]->userService->getResetInfo($resetID, $resetKey);

		require APP . 'views/_templates/header.php';
		require APP . 'views/user/reset.php';
		require APP . 'views/_templates/footer.php';
	}

	public function sendForgetEmail()
	{
		$errorMessage = $GLOBALS["beans"]->userService->sendForgetEmail();

		if ($errorMessage != "")
		{
			$GLOBALS["beans"]->siteHelper->setAlert("danger", $errorMessage);
			header('location: ' . URL_WITH_INDEX_FILE . 'user/forget');
		}
		else
		{
			$_SESSION["forgetEmailSent"] = 1;
			header('location: ' . URL_WITH_INDEX_FILE);
		}
	}

	public function editLogin()
	{
		$userID = $GLOBALS["beans"]->siteHelper->getSession("userID");
		$user = $GLOBALS["beans"]->userService->getUser($userID);

		require APP . 'views/_templates/header.php';
		require APP . 'views/user/editLogin.php';
		require APP . 'views/_templates/footer.php';
	}

	public function editProfile()
	{
		$userID = $GLOBALS["beans"]->siteHelper->getSession("userID");
		$user = $GLOBALS["beans"]->userService->getUser($userID);

		$countries = $GLOBALS["beans"]->resourceService->getCountries();
		if ($user->Country != "") {
			$states = $GLOBALS["beans"]->resourceService->getStates($user->Country);
		}

		require APP . 'views/_templates/header.php';
		require APP . 'views/user/editProfile.php';
		require APP . 'views/_templates/footer.php';
	}

	public function login()
	{
		$errorMessage = $GLOBALS["beans"]->userService->login();

		if ($errorMessage != "")
		{
			$GLOBALS["beans"]->siteHelper->setAlert("danger", $errorMessage);
		}

		header('location: ' . URL_WITH_INDEX_FILE);
	}

	public function logout()
	{
		$GLOBALS["beans"]->userService->logout();

		header('location: ' . URL_WITH_INDEX_FILE);
	}

	public function createAccount()
	{
		$GLOBALS["beans"]->userService->insertUser();
		$this->login();
	}

	public function saveProfile()
	{
		$GLOBALS["beans"]->userService->updateProfile();

		header('location: ' . URL_WITH_INDEX_FILE . 'user');
	}

	public function saveLogin()
	{
		$GLOBALS["beans"]->userService->updateLogin();

		header('location: ' . URL_WITH_INDEX_FILE . 'user');
	}

	public function checkUniqueEmail()
	{
		$unique = false;
		$loginInfo = $GLOBALS["beans"]->userService->getLoginInfo($_POST["email"]);

		if (!is_numeric($loginInfo->ID))
		{
			$unique = true;
		}

		echo json_encode($unique);
	}

	public function resetPassword()
	{
		$GLOBALS["beans"]->userService->resetPassword();

		$_SESSION["passwordChanged"] = 1;
		header('location: ' . URL_WITH_INDEX_FILE);
	}

}
