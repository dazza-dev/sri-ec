<?php

namespace DazzaDev\SriEc;

use DazzaDev\SriEc\Exceptions\DocumentException;
use DazzaDev\SriEc\Traits\File;
use DazzaDev\SriSender\Sender;
use DazzaDev\SriSigner\Signer;
use DazzaDev\SriXmlGenerator\Enums\Environments;

class Client
{
    use File;

    /**
     * Is test environment
     */
    private bool $isTestEnvironment;

    /**
     * Environment
     */
    protected array $environment;

    /**
     * Document instance
     */
    protected ?Document $document = null;

    /**
     * Document type (temporary storage)
     */
    private string $documentType;

    /**
     * Signed document
     */
    protected string $signedDocument;

    /**
     * Certificate
     */
    protected array $certificate;

    /**
     * Signer
     */
    protected ?Signer $signer = null;

    /**
     * Sender
     */
    protected Sender $sender;

    /**
     * Constructor
     */
    public function __construct(bool $test = false)
    {
        $this->isTestEnvironment = $test;

        // Set environment
        if ($this->isTestEnvironment) {
            $this->setEnvironment(Environments::TEST);
        } else {
            $this->setEnvironment(Environments::PRODUCTION);
        }

        // Initialize Sender
        $this->sender = new Sender($this->isTestEnvironment);
    }

    /**
     * Set environment
     */
    protected function setEnvironment(Environments $environment): void
    {
        $this->environment = $environment->toArray();
    }

    /**
     * Get environment
     */
    public function getEnvironment(): array
    {
        return $this->environment;
    }

    /**
     * Is test environment
     */
    protected function isTestEnvironment(): bool
    {
        return $this->environment['code'] == Environments::TEST->value;
    }

    /**
     * Set certificate
     */
    public function setCertificate(array $certificate): void
    {
        $this->certificate = $certificate;

        // Set Signer
        $this->signer = new Signer(
            certificatePath: $this->certificate['path'],
            certificatePassword: $this->certificate['password']
        );
    }

    /**
     * Set document type
     */
    public function setDocumentType(string $documentType): void
    {
        $this->documentType = $documentType;
    }

    /**
     * Get document type
     */
    public function getDocumentType(): string
    {
        return $this->document?->getDocumentType() ?? '';
    }

    /**
     * Get document type code
     */
    public function getDocumentTypeCode(): string
    {
        return $this->document?->getDocumentTypeCode() ?? '';
    }

    /**
     * Get access key
     */
    public function getAccessKey(): string
    {
        return $this->document?->getAccessKey() ?? '';
    }

    /**
     * Set document data
     */
    public function setDocumentData(array $documentData): void
    {
        $this->document = new Document(
            $this->environment['code'],
            $this->documentType,
            $documentData
        );
    }

    /**
     * Sign document
     */
    public function signDocument(): string
    {
        if (! $this->document) {
            throw new DocumentException('Documento no establecido. Llama a setDocumentData() primero.');
        }

        if (! $this->signer) {
            throw new DocumentException('Certificado no establecido. Llama a setCertificate() primero.');
        }

        // Validate file path
        $this->validateFilePath();

        // Document XML
        $xml = $this->document->getDocumentXml();

        // Sign document
        $this->signedDocument = $this->signer->loadXML($xml)->sign();

        // Save signed document
        $this->saveSignedFile(
            $this->documentType,
            $this->getAccessKey(),
            $this->signedDocument
        );

        return $this->signedDocument;
    }

    /**
     * Send document
     */
    public function sendDocument(): array
    {
        if (! $this->document) {
            throw new DocumentException('Document not set. Call setDocumentData() first.');
        }

        // Sign document
        $this->signDocument();

        // Send document
        $send = $this->sender->send(
            $this->document->getAccessKey(),
            $this->signedDocument
        );

        // Check if document was sent successfully
        if (! $send['success']) {
            throw new DocumentException($send['status'].' - '.$send['error']);
        }

        // Save Authorized document
        if ($send['status'] == 'AUTORIZADO') {
            $autorizedXml = $send['authorization']['authorized_document']['xml'];
            $this->saveAuthorizedFile(
                $this->documentType,
                $this->getAccessKey(),
                $autorizedXml
            );
        }

        // Add document number to response
        $send['authorization']['authorized_document']['number'] = $this->document->getDocumentNumber();

        return $send['authorization'];
    }
}
