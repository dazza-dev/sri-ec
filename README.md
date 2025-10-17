# SRI Ecuador 🇪🇨

Paquete para generar, firmar y enviar documentos electrónicos (Factura, Guía de remisión, Nota crédito, Nota débito y Comprobante de retención) al SRI (Ecuador).

## Instalación

```bash
composer require dazza-dev/sri-ec
```

## Configuración

```php
use DazzaDev\SriEc\Client;

$client = new Client(test: true); // true or false

$client->setCertificate([
    'path' => _DIR_ . '/certificado.p12',
    'password' => 'clave_certificado',
]);

// Ruta donde se guardarán los archivos xml
$client->setFilePath(_DIR_ . '/sri');
```

## Uso

### Enviar un documento electrónico

Para enviar un documento electrónico como Factura, Guía de remisión, Nota crédito, Nota débito o Comprobante de retención. primero debes pasar la estructura de datos que puedes encontrar en: [dazza-dev/sri-xml-generator](https://github.com/dazza-dev/sri-xml-generator).

### Ejemplo de uso (Factura)

```php
// Usar el valor en inglés de la tabla
$client->setDocumentType('invoice');

// Datos del documento
$client->setDocumentData($documentData);

// Enviar el documento
$document = $client->sendDocument();
```

### Tipos de documentos disponibles

| Documento                | Valor                 |
| ------------------------ | --------------------- |
| Factura                  | `invoice`             |
| Nota de crédito          | `credit-note`         |
| Nota de débito           | `debit-note`          |
| Guía de remisión         | `delivery-guide`      |
| Comprobante de retención | `withholding-receipt` |

### Obtener los listados

SRI tiene una lista de códigos que este paquete te pone a disposición para facilitar el trabajo de consultar esto en el anexo técnico:

```php
use DazzaDev\SriEc\Listing;

// Obtener los listados disponibles
$listings = Listing::getListings();

// Consultar los datos de un listado por tipo
$listingByType = Listing::getListing('identification-types');
```

## Contribuciones

Contribuciones son bienvenidas. Si encuentras algún error o tienes ideas para mejoras, por favor abre un issue o envía un pull request. Asegúrate de seguir las guías de contribución.

## Autor

SRI Ecuador fue creado por [DAZZA](https://github.com/dazza-dev).

## Licencia

Este proyecto está licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).
