<?php

namespace DocCheck\Command\Result;

class Target
{
    private $name;
    private $filteredFiles = [];
    private $failedFiles = [];
    private $unparsedFiles = [];

    public function addFilteredFiles(array $files)
    {
        $this->filteredFiles = array_merge($files, $this->filteredFiles);
    }

    public function addUnparsedFiles(array $files)
    {
        $this->unparsedFiles = array_merge($files, $this->unparsedFiles);
    }

    public function addFailedFiles(array $files)
    {
        $this->failedFiles = array_merge($files, $this->failedFiles);
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
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