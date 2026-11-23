# HotelHUB - Documentacion funcional para chatbot

Documento base para alimentar un chatbot sobre las funcionalidades del SaaS HotelHUB. La informacion fue levantada desde las rutas, vistas, controladores y modelos del proyecto Laravel.

## 1. Resumen general

HotelHUB es un SaaS de gestion hotelera y administracion de alojamientos. Permite operar una o varias sucursales desde un panel web con control de usuarios, permisos, habitaciones, reservas, check-in, check-out, ventas, caja, facturacion electronica, inventario, housekeeping, encuestas, contratos de alquiler de ambientes y salones/eventos.

El sistema esta orientado a hoteles, hospedajes, hostales, apart hoteles y negocios que combinan alojamiento con venta de productos/servicios, alquiler de ambientes y gestion de pagos.

## 2. Perfiles y acceso

### Acceso principal

- Login de usuarios del sistema.
- Registro y recuperacion de contrasena mediante los flujos estandar de Laravel Breeze.
- Perfil de usuario con edicion y eliminacion de cuenta.
- Validacion de sucursal activa para operar dentro del sistema.
- Dashboard principal con modulos habilitados segun sucursal, perfil y permisos.

### Acceso housekeeping

- Login especial para personal de limpieza mediante URL con RUC de la sucursal.
- El personal de limpieza ve sus jornadas y habitaciones asignadas.
- Puede iniciar limpieza, finalizar limpieza y registrar observaciones.
- Al finalizar todas las tareas de una jornada, la jornada se marca como completada.

### Control de permisos

- Existen perfiles por sucursal.
- Se configuran permisos por usuario, perfil, seccion, operacion y opcion de menu.
- El dashboard y menu lateral se construyen dinamicamente segun permisos.
- Las opciones pueden marcarse como acceso rapido.
- Algunas configuraciones administrativas estan protegidas por `check.permission:15`.

## 3. Dashboard y navegacion

El dashboard muestra secciones activas configuradas para la sucursal. Cada seccion puede abrir opciones de menu relacionadas con modulos, operaciones y vistas.

La navegacion incluye:

- Menu lateral dinamico por seccion.
- Accesos rapidos.
- Boton de turno/caja del usuario.
- Enlaces de ayuda.
- Perfil y cierre de sesion.
- Notificacion de proximidad de checkout mediante ping periodico.

## 4. Gestion de empresa y sucursales

### Empresa

- CRUD de empresas.
- Vista "Mi empresa".
- Asociacion con sucursales.
- Configuracion de datos principales del negocio.

### Sucursales

- CRUD de sucursales.
- Vista de "Mis sucursales".
- Configuracion por sucursal.
- Importacion de productos.
- Asociacion con parametros, perfiles, tablas, campos, secciones, operaciones, categorias, unidades y productos.

### Parametros del sistema

- Parametros generales.
- Categorias de parametros.
- Parametros por empresa.
- Parametros por sucursal.
- Pantalla de configuracion del sistema.
- Importacion y actualizacion masiva de parametros por sucursal.

## 5. Habitaciones y alojamiento

### Pisos

- CRUD de pisos por sucursal.
- Se usan para agrupar habitaciones.
- Permiten filtrar vista general y reportes.

### Tipos de habitacion

- CRUD de tipos de habitacion.
- Campos funcionales esperados: nombre, descripcion, precio, capacidad, sucursal.
- Asociacion de amenities.
- Galeria de imagenes por tipo de habitacion.
- Eliminacion logica/estado segun implementacion.

### Imagenes de tipo de habitacion

- Carga de imagenes.
- Reemplazo de imagenes existentes.
- Eliminacion de imagenes.
- Consulta por API para integraciones externas o landing.

### Amenities

- Registro de amenities por tipo de habitacion.
- Cada amenity puede tener nombre e icono.
- API para listar amenities por tipo de habitacion o por sucursal.
- Eliminacion de amenities.

### Habitaciones

