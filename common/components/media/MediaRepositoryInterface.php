<?php

namespace app\common\components\media;

interface MediaRepositoryInterface
{
    public function getPath(string $hash, string $ext) : string;
    public function save(string $filePath, string $name, string $ext) : string;
    public function delete(string $filePath) : void;
}