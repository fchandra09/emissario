<?php

class FriendModel
{
    /**
     * @param object $db A PDO database connection
     */
    function __construct($db)
    {
        try {
            $this->db = $db;
        } catch (PDOException $e) {
            exit('Database connection could not be established.');
        }
    }

    public function getFriends($user_id)
    {
        $sql = "SELECT User.First_Name, User.Last_Name, User.City, User.State, User.Country
        		FROM Friend
        		INNER JOIN User ON User.ID = Friend.User_ID2
        		WHERE Friend.User_ID1 = :user_id
        			AND Friend.Pending = 0
        		UNION
        		SELECT User.First_Name, User.Last_Name, User.City, User.State, User.Country
        		FROM Friend
        		INNER JOIN User ON User.ID = Friend.User_ID1
        		WHERE Friend.User_ID2 = :user_id
        			AND Friend.Pending = 0";
        $query = $this->db->prepare($sql);
        $parameters = array(':user_id' => $user_id);
        $query->execute($parameters);
        
        return $query->fetchAll();
    }

}