- CRUD de habitaciones.
- Creacion individual o por bloques.
- Relacion con piso, tipo de habitacion y sucursal.
- Estados funcionales:
  - Disponible.
  - Ocupada.
  - Mantenimiento.
  - Limpieza.
  - Reservada, cuando aplica desde flujos de reserva.
- Filtros por estado, piso y tipo.
- Verificacion de estado de habitacion.
- Cambios manuales a ocupada o disponible.

## 6. Vista general de habitaciones

La vista general permite operar el alojamiento desde una pantalla visual:

- Ver habitaciones por piso, tipo/categoria y estado.
- Filtrar por categoria, piso o estado.
- Identificar habitaciones disponibles, ocupadas, en limpieza o mantenimiento.
- Iniciar check-in desde una habitacion.
- Editar check-in de una habitacion ocupada.
- Realizar checkout.
- Agregar productos o servicios al alojamiento.
- Enviar habitacion a limpieza.
- Marcar limpieza completada.
- Enviar habitacion a mantenimiento.
- Reservar habitacion.
- Abrir calendario de reservas.

## 7. Check-in

El check-in registra un movimiento de alojamiento.

Funciones principales:

- Crear alojamiento desde habitacion disponible.
- Asociar cliente principal.
- Registrar acompanantes.
- Seleccionar una o varias habitaciones asociadas al alojamiento.
- Definir fechas de entrada y salida.
- Manejar modalidad por horas o por noche/dia, segun tarifas configuradas.
- Registrar tarifa por habitacion.
- Asociar recepcionista.
- Asociar reserva previa cuando existe.
- Generar movimientos internos.
- Actualizar estado de habitacion a ocupada.
- Registrar historial de habitacion por turno/caja cuando corresponde.

Datos relacionados:

- Cliente/persona.
- Huespedes.
- Habitaciones.
- Movhabitacion.
- MovhabitacionHabitacion.
- Movimientos.
- Caja/turno si se cobra en el flujo.

## 8. Edicion de check-in

El sistema permite editar alojamientos activos:

- Cambiar datos del cliente o huespedes.
- Cambiar habitaciones asociadas.
- Actualizar acompanantes.
- Ajustar fechas y tarifas.
- Recalcular importes.
- Mantener trazabilidad del movimiento.

## 9. Productos y servicios en alojamiento

Desde un alojamiento se pueden agregar consumos:

- Productos.
- Servicios.
- Cantidades, precios y totales.
- Asociacion del consumo al movimiento de alojamiento.
- Integracion con stock e inventario cuando son productos.
- Posterior cobro en checkout o venta.

## 10. Checkout

El checkout cierra o cobra un alojamiento.

Funciones principales:

- Consultar detalle de habitacion, cliente, fechas, tarifa, productos y servicios.
- Calcular subtotal, IGV y total.
- Aplicar late checkout y early check-in.
- Seleccionar tipo de documento.
- Seleccionar caja activa.
- Registrar uno o varios medios de pago.
- Manejar efectivo, tarjeta, transferencia, deposito, billetera digital y POS/pasarela.
- Generar venta asociada.
- Generar movimiento de caja.
- Generar comprobante o documento interno.
- Enviar facturacion electronica cuando corresponde.
- Imprimir o generar PDF.
- Cambiar habitacion a limpieza o disponible segun flujo.

Acciones posibles:

- Procesar checkout con pago.
- Procesar checkout sin pago segun flujo.
- Finalizar alojamiento.
- Reactivar alojamiento.
- Anular alojamiento con codigo de aprobacion.

## 11. Recordatorios y notificaciones de checkout

El sistema incluye un evento `CheckoutReminder` y un ping periodico:

- Verifica proximidad de checkout.
- Dispara notificaciones internas.
- Tiene sonido publico `new_checkout.mp3`.
- En entorno local existe una ruta de prueba para disparar una notificacion de checkout.

## 12. Historial y reportes de alojamientos

Funciones:

- Listado de alojamientos.
- Listado de checkouts.
- Historial de ventas asociadas a alojamiento.
- Resumen de alojamientos en PDF.
- Exportacion Excel de alojamientos.
- Exportacion PDF de checkouts.
- Filtros por habitacion, cliente, estado, fechas, turno, modalidad y situacion.
- Historial de alojamientos por huesped.

