<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Example user model
 *
 * PHP version 7.0
 */
class User extends \Core\Model
{

    /**
     * Register a new user
     *
     * @return array $registerResult["success": true|false, "fieldName"; "message d'erreur"]
     */
    public static function register(array $user): array
    {

        try {
            $db = static::getDB();

            $registerResult = [];

            $request = "INSERT INTO User(username, email, password, createdAt) VALUES(?, ?, ?, ?)";
            $stmt = $db->prepare($request);

            $stmt->execute([
                $user['username'],
                $user['email'],
                $user['password'],
                date("Y-m-d H:i:s")
            ]);

            $registerResult["success"] = true;
            $registerResult["userId"] = $db->lastInsertId();

            return $registerResult;

            // https://stackoverflow.com/questions/44867463/difference-between-exception-e-and-exception-e-in-trycatch?noredirect=1&lq=1
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'username') !== false) {
                $registerResult['success'] = false;
                $registerResult['username'] = "Ce nom d'utilisateur est déjà pris";
            }
            if (strpos($e->getMessage(), 'email') !== false) {
                $registerResult['success'] = false;
                $registerResult['email'] = "Cet email est déjà pris";
            }
            return $registerResult;
        }
    }

    /**
     * Login a user
     *
     * @return array $loginResult["success": true|false, "fieldName"; "message d'erreur"]
     */
    public static function login($user): array
    {
        $db = static::getDB();

        $loginResult = [];

        // vérifier que le user qui tente de se connecter est bien ds la db
        $emailOrUsername = strpos($user["username"], '@') !== false ? 'email' : 'username';

        $request = "SELECT
                        u.id AS id, 
                        u.username AS username, 
                        u.email AS email, 
                        u.password AS password,
                        u.role_id AS role_id,
                        p.image AS image
                    FROM User AS u 
                    INNER JOIN Profile AS p ON u.id = p.user_id 
                    WHERE $emailOrUsername = ?";

        $stmt = $db->prepare($request);
        $stmt->execute([$user['username']]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        // vérifier que le password est valide
        if ($existingUser) {
            $isMatch = password_verify($user['password'], $existingUser['password']);
            // et mettre les infos du logged in user en session
            if ($isMatch) {
                session_regenerate_id();
                $_SESSION['userId'] = $existingUser['id'];
                $_SESSION['roleId'] = $existingUser['role_id'];
                $_SESSION['username'] = $existingUser['username'];
                $_SESSION['avatar'] = $existingUser['image'];
                $_SESSION['fingerPrint'] = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
                $loginResult['success'] = true;
                // le mot de passe entré est incorrect
            } else {
                $loginResult['success'] = false;
                $loginResult['password'] = "Mot de passe incorrect";
            }
            // le username ou courriel n'existe pas dans la db
        } else {
            $loginResult['success'] = false;
            $loginResult['username'] = "Vérifier le nom d'utilisateur ou le courriel";
        }
        return $loginResult;
    }
}
