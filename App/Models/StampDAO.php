<?php

declare(strict_types=1);

namespace App\Models;

use \Core\ImageUpload;
use PDO;

class StampDAO extends \Core\Model
{

    /**
     * Récupérer tous les timbres d'un utilisateur
     */
    public static function getAllStampsByUser($userId): array 
    {
        $db = static::getDB();

        $request = "SELECT 
                        s.id AS stampId,
                        s.name AS stampName,
                        i.url AS url
                    FROM Stamp AS s
                    INNER JOIN Image AS i ON i.stamp_id = s.id
                    WHERE s.user_id = ?";

        $stmt = $db->prepare($request);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un timbre par son Id
     */
    public static function getStampById($stampId): array
    {
        $db = static::getDB();

        $request = "SELECT 
                        s.id AS id,
                        s.name,
                        s.description,
                        s.year,
                        s.width,
                        s.height, 
                        s.category_id AS category,
                        s.theme_id AS theme,
                        s.country_id AS country,
                        s.condition_id AS state,
                        s.user_id AS user_id,
                        i.url AS url,
                        i.public_id AS public_id
                    FROM Stamp AS s
                    INNER JOIN Image AS i
                        ON s.id = i.stamp_id
                    WHERE s.id = ?";

        $stmt = $db->prepare($request);
        $stmt->execute([$stampId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

        /**
     * Récupérer tous les timbres d'une enchère
     */
    public static function getStampsByAuction($auctionId): array
    {
        $db = static::getDB();

        $request = "SELECT 
                        id AS stampId,
                        name AS stampName
                    FROM Stamp
                    WHERE auction_id = ?";

        $stmt = $db->prepare($request);
        $stmt->execute([$auctionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ajouter un nouveau timbre
     */
    public static function create($data, $file)
    {
        $db = static::getDB();

        try {

            $db->beginTransaction();

            $cloudinary = ImageUpload::upload();

            $image = $cloudinary->uploadApi()->upload($file, [
                "folder" => "stampee/stamps"
            ]);

            $insertStampSQL = "INSERT INTO Stamp(
                name,
                description,
                country_id,
                category_id,
                theme_id,
                condition_id,
                year,
                width,
                height,
                createdAt,
                user_id) 
               VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($insertStampSQL);

            $stmt->execute([
                $data['name'],
                $data['description'],
                $data['country'],
                $data['category'],
                $data["theme"],
                $data["state"],
                $data['year'],
                $data['width'],
                $data['height'],
                date("Y-m-d H:i:s"),
                $_SESSION["userId"]
            ]);

            // get the stampID
            $stampId = $db->lastInsertId();

            // Insert image
            $insertImageSQL = "INSERT INTO Image(stamp_id, url, public_id) VALUES(?, ?, ?)";
            $stmt = $db->prepare($insertImageSQL);
            $stmt->execute([$stampId, $image["secure_url"], $image["public_id"]]);

            return $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            echo $e->getMessage();
        }
    }

    /**
     * Supprimer un timbre
     */
    public static function delete($stampId)
    {
        $db = static::getDB();

        // le public_id est nécessaire pour supprimer l'image du serveur Cloudinary
        $reqImgPublicId = "SELECT public_id FROM Image WHERE stamp_id=?";
        $stmt = $db->prepare($reqImgPublicId);
        $stmt->execute([$stampId]);
        $publicIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // suprimer les images de cloudinary avec les public_id
        $cloudinary = ImageUpload::upload();
        foreach ($publicIds as $key) {
            $cloudinary->uploadApi()->destroy($key["public_id"]);
        }

        // supprimer le timbre
        $request = "DELETE FROM Stamp WHERE id = ?";
        $stmt = $db->prepare($request);
        $stmt->execute([$stampId]);
        return $stmt->rowCount() > 0 ? true : false;
    }

    /**
     * Mettre un jour un timbre
     */
    public static function update($stampUpdate)
    {
        $db = static::getDB();

        try {
            $db->beginTransaction();

            $updateSQL = "UPDATE Stamp SET
                            name = ?,
                            description = ?,
                            country_id = ?,
                            category_id = ?,
                            theme_id = ?,
                            condition_id = ?,
                            year = ?,
                            width = ?,
                            height = ?
                            WHERE id = ? AND user_id = ?";

            $stmt = $db->prepare($updateSQL);
            $stmt->execute([
                $stampUpdate['name'],
                $stampUpdate['description'],
                $stampUpdate['country'],
                $stampUpdate['category'],
                $stampUpdate['theme'],
                $stampUpdate['state'],
                $stampUpdate['year'],
                $stampUpdate['width'],
                $stampUpdate['height'],
                $stampUpdate['id'],
                $_SESSION["userId"]
            ]);

            // If user tries to upload a new stamp image
            if ($stampUpdate['img']) {
                // update the image
                $cloudinary = ImageUpload::upload();
                $image = $cloudinary->uploadApi()->upload($stampUpdate['img'], [
                    "folder" => "stampee/stamps"
                ]);

                // destroy old image
                $cloudinary->uploadApi()->destroy($stampUpdate['public_id']);

                // update Image table
                $imgSQL = "UPDATE Image SET 
                            url = ?,
                            public_id = ?
                            WHERE stamp_id = ?";
                $stmt = $db->prepare($imgSQL);
                $stmt->execute([
                    $image['secure_url'],
                    $image['public_id'],
                    $stampUpdate['id']
                ]);
            }
            return $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            echo $e->getMessage();
        }
    }
}