## 13. Reservas de habitaciones

### Gestion de reservas

- CRUD de reservas.
- Crear reserva de habitacion.
- Seleccionar cliente, fechas, habitaciones y origen.
- Manejar motivo de reserva.
- Asociar descuento y precio de habitacion.
- Registrar acompanantes.
- Cambiar estado/situacion de la reserva.
- Ver detalles de reserva.
- Atender reserva y convertirla en alojamiento/check-in.
- Anular reserva.
- Finalizar reserva.
- Generar PDF de reserva.
- Reporte de reservas en PDF y Excel.
- Filtros por fecha, cliente y situacion.

### Disponibilidad

- Consulta de habitaciones disponibles por rango de fechas.
- Consulta por tipo de habitacion.
- Exclusion de habitaciones ya reservadas en el rango.

### Origen y motivos

- CRUD de origen de reserva.
- El origen puede tener logo.
- CRUD de motivos de reserva.

## 14. Reservas de paquetes y salones

HotelHUB tambien soporta reservas relacionadas con salones y paquetes.

### Salones

- CRUD de salones.
- Registro de titulo, descripcion, aforo, precio, imagen y sucursal.
- Estados activos/desactivados.
- Listado por sucursal via API.
- Consulta de detalle de salon via API.

### Servicios de salon

- CRUD de servicios por salon.
- Listado de servicios vinculados a un salon.

### Paquetes de salon

- CRUD de paquetes por salon.
- Reserva de paquetes.
- Consulta de reserva de paquete via API.

## 15. Huespedes y personas

### Personas

- CRUD de personas/clientes.
- Datos personales y documento.
- Relacion con sucursal.
- Marcado de persona no grata.
- Uso en ventas, alojamientos, reservas, contratos y encuestas.

### Huespedes

- CRUD de huespedes.
- Asociacion a alojamiento o reserva.
- Reporte PDF de huespedes.
- Filtro dinamico de huespedes.
- Historial de alojamientos por huesped.

## 16. Ventas

El modulo de ventas maneja ventas directas y ventas generadas desde alojamiento o contratos.

Funciones:

- Listado de ventas.
- Crear venta directa.
- Seleccionar cliente, vendedor, productos/servicios, caja y tipo de documento.
- Calcular subtotal, IGV y total.
- Registrar medio de pago.
- Soportar multiples medios de pago.
- Registrar detalle de caja por cada pago.
- Generar comprobante PDF.
- Imprimir comprobante.
- Convertir una venta a comprobante.
- Ver detalles de venta.
- Ver alojamiento relacionado a una venta.
- Anular venta.
- Generar codigo de aprobacion para anulaciones.
- Reporte de ventas.
- Exportacion PDF y Excel de ventas.
- Filtros por tipo, turno, fechas y caja.

Medios de pago soportados:

- Efectivo.
- Tarjeta.
- Transferencia.
- Deposito.
- Billetera digital.
- POS/pasarela.

## 17. Caja y turnos

### Cajas

- CRUD de cajas.
- Caja asociada a sucursal.
- Estado de caja: abierta/activa o cerrada, segun campo `situacion`.
- Serie para documentos.

### Turnos

- CRUD de turnos.
- Relacion turno-caja.
- Vista "mi turno" para el cajero.

### Movimientos de caja

Funciones:

- Apertura de caja.
- Cierre de caja.
- Ingresos.
- Egresos.
- Registro de movimientos por concepto de pago.
- Detalle por medio de pago.
- Anulacion de movimientos.
- Ver detalle de movimiento.
- Reporte de movimientos de caja.
- Filtro por caja, turno, concepto, fechas y busqueda.
- Sincronizacion de apertura.
- Resumen de estado de caja.

### Cierre y arqueo

