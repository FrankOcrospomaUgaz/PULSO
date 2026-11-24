<?php

return [

    'erp' => [
        'base_url' => env('ERP_API_BASE_URL'),
        'integration_secret' => env('ERP_INTEGRATION_SECRET'),
    ],

    'comprobantes_mail' => [
        'token' => env('COMPROBANTES_MAIL_TOKEN'),
    ],

    'facturacion' => [
        'boleta_wsdl' => env('FACTURACION_BOLETA_WSDL', 'http://157.245.85.164/facturacion/wsdl/wsdl_boleta_rc.php'),
        'factura_wsdl' => env('FACTURACION_FACTURA_WSDL', 'http://157.245.85.164/facturacion/wsdl/wsdl_factura_rc.php'),
        'nota_credito_wsdl' => env('FACTURACION_NOTA_CREDITO_WSDL', 'http://157.245.85.164/facturacion/wsdl/wsdl_notacredito.php'),
        'comprobantes_wsdl' => env('FACTURACION_COMPROBANTES_WSDL', 'http://157.245.85.164/facturacion/wsdl/wsdl_comprobantes.php'),
        'resumen_boletas_wsdl' => env('FACTURACION_RESUMEN_BOLETAS_WSDL', 'http://157.245.85.164/facturacion/wsdl/wsdl_resumenboletas.php'),
        'bajas_wsdl' => env('FACTURACION_BAJAS_WSDL', 'http://157.245.85.164/facturacion/wsdl/wsdl_comunicacionbajas.php'),
    ],

    'meta_whatsapp' => [
        'token' => env('META_AUTH_TOKEN'),
        'phone_number_id' => env('META_WHATSAPP_PHONE_NUMBER_ID'),
        'api_version' => env('META_WHATSAPP_API_VERSION', 'v19.0'),
        'clinical_automatic' => env('CLINICA_WHATSAPP_AUTOMATICO', false),
        'appointment_template' => env('META_TEMPLATE_RECORDATORIO_CITA'),
        'birthday_template' => env('META_TEMPLATE_CUMPLEMES'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
