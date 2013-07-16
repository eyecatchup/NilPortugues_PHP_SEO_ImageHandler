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

    /**
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->db = $pdo;
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

        $q = $this->db->prepare($sql);
        try {
            $this->db->beginTransaction();
            $q->execute(array_values($values));
            $id = $this->db->lastInsertId();
            $this->db->commit();

            return $id;
        } catch (PDOException $e) {
            $this->db->rollback();

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
            $q = $this->db->prepare($sql);
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
            $q = $this->db->prepare($sql);
            $q->execute(array($hash));
            $data = $q->fetch();

            return $data;
        }

        return false;
    }
}
