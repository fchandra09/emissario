<?php

class ReviewService extends Service
{

	public function getReviews($userID, $reviewType, $recommended = "")
	{
		return $this->model->getReviews($userID, $reviewType, $recommended);
	}

	public function getValidWishesForReview($userID, $wishID = "")
	{
		return $this->model->getValidWishesForReview($userID, $wishID);
	}

	public function saveReview()
	{
		return $this->model->insertReview();
	}

}