<?php

namespace Controllers;

use Models\Friends;


class Friend
{
    private const STATE_ACCEPT = 2;
    private const STATE_WAITING = 1;
    private const STATE_CANCELLED = 0;


    private $friends;
    public function __construct()
    {
        $this->friends = new Friends;
    }


    public function searchFriends()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $email = $_POST['email'];
            $res = $this->friends->searchFriends($email);
            echo json_encode($res);
        }
    }

    public function getFriendWaiting()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userID = $_POST['userID'];
            $res = $this->friends->getFriendsAndPending($userID);
            echo json_encode($res);
        }
    }

    public function addFriend()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $userID = $_POST['userID'];
            $friendID = $_POST['friendID'];
            $data =
                [
                    'user1' => $userID,
                    'user2' => $friendID,
                    "state" => self::STATE_WAITING,
                    "requester" => $userID
                ];
            $res = $this->friends->addFriends($data);
            if ($res) {
                $response =
                    [
                        "message" => "ami ajoute"
                    ];
            } else {
                $response =
                    [
                        "message" => "ami non ajoute"
                    ];
            }
            echo json_encode($response);
        }
    }


    public function acceptFriend()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userID = $_POST['userID'];
            $friendID = $_POST['friendID'];
            $state = self::STATE_ACCEPT;
            $res = $this->friends->acceptFriend($userID, $friendID, $state);
            if ($res) {
                $response =
                    [
                        "message" => "ami ajoute"
                    ];
            } else {
                $response =
                    [
                        "message" => "ami non ajoute"
                    ];
            }
            echo json_encode($response);
        }
    }
}