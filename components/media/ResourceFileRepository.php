<?php

namespace app\common\components\media;

class ResourceFileRepository extends UploadFileRepository
{
    public $path;
    public $entity_type;
    public $entity_id;

    /**
     * @param string $hash
     * @param string $ext
     * @return string
     */
    public function getPath(string $hash, string $ext) : string
    {
        return $this->path . DIRECTORY_SEPARATOR
                . $this->entity_type . DIRECTORY_SEPARATOR
                . $this->entity_id . DIRECTORY_SEPARATOR
                . $hash . '.' . $ext;
    }
}