- Generacion de PDF de cierre.
- Generacion de ticket de cierre.
- Ticket de arqueo.
- PDF de arqueo.
- Resumen por medio de pago.
- Resumen de ingresos, egresos, apertura y ventas.
- Resumen de alojamientos pagados, sin pagar, con checkout o pendientes.
- Inclusion de anulaciones de alojamientos.
- Inclusion de ventas por producto y stock inicial/final mediante Kardex.

## 18. Facturacion electronica y comprobantes

### Facturador

- CRUD de configuracion de facturador por sucursal.
- Activar/desactivar facturador.
- Asociacion con datos de emision.

### Solicitudes/comprobantes

- Listado de solicitudes de facturacion.
- Descarga de XML.
- Sincronizacion de comprobantes.
- Envio de comprobantes pendientes.
- Envio de boletas pendientes.
- Envio de facturas pendientes.
- Solicitud de anulacion de boletas.
- Solicitud de anulacion de facturas.
- Resumen de boletas anuladas.
- Resumen de facturas anuladas.
- Detalle de anulaciones.
- Envio de comprobante por correo.
- Envio de comprobante por WhatsApp.
- Reporte de comprobantes.
- Exportacion PDF y Excel.

Documentos y conceptos relacionados:

- Tipo de documento.
- Tipo de movimiento.
- Concepto de pago.
- IGV.
- Series de caja.

## 19. Inventario y almacen

### Productos

- CRUD de productos.
- Asociacion con marca, categoria, unidad, subalmacen, ubicacion y sucursal.
- Filtro por categoria y clasificacion.
- Consulta de detalle de producto.
- Importacion de productos por sucursal.
- Generacion e impresion de codigos de barra.

### Categorias y marcas

- CRUD de categorias.
- Categorias padre/hijas.
- Activar/desactivar categoria.
- Ordenamiento de categorias.
- CRUD de marcas.

### Almacen

- CRUD de movimientos de almacen.
- Entrada de almacen.
- Salida de almacen.
- Detalle de movimiento.
- Anulacion de movimiento.
- Registro de subalmacenes.
- Registro de ubicaciones.
- Lista de unidades.
- Unidades por sucursal.

### Stock y Kardex

- CRUD/consulta de stock de productos.
- Filtro de stock.
- Kardex de movimientos.
- Registro de stock anterior y stock actual.
- Uso en reportes de cierre de caja y ventas.

## 20. Housekeeping

### Jornadas de limpieza

- CRUD de jornadas de housekeeping.
- Seleccion de habitaciones por sucursal.
- Agrupacion por pisos.
- Asignacion de empleados a habitaciones.
- Estado inicial de jornada.
- Edicion de jornada.
- Eliminacion de jornada.

### Asignacion y seguimiento

- Vista de progreso de jornada.
- Vista de asignacion.
- Reasignacion de empleados.
- Detalle por habitacion.
- Estados de detalle:
  - Pendiente.
  - En progreso.
  - Completado.
- Registro de inicio y fin de limpieza.
- Calculo de tiempo total cuando se completa la jornada.

### Portal de limpieza

- Login independiente para empleados de limpieza.
- Muestra solo las tareas del empleado.
- Permite iniciar y terminar limpieza.
- Guarda observaciones.

## 21. Encuestas

### Configuracion de encuestas

- CRUD de encuestas.
- UUID de encuesta para API.
- Mensaje de finalizacion.
- Configuracion de validacion de cliente.
- Activar/desactivar encuesta.
- Finalizar encuesta.

### Preguntas y respuestas

- Gestion de preguntas por encuesta.
- Categorias de pregunta.
- Tipos de pregunta.
- Respuestas almacenadas.
- Valores/detalles de respuesta.
- Vista de respuestas por encuesta.
- Vista de detalle de respuestas.

### Estadisticas

- Estadisticas por encuesta.
- Conteo de respuestas asociadas a responsables/recepcionistas desde movimientos de alojamiento.

### API de encuesta

Endpoints:

- `GET /api/survey/customer/{id}`: obtiene datos de cliente para validar encuesta.
- `GET /api/survey/checkRoomCode/{id}`: obtiene datos de habitacion/movimiento.
- `GET /api/survey`: obtiene definicion de encuesta y preguntas.
- `POST /api/survey`: guarda respuestas de encuesta.

