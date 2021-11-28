<?php

declare(strict_types=1);

namespace App\Models;

use Cloudinary\Cloudinary;
use \Core\ImageUpload;

use PDO;

class ProfileDAO extends \Core\Model
{

    /**
     * Create a new profile associated to a user
     * @param $userId
     * @return bool
     */
    public static function create($userId): bool
    {
        $db = static::getDB();
        $request = "INSERT INTO Profile(user_Id) VALUES(?)";
        $stmt = $db->prepare($request);
        $stmt->execute([$userId]);
        return $stmt->rowCount() > 0 ? true : false;
    }

    /**
     * Récupérer le profil d'un utilisateur par son id
     * @param $userId
     * @return array
     */
    public static function getProfileById($userId): array
    {
        $db = static::getDB();
        $request = "SELECT * FROM Profile WHERE user_id = ?";
        $stmt = $db->prepare($request);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mettre à jour le profil d'un utilisateur

     * @return bool|null
     */
    public static function update($profileUpdate): ?bool
    {
        $db = static::getDB();

        // Si l'utilisateur upload une nouvelle image
        if ($profileUpdate['img']) {
            // mettre à jour l'image
            $cloudinary = ImageUpload::upload();
            $image = $cloudinary->uploadApi()->upload($profileUpdate['img'], [
                "folder" => "stampee/users"
            ]);

            // destroy old image
            // user might create a profile with the default image and later on, add a profile image
            // in this case, there is no previous img to destroy and public_id is NULL in the DB
            if (!is_null($profileUpdate['public_id'])) {
                $cloudinary->uploadApi()->destroy($profileUpdate['public_id']);
            }

            // set new $book cover url and new public_id
            $profileUpdate['image'] = $image['secure_url'];
            $profileUpdate['public_id'] = $image['public_id'];

            $request = "UPDATE Profile SET firstname = ?, lastname = ?, country_id = ?, dob = ?, public_id = ?, image = ? WHERE user_id = ?";
            $stmt = $db->prepare($request);
            $stmt->execute([
                $profileUpdate['firstname'],
                $profileUpdate['lastname'],
                $profileUpdate['country'],
                $profileUpdate['dob'],
                $profileUpdate['public_id'],
                $profileUpdate['image'],
                $_SESSION['userId']
            ]);

            // mettre l'image de profile en session
            $_SESSION['avatar'] = $profileUpdate['image'];
        } else {
            $request = "UPDATE Profile SET firstname = ?, lastname = ?, country_id = ?, dob = ? WHERE user_id = ?";
            $stmt = $db->prepare($request);
            $stmt->execute([
                $profileUpdate['firstname'],
                $profileUpdate['lastname'],
                $profileUpdate['country'],
                $profileUpdate['dob'],
                $_SESSION['userId']
            ]);
        }

        return $stmt->rowCount() > 0 ? true : null;
    }
}
