<?php

namespace DocCheck\Command\Result;

use DocCheck\Command\Result\Target;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\File\LocalFile;
use phpDocumentor\Reflection\Php\ProjectFactory;

class Target
{
    private $name;
    private $filteredFiles = [];
    private $failedFiles = [];
    private $unparsedFiles = [];

    public function __construct($name, $fileSystem) 
    {
        $this->name = $name;
        $this->parseFiles($fileSystem);
    }

    private function parseFiles($fileSystem) 
    {
        $files = $fileSystem->listContents($this->name, true);
        $this->filteredFiles = array_filter($files, function ($entry) {
            return key_exists('extension', $entry) && $entry['extension'] == 'php';
        });

        foreach ($this->filteredFiles as $file) {
            $filePath = $file['path'];
            try {
                $hasDocumentationLink = $this->hasDocumentationLink($filePath);
            } catch(\Throwable $t) {
                array_push($this->unparsedFiles, $filePath);
                continue;
            }

            if (!$hasDocumentationLink) {
                array_push($this->failedFiles, $filePath);
            };
        }
    }

    /**
     * @param string $filePath
     * @return bool
     */
    private function hasDocumentationLink(string $filePath): bool
    {
        $projectFactory = ProjectFactory::createInstance();
        $files = [new LocalFile($filePath)];
        $project = $projectFactory->create('MyProject', $files);
        $docblock = $project->getFiles()[$filePath]->getDocBlock();
        if(!$docblock instanceof DocBlock) {
           return false;
        }
        return (count($docblock->getTagsByName('see')) > 0|| count($docblock->getTagsByName('link')) > 0);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getFilteredFiles()
    {
        return $this->filteredFiles;
    }

    /**
     * @return mixed
     */
    public function getFailedFiles()
    {
        return $this->failedFiles;
    }

    /**
     * @return mixed
     */
    public function getUnparsedFiles()
    {
        return $this->unparsedFiles;
    }
}