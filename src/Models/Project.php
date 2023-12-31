<?php

namespace Models;

use \PDO;

class Project extends Database
{
    public $table = "todo_projects";

    public function __construct()
    {
        parent::__construct();

    }
    public function getProjectByUser()
    {
        if ($_SESSION) {
            $userID = $_SESSION['ID'];
            if ($_SESSION) {
                $userID = $_SESSION['ID'];
                $sql = "SELECT 
            projects.name AS project_name, 
            projects.ID AS project_id, 
            COUNT(todo_projects.ID) AS task_count,
            COUNT(CASE WHEN todo_projects.state = 1 THEN todo_projects.ID END) AS task_finish
        FROM users 
        JOIN participation ON users.ID = participation.userID 
        JOIN projects ON participation.projectID = projects.ID 
        LEFT JOIN todo_projects ON projects.ID = todo_projects.projectID 
        WHERE users.ID = :userID AND participation.state = 1
        GROUP BY projects.ID;";

                $this->getConnection();
                $query = self::$connection->prepare($sql);
                $query->bindParam(':userID', $userID, \PDO::PARAM_INT);
                $query->execute();
                return $query->fetchAll();
            }
        }
    }

    public function updateInvit($projectID, $choose): array
    {
        if ($_SESSION['ID']) {
            $userID = $_SESSION['ID'];
            if ($choose == 1) {
                $sql = 'UPDATE participation SET state = :state WHERE userID = :userID AND projectID = :projectID';
                $this->getConnection();
                $query = self::$connection->prepare($sql);
                $query->bindParam(':state', $choose);
                $query->bindParam(':userID', $userID);
                $query->bindParam(':projectID', $projectID);
                $res = $query->execute();
                if ($res) {
                    $response =
                        [
                            "message" => "Invitation mis à jour",
                            "type" => "ok"
                        ];
                } else {
                    $response =
                        [
                            "message" => "Erreur",
                            "type" => "cancel"
                        ];
                }
            } else if ($choose == 0) {
                $sql = "DELETE FROM participation WHERE userID = :userID AND projectID = :projectID";
                $this->getConnection();
                $query = self::$connection->prepare($sql);
                $query->bindParam(':userID', $userID);
                $query->bindParam(':projectID', $projectID);
                $res = $query->execute();
                if ($res) {
                    $response =
                        [
                            "message" => "Invitation supprimé",
                            "type" => "ok"
                        ];
                } else {
                    $response =
                        [
                            "message" => "Erreur lors de la mise à jour",
                            "type" => "cancel"
                        ];
                }
            }

        } else {
            $response =
                [
                    "message" => "Veuillez vous connectez",
                    "type" => "cancel"
                ];
        }

        return $response;
    }

    public function getProjectWaitingByUser()
    {
        if ($_SESSION['ID']) {
            $userID = $_SESSION['ID'];
            if ($_SESSION) {
                $userID = $_SESSION['ID'];
                $sql = "SELECT 
            projects.name AS project_name, 
            projects.ID AS project_id, 
            COUNT(todo_projects.ID) AS task_count,
            COUNT(CASE WHEN todo_projects.state = 1 THEN todo_projects.ID END) AS task_finish
        FROM users 
        JOIN participation ON users.ID = participation.userID 
        JOIN projects ON participation.projectID = projects.ID 
        LEFT JOIN todo_projects ON projects.ID = todo_projects.projectID 
        WHERE users.ID = :userID AND participation.state = 0
        GROUP BY projects.ID;";

                $this->getConnection();
                $query = self::$connection->prepare($sql);
                $query->bindParam(':userID', $userID, \PDO::PARAM_INT);
                $query->execute();
                return $query->fetchAll();
            }
        }
    }

