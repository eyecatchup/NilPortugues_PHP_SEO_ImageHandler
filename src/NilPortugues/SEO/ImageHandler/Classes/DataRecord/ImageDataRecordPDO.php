<?php
namespace NilPortugues\SEO\ImageHandler\Classes\DataRecord;

/**
 * Database Abstraction using PHP's PDO Class
 */
class ImageDataRecordPDO implements \NilPortugues\SEO\ImageHandler\Interfaces\ImageDataRecordInterface
{
    protected static $instance;
    protected static $db;
    protected $tableName = 'cms_content_images';

    protected function __construct()
    {

    }

    /**
     * Cloning a Singleton instance is completely forbidden
     *
     * @throws \Exception
     */
    protected function __clone()
    {
        throw new \Exception('A singleton class cannot be cloned');
    }

    /**
     * The Singleton method. Retrieves the static object instance.
     *
     * @param  \PDO  $pdo
     * @return mixed
     */
    public static function getInstance(\PDO $pdo)
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
            self::$db = $pdo;
        }

        return static::$instance;
    }


    /**
     * @param  \NilPortugues\SEO\ImageHandler\Classes\ImageObject $obj
     * @return bool|mixed
     */
    public function insertImageObject(\NilPortugues\SEO\ImageHandler\Classes\ImageObject $obj)
    {
        //If parent hash is set, find ID and store it.
        $parentHash = $obj->getParentHash();

        if (!empty($parentHash)) {
            $parent = $this->getImageByHash($parentHash);
        }

        $values = array
        (
            'parent_id' => (empty($parent['id'])) ? NULL : $parent['id'],
            'title' => htmlspecialchars($obj->getTitle()),
            'alt' => htmlspecialchars($obj->getAlt()),
            'file_md5' => $obj->getHash(),
            'filename' => $obj->getFileName(),
            'file_extension' => $obj->getFileExtension(),
            'filepath' => $obj->getFilePath(),
            'width' => $obj->getWidth(),
            'height' => $obj->getHeight(),
            'date_creation' => date("Y-m-d h:i:s"),
        );

        $sql = "INSERT INTO $this->tableName(parent_id,title,alt,file_md5,filename,file_extension,filepath,width,height,date_creation) VALUES(?,?,?,?,?,?,?,?,?,?);";

        $q = self::$db->prepare($sql);
        try {
            self::$db->beginTransaction();
            $q->execute(array_values($values));
            $id = self::$db->lastInsertId();
            self::$db->commit();

            return $id;
        } catch (PDOException $e) {
            self::$db->rollback();

            return false;
        }
    }

    /**
     * @param  array $values
     * @return mixed
     */
    public function getExistingImageHashes($values = array())
    {
        $data = false;
        if (!empty($values)) {
            $placeholders = implode(',', array_fill(0, count($values), '?'));

            $sql = "SELECT * FROM " . $this->tableName . " WHERE file_md5 IN ($placeholders);";
            $q = self::$db->prepare($sql);
            $q->execute($values);

            $data = $q->fetchAll();
        }

        return $data;
    }

    /**
     * @param $hash
     * @return bool
     */
    public function getImageByHash($hash)
    {
        if (!empty($hash)) {
            $sql = "SELECT * FROM " . $this->tableName . " WHERE file_md5 = ?; ";
            $q = self::$db->prepare($sql);
            $q->execute(array($hash));
            $data = $q->fetch();

            return $data;
        }

        return false;
    }
}
