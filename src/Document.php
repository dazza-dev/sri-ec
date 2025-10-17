<?php

namespace DazzaDev\SriEc;

use DazzaDev\SriAccessKeyGenerator\AccessKeyGenerator;
use DazzaDev\SriXmlGenerator\Factories\DocumentBuilderFactory;
use DOMDocument;
use InvalidArgumentException;

class Document
{
    /**
     * Environment Code
     */
    private int $environmentCode;

    /**
     * Document type
     */
    private string $documentType;

    /**
     * Document type code
     */
    private string $documentTypeCode;

    /**
     * Access key
     */
    private string $accessKey;

    /**
     * Document data
     */
    private array $documentData;

    /**
     * Document instance
     */
    private mixed $document;

    /**
     * Document XML
     */
    private DOMDocument $documentXml;

    /**
     * Document type mapping
     */
    private const DOCUMENT_TYPE_MAP = [
        'invoice' => '01',
        'credit-note' => '04',
        'debit-note' => '05',
        'delivery-guide' => '06',
        'withholding-receipt' => '07',
    ];

    /**
     * Constructor
     */
    public function __construct(int $environmentCode, string $documentType, array $documentData)
    {
        $this->setEnvironmentCode($environmentCode);
        $this->setDocumentType($documentType);
        $this->setDocumentData($documentData);
        $this->generateAccessKey();
        $this->buildDocument();
    }

    /**
     * Set environment code
     */
    private function setEnvironmentCode(int $environmentCode): void
    {
        $this->environmentCode = $environmentCode;
    }

    /**
     * Set document type
     */
    private function setDocumentType(string $documentType): void
    {
        if (! array_key_exists($documentType, self::DOCUMENT_TYPE_MAP)) {
            throw new InvalidArgumentException("Invalid document type: {$documentType}");
        }

        $this->documentType = $documentType;
        $this->documentTypeCode = self::DOCUMENT_TYPE_MAP[$documentType];
    }

    /**
     * Set document data
     */
    private function setDocumentData(array $documentData): void
    {
        $this->documentData = $documentData;
    }

    /**
     * Generate access key
     */
    private function generateAccessKey(): void
    {
        $this->accessKey = AccessKeyGenerator::generate([
            'date' => $this->documentData['date'],
            'ruc' => $this->documentData['company']['ruc'],
            'document_type' => $this->documentTypeCode,
            'environment_code' => $this->environmentCode,
            'sequential' => $this->documentData['sequential'],
            'establishment_code' => $this->documentData['establishment']['code'],
            'emission_point_code' => $this->documentData['emission_point']['code'],
        ]);
    }

    /**
     * Build document using DocumentBuilderFactory
     */
    private function buildDocument(): void
    {
        $builder = DocumentBuilderFactory::create(
            $this->environmentCode,
            $this->documentType,
            $this->accessKey,
            $this->documentData
        );

        $this->document = $builder->getDocument();
        $this->documentXml = $builder->getXml();
    }

    /**
     * Get document type
     */
    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    /**
     * Get document type code
     */
    public function getDocumentTypeCode(): string
    {
        return $this->documentTypeCode;
    }

    /**
     * Get access key
     */
    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    /**
     * Get document data
     */
    public function getDocumentData(): array
    {
        return $this->documentData;
    }

    /**
     * Get document instance
     */
    public function getDocument(): mixed
    {
        return $this->document;
    }

    /**
     * Get document Number
     */
    public function getDocumentNumber(): string
    {
        return $this->document->getDocumentNumber();
    }

    /**
     * Get document XML
     */
    public function getDocumentXml(): DOMDocument
    {
        return $this->documentXml;
    }

    /**
     * Get available document types
     */
    public static function getAvailableDocumentTypes(): array
    {
        return array_keys(self::DOCUMENT_TYPE_MAP);
    }
}