La API usa middleware `survey.api` y espera UUID de encuesta en encabezados. Si la encuesta requiere validacion, pide documento del cliente.

## 22. Contratos y alquiler de ambientes

HotelHUB incluye un modulo para administrar contratos de alquiler, ambientes, edificios y compromisos de pago.

### Edificios

- CRUD de edificios.
- Asociacion con sucursal.
- Busqueda y paginacion.

### Tipos de ambiente

- CRUD de tipos de ambiente.
- Se usan para clasificar ambientes y asociarlos con plantillas de contrato.

### Ambientes

- CRUD de ambientes.
- Asociacion con edificio, piso, tipo de ambiente y sucursal.
- Campos funcionales: numero, monto, garantia, situacion.
- Situaciones usadas:
  - `L`: libre.
  - `A`: ocupado/asignado.
  - Activo/Desactivado en listados administrativos.
- API interna para obtener monto y garantia de un ambiente.

### Plantillas de contrato

- CRUD de plantillas.
- Asociacion con tipo de ambiente.
- Contenido HTML.
- Variables dinamicas.
- Activar/desactivar plantilla.

### Variables de plantilla

- CRUD de variables.
- Variables disponibles para insertar en plantillas.
- Se reemplazan al generar vista previa/PDF.

### Contratos

Funciones:

- Listado y busqueda de contratos.
- Crear contrato.
- Numero de contrato autogenerado incremental.
- Asociar arrendatario, arrendador, ambiente y edificio.
- Definir fechas, monto, garantia, pago anticipado y condiciones.
- Generar vista previa PDF desde plantilla.
- Guardar PDF del contrato.
- Crear compromisos de pago al guardar contrato.
- Cambiar ambiente a ocupado al crear contrato.
- Editar contrato y reemplazar PDF.
- Anular contrato.
- Finalizar contrato y liberar ambiente.
- Renovar contrato.
- Consultar pagos del contrato.
- Filtrar ambientes libres por edificio.

### Compromisos de pago

Funciones:

- Listar compromisos de un contrato.
- Crear, editar y anular compromisos.
- Estados:
  - pendiente.
  - pagado.
  - anulado.
- Pagar compromiso total o parcial.
- Generar venta asociada al pago.
- Registrar movimiento de caja.
- Registrar medios de pago.
- Emitir comprobante o ticket.
- Ver historial de ventas parciales por compromiso.

## 23. Modulos administrativos y maestros

El sistema cuenta con maestros configurables que alimentan los flujos principales:

- Bancos.
- Tarjetas.
- Billeteras digitales.
- Pasarelas/POS.
- Medios de pago.
- Conceptos de pago.
- IGV.
- Motivos de movimiento.
- Motivos de reserva.
- Origenes de reserva.
- Situaciones.
- Tipos de documento.
- Tipos de movimiento.
- Unidades.
- Ubigeo.
- Ubicaciones.
- Subalmacenes.
- Distribuidores.
- Roles y roles de persona.
- Personas maestras.
- Dinero.
- Anulaciones.

Varios maestros incluyen activar/desactivar, busqueda, paginacion y operaciones por permisos.

## 24. Seguridad y permisos operativos

Elementos de seguridad funcional:

- Autenticacion requerida para el panel principal.
- Middleware de sucursal activa.
- Permisos por perfil/sucursal/seccion/operacion.
- Operaciones activas/inactivas.
- Menu dinamico filtrado por permisos.
- Codigos de aprobacion para anulaciones sensibles.
- Separacion de login housekeeping.
- Rutas API de encuesta con middleware especifico.

## 25. APIs publicas/integracion

### Habitaciones

- `GET /api/habitacion/{sucursal_id}/{tipohabitacion_id?}`: lista habitaciones por sucursal y opcionalmente por tipo.
- `GET /api/habitaciones/disponibles/{fecha}/{fechafin}/{sucursal_id}/{tipohabitacion_id?}`: lista habitaciones disponibles por rango de fechas.

