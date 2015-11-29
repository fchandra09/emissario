<?php

class Reviews
{

	public function index()
	{
		$userID = $GLOBALS["beans"]->siteHelper->getSession("userID");

		$reviewType = "received";
		if (array_key_exists("reviewType", $_POST))
		{
			$reviewType = $_POST["reviewType"];
		}
		elseif ($GLOBALS["beans"]->siteHelper->getSession("reviewType") != "") {
			$reviewType = $GLOBALS["beans"]->siteHelper->getSession("reviewType");
			$_SESSION["reviewType"] = "";
		}

		$recommended = "";
		if (array_key_exists("recommended", $_POST))
		{
			$recommended = $_POST["recommended"];
		}

		$reviews = $GLOBALS["beans"]->reviewService->getReviews($userID, $reviewType, $recommended);

		require APP . 'views/_templates/header.php';
		require APP . 'views/reviews/index.php';
		require APP . 'views/_templates/footer.php';
	}

	public function add($wishID = "")
	{
		$userID = $GLOBALS["beans"]->siteHelper->getSession("userID");
		$unreviewedWishes = $GLOBALS["beans"]->reviewService->getValidWishesForReview($userID);

		require APP . 'views/_templates/header.php';
		require APP . 'views/reviews/add.php';
		require APP . 'views/_templates/footer.php';
	}

	public function getValidWishInfo($wishID)
	{
		$userID = $GLOBALS["beans"]->siteHelper->getSession("userID");
		$validWish = $GLOBALS["beans"]->reviewService->getValidWishesForReview($userID, $wishID);

		echo json_encode($validWish);
	}

	public function save()
	{
		$GLOBALS["beans"]->reviewService->saveReview();

		$_SESSION["reviewType"] = "written";
		header('location: ' . URL_WITH_INDEX_FILE . 'reviews');
	}

}