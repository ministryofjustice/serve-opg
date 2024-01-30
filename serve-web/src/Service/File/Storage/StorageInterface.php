<?php

namespace App\Service\File\Storage;

interface StorageInterface
{
    public function retrieve(string $key);

    public function delete(string $key);

    public function store(string $key, string $body);
}
