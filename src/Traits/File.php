<?php

namespace DazzaDev\SriEc\Traits;

use DazzaDev\SriEc\Exceptions\FileException;

trait File
{
    /**
     * File path
     */
    protected ?string $filePath = null;

    /**
     * File name
     */
    protected ?string $fileName = null;

    /**
     * File path
     */
    protected function validateFilePath()
    {
        if (is_null($this->filePath)) {
            throw new FileException('File path is not set');
        }
    }

    /**
     * Set file path
     */
    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    /**
     * Get file path
     */
    public function getFilePath()
    {
        $this->validateFilePath();

        return $this->filePath;
    }

    /**
     * Set file name
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * Get file name
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Get XML path
     */
    public function getXmlPath(): string
    {
        $this->validateFilePath();

        return $this->filePath.'/xml';
    }

    /**
     * Get Generated XML path
     */
    public function getGeneratedXmlPath(): string
    {
        return $this->getXmlPath().'/generated';
    }

    /**
     * Get Signed XML path
     */
    public function getSignedXmlPath(): string
    {
        return $this->getXmlPath().'/signed';
    }

    /**
     * Get Authorized XML path
     */
    public function getAuthorizedXmlPath(): string
    {
        return $this->getXmlPath().'/authorized';
    }

    /**
     * Save signed file
     */
    protected function saveSignedFile(string $documentType, string $fileName, string $fileContent): string
    {
        return $this->saveFile(
            folder: $this->getSignedXmlPath().'/'.$documentType,
            fileName: $fileName,
            fileContent: $fileContent
        );
    }

    /**
     * Save authorized file
     */
    protected function saveAuthorizedFile(string $documentType, string $fileName, string $fileContent): string
    {
        return $this->saveFile(
            folder: $this->getAuthorizedXmlPath().'/'.$documentType,
            fileName: $fileName,
            fileContent: $fileContent
        );
    }

    /**
     * Save file
     */
    protected function saveFile(string $folder, string $fileName, string $fileContent): string
    {
        // Create directories
        $this->createDirectories();

        // Set file name
        $this->setFileName($fileName.'.xml');

        // Save signed XML document
        $filePath = $folder.'/'.$this->getFileName();
        $file = file_put_contents($filePath, $fileContent);

        if (! $file) {
            throw new FileException('Error saving file: '.$filePath);
        }

        return $file;
    }

    /**
     * Create directories
     */
    protected function createDirectories()
    {
        $filePath = $this->getFilePath();

        // Create base directory if it doesn't exist
        if (! file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }

        // Create signed directory with subdirectories inside xml if it doesn't exist
        $this->createDocumentTypeDirectories($filePath.'/xml/signed');

        // Create authorized directory with subdirectories inside xml if it doesn't exist
        $this->createDocumentTypeDirectories($filePath.'/xml/authorized');
    }

    /**
     * Create document type directories with subdirectories
     */
    private function createDocumentTypeDirectories(string $basePath): void
    {
        if (! file_exists($basePath)) {
            mkdir($basePath, 0777, true);
            mkdir($basePath.'/invoice', 0777, true);
            mkdir($basePath.'/withholding', 0777, true);
            mkdir($basePath.'/credit-note', 0777, true);
            mkdir($basePath.'/debit-note', 0777, true);
        }
    }
}