    public function getProjectNameById($projectId)
    {
        $sql = "SELECT name FROM projects WHERE ID = :projectId";
        $this->getConnection();
        $query = self::$connection->prepare($sql);
        $query->bindParam(':projectId', $projectId, \PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchColumn();

        return $result;
    }

    private function checkUserInProject($userID, $projectID)
    {
        $sql = "SELECT COUNT(*) FROM participation WHERE userID = :userID AND projectID = :projectID;";
        $this->getConnection();
        $query = self::$connection->prepare($sql);
        $query->bindParam(':userID', $userID, \PDO::PARAM_INT);
        $query->bindParam(':projectID', $projectID, \PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchColumn();

        return $result > 0;
    }

    public function getTodoProject()
    {
        $userID = $_SESSION['ID'];
        $projectID = $_POST['id'];
        $isUserInProject = $this->checkUserInProject($userID, $projectID);
        if ($isUserInProject) {
            $userID = $_SESSION['ID'];
            $sql = "SELECT 
                todo_projects.name AS task_name, 
                todo_projects.ID AS task_id, 
                todo_projects.orderTodo as task_order,
                todo_projects.state AS task_state, 
                todo_projects.description AS task_description, 
                todo_projects.owner AS task_owner, 
                users.username AS owner_username,
                categorydefault.name AS task_category
            FROM todo_projects 
            LEFT JOIN categorydefault ON todo_projects.category = categorydefault.ID 
            INNER JOIN users ON todo_projects.owner = users.ID 
            WHERE todo_projects.projectID = :projectID
            ORDER BY todo_projects.orderTodo ASC;";

            $this->getConnection();
            $query = self::$connection->prepare($sql);
            $query->bindParam(':projectID', $projectID, \PDO::PARAM_INT);
            $query->execute();
            $todos = $query->fetchAll();

            if ($todos) {
                echo json_encode($todos);
            } else {
                $response = [
                    "message" => "Aucune tâche à faire",
                    "type" => "noTodo"
                ];
                echo json_encode($response);
            }
        } else {
            $response = [
                "message" => "Pas autorisé",
                "type" => "NotAllowed"
            ];
            echo json_encode($response);
        }

    }

    public function postEventProject($data)
    {
        self::getConnection();
        $this->insertData($data);
        $response =
            [
                "message" => "Event envoyé",
            ];

        echo json_encode($response);
    }

    public function changeOrder($orderData): void
    {

        foreach ($orderData as $taskId => $newOrder) {
            self::getConnection();
            $this->edit(['orderTodo' => $newOrder], $taskId);
        }
        $response =
            [
                "message" => $orderData
            ];
        echo json_encode($response);
    }

    public function getUserInProject()
    {
        self::getConnection();
        $projectID = $_POST['projectID'];
        $sql = "SELECT users.* FROM participation 
        INNER JOIN users ON participation.userID = users.ID 
        WHERE participation.projectID = :projectID AND participation.state = 1";
        $query = self::$connection->prepare($sql);
        $query->bindParam(':projectID', $projectID, PDO::PARAM_INT);
        $query->execute();
        $users = $query->fetchAll(\PDO::FETCH_ASSOC);
        return $users;
    }


    public function checkEvent($data, $id)
    {
        self::getConnection();
        $this->edit($data, $id);
        $response =
            [
                "message" => "Event modifié"
            ];
        echo json_encode($response);
    }

    public function createProject($name)
    {
        self::getConnection();

        try {
            self::$connection->beginTransaction();

            $sql = "INSERT INTO projects VALUES(0, :name, :description, :owner)";
            $query = self::$connection->prepare($sql);
            $req = $query->execute(array("name" => $name, "description" => "", "owner" => $_SESSION['ID']));

            if ($req) {
                $projectId = self::$connection->lastInsertId();

                $sqlParticipation = "INSERT INTO participation VALUES(0, :userID, :projectID, :state)";
                $queryParticipation = self::$connection->prepare($sqlParticipation);

                $reqParticipation = $queryParticipation->execute(array("userID" => $_SESSION['ID'], "projectID" => $projectId, "state" => 1));

                if ($reqParticipation) {
                    self::$connection->commit();
                    $response = [
                        'message' => 'Projet créé',
                        "ok" => true
                    ];
                    return $response;
                } else {
                    self::$connection->rollBack();
                    return false;
                }
            } else {
                self::$connection->rollBack();
                return false;
            }
        } catch (\PDOException $e) {
            self::$connection->rollBack();
            return false;
        }
    }

    public function addUserInProject()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $friendID = $_POST['friendID'];
            $projectID = $_POST['projectID'];

            if (isset($_SESSION['ID'])) {
                $isUserInProject = $this->checkUserInProject($friendID, $projectID);

                if ($isUserInProject) {
                    $response =
                        [
                            "message" => "L'utilisateur est déjà dans le projet",
                            "type" => "cancelled"
                        ];
                } else {
                    $sql = "INSERT INTO participation VALUES(0, :userID, :projectID, 0)";
                    self::getConnection();
                    $query = self::$connection->prepare($sql);
                    $res = $query->execute(array("userID" => $friendID, "projectID" => $projectID));

                    if ($res) {
                        $response =
                            [
                                "message" => "Participation ajoutée",
                                "type" => "ok"
                            ];
                    } else {
                        $response =
                            [
                                "message" => "Erreur lors de l'ajout",
                                "type" => "cancelled"
                            ];
                    }
                }
            } else {
                $response = [
                    "message" => "Veuillez vous connecter"
                ];
            }

            return $response;
        }
    }

    public function addCategoryProject($data, $id)
    {
        self::getConnection();
        $this->edit($data, $id);
        $response =
            [
                "message" => "Categorie cree"
            ];
        echo json_encode($response);
    }

    public function deleteTodoInProject(int $todoID): void
    {
        self::getConnection();
        $sql = "DELETE FROM " . $this->table . " WHERE id = :todoID";
        $query = self::$connection->prepare($sql);
        $query->bindParam(":todoID", $todoID);
        $res = $query->execute();
        if ($res) {
            $response =
                [
                    "message" => "todo supprime",
                ];
        } else {
            $response =
                [
                    "message" => "Erreur"
                ];
        }
        echo json_encode($response);
    }

    public function deleteUserInProject($userID, $projectID)
    {
        $isOwner = $this->isOwner($projectID);
        if ($isOwner) {
            $sql = "DELETE FROM participation WHERE userID = :userID AND projectID = :projectID";
            $query = self::$connection->prepare($sql);
            $query->bindParam(":userID", $userID);
            $query->bindParam(":projectID", $projectID);
            $res = $query->execute();
            if ($res) {
                $response =
                    [
                        "message" => "Utilisateur supprimé",
                        "type" => "ok"
                    ];
            } else {
                $response =
                    [
                        "message" => "Utilisateur non supprimé",
                        "type" => "cancel"
                    ];
            }


        } else {
            $response =
                [
                    "message" => "Vous n'êtes pas le propiétaire",
                    "type" => "cancel"
                ];
        }

        echo json_encode($response);

    }

    public function isOwner($projectID)
    {
        $userID = $_SESSION['ID'];
        $sql = "SELECT owner FROM projects WHERE owner = :userID AND ID = :projectID";
        $query = self::$connection->prepare($sql);
        $query->bindParam(":userID", $userID);
        $query->bindParam(":projectID", $projectID);
        $query->execute();

        $result = $query->fetch(PDO::FETCH_ASSOC);

        return !empty($result);
    }



}