### Imagenes y tipos de habitacion

- `POST /api/habitacion/{tipohabitacionId}/imagenes`: carga imagen para tipo de habitacion.
- `GET /api/habitacion/{tipohabitacionId}/imagenes`: lista imagenes de tipo de habitacion.
- `GET /api/tipohabitacion/{sucursal_id}`: lista tipos de habitacion con imagenes, amenities y habitaciones disponibles.
- `GET /api/tipohabitacion/detalle/{id}`: detalle de tipo de habitacion.

### Salones

- `POST /api/salon/store/{sucursal_id}`: crea salon via API.
- `GET /api/salon/{sucursal_id}`: lista salones por sucursal.
- `GET /api/salon/{salon_id}/detalles`: detalle de salon.

### Amenities

- `POST /api/amenities/{tipohabitacionId}`: crea/actualiza amenities de tipo de habitacion.
- `GET /api/amenities/tipo-habitacion/{tipohabitacionId}`: lista amenities por tipo.
- `GET /api/amenities/{sucursal_id}`: lista amenities por sucursal.
- `DELETE /api/amenities/{amenityId}`: elimina amenity.

### Reservas

- `POST /api/reserva/habitacion`: crea reserva de habitacion.
- `GET /api/reserva/habitacion/{id}`: consulta reserva de habitacion.
- `POST /api/reserva/paquete`: crea reserva de paquete.
- `GET /api/reserva/paquete/{id}`: consulta reserva de paquete.

### Encuestas

- `GET /api/survey/customer/{id}`.
- `GET /api/survey/checkRoomCode/{id}`.
- `GET /api/survey`.
- `POST /api/survey`.

## 26. Reportes y exportaciones

Reportes identificados:

- Reporte de reservas.
- PDF de reserva.
- Reporte de reservas en PDF.
- Reporte de reservas en Excel.
- Reporte de ventas.
- Reporte de ventas en PDF.
- Reporte de ventas en Excel.
- PDF de comprobante.
- Impresion de comprobante.
- Reporte de comprobantes.
- Reporte de comprobantes en PDF.
- Reporte de comprobantes en Excel.
- Reporte de caja.
- PDF de cierre de caja.
- Ticket de cierre de caja.
- Arqueo ticket.
- Arqueo PDF.
- Reporte de huespedes en PDF.
- Reporte/resumen de alojamientos en PDF.
- Exportacion de alojamientos en Excel.
- Exportacion de checkouts en PDF.
- Estadisticas de encuestas.
- Historial de ventas de compromisos de pago.

## 27. Estados y vocabulario importante

### Habitacion

- Disponible: puede recibir check-in o reserva.
- Ocupada: tiene alojamiento activo.
- Limpieza: requiere limpieza o esta en proceso de limpieza.
- Mantenimiento: no disponible por mantenimiento.
- Reservada: apartada para reserva, segun flujo.

### Movimiento

- Activado: movimiento valido.
- D: movimiento anulado/desactivado en algunos flujos.

### Alojamiento

- Activo/en curso.
- Con checkout.
- Sin checkout.
- Pagado.
- Sin pagar.
- Finalizado.
- Reactivado.
- Anulado.

### Reserva

- Creada/pendiente.
- Atendida.
- Finalizada.
- Anulada.

### Caja

- Apertura de caja.
- Cierre de caja.
- Ingreso.
- Egreso.
- Caja activa.
- Caja cerrada.

### Housekeeping

- Pendiente.
- En progreso.
- Completado.

### Contrato

- Activo.
- Anulado: `C`.
- Finalizado/retirado: `R`.

### Ambiente

- Libre: `L`.
- Ocupado/asignado: `A`.

### Compromiso de pago

- pendiente.
- pagado.
- anulado.

### Encuesta

- Activa/habilitada.
- Desactivada.
- FINALIZADO.

## 28. Preguntas frecuentes sugeridas para el chatbot

### Que es HotelHUB?

HotelHUB es un SaaS para gestionar hoteles y alojamientos: habitaciones, reservas, check-in, checkout, caja, ventas, facturacion electronica, inventario, housekeeping, encuestas, contratos y salones.

