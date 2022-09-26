<?php

namespace App\Console\Commands;

use App\Modelo\Tabla;
use Illuminate\Console\Command;

class SumProducts extends Command {
	// php artisan integracion:delete-repeat-data 2 tbl_ws_union_ventas
	protected $signature = 'integracion:sum-productos {table}';
	protected $description = 'Consulta y suma productos de las mismas facturas';

	public function __construct() {
		parent::__construct();
	}

	public function handle() {

		$table = $this->argument('table');

		echo "\n =====> [ CONSULTADO DATOS: $table ] <===== \n";
		$consTabla = new Tabla;
		$consTabla->getTable();
		$consTabla->bind($table);
		$data = $consTabla->get();

		echo "=====> [ RECORRIENDO DATOS TABLA: $table ] <===== \n";
		foreach ($data as $value) {

            if($consTabla->where('consecutivo_factura','<>',$value['consecutivo_factura'])->where(['FECHA' => $value['FECHA'], 'CODIGO_CLIENTE' => $value['CODIGO_CLIENTE'], 'ZONA' => $value['ZONA'], 'CODIGO_PRODUCTO' => $value['CODIGO_PRODUCTO']])->exists()){

				echo "=====> [ ENCONTRADO COINCIDENCIA FACTURA: ".$value['consecutivo_factura']." ] <===== \n";

				$dataF = $consTabla->where('consecutivo_factura','<>',$value['consecutivo_factura'])->where(['FECHA' => $value['FECHA'], 'CODIGO_CLIENTE' => $value['CODIGO_CLIENTE'], 'ZONA' => $value['ZONA'], 'CODIGO_PRODUCTO' => $value['CODIGO_PRODUCTO']])->first();
            	$contPedido = $dataF['PEDIDO'] + $value['PEDIDO'];
            	$contDespachado = $dataF['DESPACHADO'] + $value['DESPACHADO'];
            	$contCambios = $dataF['CAMBIOS'] + $value['CAMBIOS'];
				if($consTabla->where('codigo',$value['codigo'])->update(['PEDIDO' => $contPedido,'DESPACHADO' => $contDespachado,'CAMBIOS' => $contCambios])){
					echo "=====> [ ACTUALIZANDO PRIMER REGISTRO DUPLICADO: ".$value['consecutivo_factura']." ] <===== \n";
					$consTabla->where('codigo',$dataF['codigo'])->delete();
				}else{
					echo "=====> [ NO SE PUDO ACTUALIZAR LA FACTURA: ".$value['consecutivo_factura']." ] <===== \n";
				}
            }else{
				echo "=";
            }
			// count($dataF) > 0 ? dd($dataF) : "";
			// if ($consTabla->where('consecutivo_factura','<>',$value['consecutivo_factura'])->where(['FECHA' => $value['FECHA'], 'CODIGO_CLIENTE' => $value['CODIGO_CLIENTE'], 'ZONA' => $value['ZONA'], 'CODIGO_PRODUCTO' => $value['CODIGO_PRODUCTO'],])->exists()) {
			// 	$dataRepeat = $consTabla->where('codigo', '<>', $value['codigo'])->where(['CODIGO_CLIENTE' => $value['CODIGO_CLIENTE'], 'CODIGO_PRODUCTO' => $value['CODIGO_PRODUCTO'], 'consecutivo_factura' => $value['consecutivo_factura']])->first();
			// 	if ($consTabla->where('codigo', $dataRepeat['codigo'])->delete()) {
			// 		echo "[ DELETE => CODIGO: " . $dataRepeat['codigo'] . " / CLIENTE: " . $value['CODIGO_CLIENTE'] . " / PRODUCTO: " . $value['CODIGO_PRODUCTO'] . " / CONSECUTIVO: " . $value['consecutivo_factura'] . " ] \n";
			// 	}
			// }
		}
		dd("PROCESO FINALIZADO");
	}

}
