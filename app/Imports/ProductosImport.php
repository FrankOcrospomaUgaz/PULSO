<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\RelProductosucursal;
use App\Models\Listaunidad;
use App\Models\Persona;
use App\Models\RelListaunidadsucursal;
use App\Models\Sucursal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;

class ProductosImport implements ToCollection
{
    protected $sucursalId;

    public function __construct($sucursalId)
    {
        $this->sucursalId = $sucursalId;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                // Ignorar la primera fila (encabezados)
                if ($index === 0) continue;

                // Manejo del código del producto
                $codigo = $row[0] ?? null;
                if (empty($codigo)) {
                    $codigo = $this->generarNuevoCodigo();
                }

                // Obtener IDs de relaciones
                $categoriaId = $this->getCategoriaId($row[4]);
                //       dd($row);

                // dd($categoriaId);
                $unidadBaseId = $this->getUnidadBaseId($row[6]);
                $medidaPesoId = $this->getMedidaPesoId($row[14]);
                $distribuidorId = $this->getDistribuidorId($row[15]);
                $ubicacionId = $this->getUbicacionId($row[16]);
                $igvId = $this->getIgvId($row[17]);
                $empresaId = $this->getEmpresaId();

                // Insertar el producto
                $producto = Producto::create([
                    'codigo' => $codigo,
                    'descripcion' => $row[1] ?? '',
                    'abreviatura' => isset($row[1]) ? substr($row[2], 0, 1) : '',
                    'clasificacion' => $row[3] === 'BIEN' ? 'Bien' : ($row[3] === 'SERVICIO' ? 'Servicio' : $row[3]),
                    'tipo' => $row[3] === 'BIEN' ? 'Producto final' : substr($row[3], 0, 1),
                    'compuesto' => 'N',
                    'kardex' => $row[11] === 'NO' ? 'N' : 'S',
                    'autoproduccion' => 'N',
                    'categoria_id' => $categoriaId,
                    'unidadbase_id' => $unidadBaseId,
                    'peso' => $row[14] ?? 0,
                    'medidapeso_id' => $medidaPesoId,
                    'empresa_id' => $empresaId,
                    'comentario' => $row[34] ?? '',
                    'imagen' => '',
                    'complemento' => 'No permite',
                    'modocomplemento' => '',
                    'distribuidor_id' => $distribuidorId,
                ]);
                // SI kardex es 'S' (sí), crear registro en stockproducto
                if ($producto->kardex === 'S') {
                    \App\Models\Stockproducto::create([
                        'stockbase' => 0, 
                        'producto_id' => $producto->id,
                        'unidadbase_id' => $unidadBaseId,
                        'sucursal_id' => $this->sucursalId,
                    ]);
                }

                // Relación con sucursal
                RelProductosucursal::create([
                    'producto_id' => $producto->id,
                    'sucursal_id' => $this->sucursalId,
                    'stockminimo' => $row[29] ?? 0,
                    'stockmaximo' => $row[30] ?? 0,
                    'stockoptimo' => $row[31] ?? 0,
                    'minimovender' => $row[32] ?? 0,
                    'minimocomprar' => $row[33] ?? 0,
                    'ubicacion_id' => $ubicacionId,
                    'fila' => 0,
                    'columna' => 0,
                    'subalmacen_id' => null,
                    'igv_id' => $igvId,
                    'venta_unitaria' => 'N',
                    'proveedor_id' => null,
                    'situacion' => 'A'
                ]);

                // Insertar en `listaunidad` y `rel_listaunidadsucursal`
                $listaUnidad = Listaunidad::create([
                    'formula' => 1,
                    'producto_id' => $producto->id,
                    'unidad_id' => $unidadBaseId,
                    'unidadbase_id' => $unidadBaseId,
                    'empresa_id' => $empresaId,
                ]);

                RelListaunidadsucursal::create([
                    'preciocompra' => $row[8] ?? 0,
                    'preciomanoobra' => $row[18] ?? 0,
                    'precioventa' => $row[9] ?? 0,
                    'moneda' => $row[7] ?? 'PEN',
                    'listaunidad_id' => $listaUnidad->id,
                    'sucursal_id' => $this->sucursalId,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function generarNuevoCodigo()
    {
        $ultimoProducto = Producto::whereHas('relProductosucursal', function ($query) {
            $query->where('sucursal_id', $this->sucursalId);
        })->orderBy('codigo', 'desc')->first();

        if ($ultimoProducto && preg_match('/P(\d+)/', $ultimoProducto->codigo, $matches)) {
            $nuevoNumero = str_pad(((int) $matches[1]) + 1, 3, '0', STR_PAD_LEFT);
            return 'P' . $nuevoNumero;
        }

        return 'P001';
    }

    private function getCategoriaId($descripcion)
    {
        if (empty($descripcion)) {
            throw new \Exception("El campo de categoría está vacío en el archivo Excel.");
            // return 1; // ID de una categoría por defecto (si existe)
        }

        $categoria = \App\Models\Categorium::firstOrCreate(
            ['descripcion' => $descripcion],
            [
                'abreviatura' => substr($descripcion, 0, 3),
                'nivel' => 1,
                'orden' => 0,
                'padre' => 'N',
                'imagen' => '',
                'categoria_id' => null,
                'empresa_id' => $this->getEmpresaId(),
            ]
        );

        // Crear la relación con la sucursal si no existe
        \App\Models\RelCategoriasucursal::firstOrCreate([
            'categoria_id' => $categoria->id,
            'sucursal_id' => $this->sucursalId,
        ], [
            'tipocarta' => 'NINGUNA',
            'situacion' => 'A',
        ]);

        return $categoria->id;
    }



    private function getMarcaId($nombre)
    {
        return \App\Models\Marca::where('descripcion', $nombre)->value('id');
    }

    private function getUnidadBaseId($nombre)
    {
        return \App\Models\Unidad::where('descripcion', $nombre)->value('id');
    }
    private function getUnidadId($nombre)
    {
        return \App\Models\Unidad::where('descripcion', $nombre)->value('id');
    }
    private function getMedidaPesoId($nombre)
    {
        return \App\Models\Unidad::where('descripcion', $nombre)->value('id');
    }


    private function getDistribuidorId($nombre)
    {
        return \App\Models\Distribuidor::where('descripcion', $nombre)->value('id');
    }

    private function getUbicacionId($descripcion)
    {
        return \App\Models\Ubicacion::where('descripcion', $descripcion)->value('id');
    }

    private function getIgvId($valor)
    {
        return \App\Models\Igv::where('porcentaje_igv', $valor)->value('id');
    }

    private function getEmpresaId()
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $empresa_id = $sucursal->empresa_id;
        return $empresa_id;
    }
}