### Que puede hacer recepcion?

Puede ver habitaciones, filtrar por estado, hacer check-in, editar alojamientos, agregar productos/servicios, realizar checkout, cobrar, imprimir comprobantes, gestionar reservas, consultar historial y enviar habitaciones a limpieza o mantenimiento.

### Que puede hacer caja?

Puede abrir caja, registrar ingresos y egresos, cobrar ventas y alojamientos, cerrar caja, generar arqueos, revisar movimientos, filtrar por turno y generar reportes PDF/ticket.

### Que puede hacer housekeeping?

Puede recibir jornadas y habitaciones asignadas, iniciar limpieza, finalizar limpieza, dejar observaciones y actualizar el estado de sus tareas.

### Se puede emitir comprobantes?

Si. El sistema integra facturacion electronica, genera comprobantes, permite imprimir PDF, enviar por correo o WhatsApp, sincronizar comprobantes y gestionar anulaciones de boletas/facturas.

### Se puede vender productos?

Si. Hay venta directa y consumos asociados al alojamiento. Los productos se relacionan con inventario, stock y kardex.

### Se puede controlar inventario?

Si. El sistema gestiona productos, categorias, marcas, unidades, almacenes, entradas, salidas, stock y kardex.

### Se pueden hacer reservas online o integraciones?

El sistema tiene APIs para consultar habitaciones, disponibilidad, tipos de habitacion, imagenes, amenities, salones y crear/consultar reservas de habitacion o paquetes.

### Se pueden gestionar alquileres o contratos?

Si. Tiene edificios, ambientes, tipos de ambiente, plantillas de contrato, contratos, compromisos de pago, pagos parciales, comprobantes y liberacion de ambientes al finalizar.

### Se pueden hacer encuestas a huespedes?

Si. Se configuran encuestas con preguntas, validacion opcional del cliente, API publica y estadisticas de respuestas.

## 29. Entidades principales para indexar en el chatbot

- Empresa.
- Sucursal.
- Usuario.
- Perfil.
- Permiso.
- Seccion.
- Menu opcion.
- Operacion.
- Persona.
- Huesped.
- Piso.
- Tipo de habitacion.
- Habitacion.
- Imagen de tipo de habitacion.
- Amenity.
- Movhabitacion.
- MovhabitacionHabitacion.
- Huesped de alojamiento.
- Movreserva.
- MovreservaHabitacion.
- MovreservaPaquete.
- Origen reserva.
- Motivo reserva.
- Caja.
- Turno.
- RelTurnocaja.
- Movcaja.
- DetMovcaja.
- Movventa.
- DetMovventa.
- Solicitud/comprobante.
- Facturador.
- Producto.
- Categoria.
- Marca.
- Unidad.
- Subalmacen.
- Ubicacion.
- Stockproducto.
- Kardex.
- Housekeeping.
- HousekeepingDetalle.
- Encuesta.
- Pregunta.
- Respuesta.
- Detalle de respuesta.
- Salon.
- SalonService.
- SalonPackage.
- Edificio.
- Ambiente.
- Tipoambiente.
- Contrato.
- PlantillaContrato.
- VariablesPlantilla.
- CompromisoPago.

## 30. Limites o consideraciones

- Algunas rutas y vistas son CRUD genericos generados, pero siguen siendo parte de los maestros del sistema.
- Los nombres internos pueden estar en singular/plural mezclado por convencion del proyecto: `movventa`, `movhabitacion`, `movreserva`, `tipohabitacion`, etc.
- Hay endpoints API duplicados para amenities en `routes/api.php`; para documentacion del chatbot se consideran como una sola capacidad funcional.
- El README del repositorio aun es el README base de Laravel, por lo que esta documentacion debe tomarse como fuente funcional principal.
- Para respuestas del chatbot orientadas al usuario final, conviene hablar en terminos de modulos: Recepcion, Reservas, Caja, Ventas, Inventario, Housekeeping, Facturacion, Encuestas, Contratos y Configuracion.

