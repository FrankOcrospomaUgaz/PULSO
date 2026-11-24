# PULSO

## Facturación electrónica

Los WSDL de boletas, facturas, notas de crédito, consulta/sincronización, resumen de boletas y comunicación de bajas se configuran con `FACTURACION_*_WSDL` en el entorno. Los valores iniciales conservan el proveedor existente; si el proveedor ofrece TLS o cambia su URL, configure la URL vigente sin modificar controladores. Las notas de crédito se pueden emitir sobre comprobantes aceptados por el total, con los motivos 01, 02 y 06 del catálogo SUNAT; cada nota guarda su referencia al comprobante afectado y queda disponible para consultar estado y descargar XML/CDR.

## Operación clínica PULSO+

La agenda clínica vive en `citas`, con catálogos de `pacientes`, `profesionales` y `servicios-clinicos`. Configure los turnos semanales en **Horarios profesionales** antes de reservar; las citas deben caber dentro de un turno y no cruzarse con otra cita del paciente o del profesional. La agenda, calendario y reportes usan la sucursal del usuario autenticado.

Los avisos por WhatsApp requieren consentimiento registrado en la ficha del paciente (o tutor), un número de WhatsApp Cloud API y plantillas aprobadas en español. Configure `META_AUTH_TOKEN`, `META_WHATSAPP_PHONE_NUMBER_ID`, `META_TEMPLATE_RECORDATORIO_CITA` y `META_TEMPLATE_CUMPLEMES`. La plantilla de cita recibe, en este orden: contacto, servicio, hora, profesional y sede. La plantilla de cumple-mes recibe: tutor/contacto, nombre del bebé y meses cumplidos. El envío automático está apagado por defecto; actívelo solo después de verificar las plantillas con `CLINICA_WHATSAPP_AUTOMATICO=true`.

El planificador de Laravel debe ejecutarse cada minuto en el servidor para enviar recordatorios del día (se revisan cada 30 minutos entre 07:00 y 21:00). `php artisan clinica:avisos` muestra solo un conteo de vista previa y no crea envíos ni contacta a nadie.

Las migraciones `2026_11_23_000001_create_clinical_schedules_and_notifications` y `2026_11_23_000002_add_clinical_notification_consent` añaden horarios, bitácora idempotente de avisos y consentimiento explícito; no alteran registros clínicos existentes. El reporte de vencimientos usa la fecha producto-sucursal que ofrece el esquema actual; no representa lotes independientes.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
