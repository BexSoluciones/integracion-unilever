<?php

namespace App\Console\Commands;

use App\Modelo\Tabla;
use Illuminate\Console\Command;

class DeleteRepeatData extends Command {
	// php artisan integracion:delete-repeat-data 2 tbl_ws_union_ventas
	protected $signature = 'integracion:delete-repeat-data {idtable} {table}';
	protected $description = 'Consulta & verifica datos repetidos, elimina datos repetidos';

	public function __construct() {
		parent::__construct();
	}

	public function handle() {

		$table = $this->argument('table');
		$idtable = $this->argument('idtable');

		echo "\n =====> [ CONSULTADO DATOS: $table ] <===== \n";
		$consTabla = new Tabla;
		$consTabla->getTable();
		$consTabla->bind($table);
		$data = $consTabla->get();

		echo "=====> [ CONSULTADO CONDICIONES TABLA ] <===== \n";
		// $condicionTabla = new Tabla; $condicionTabla->getTable(); $condicionTabla->bind('tbl_consulta_condicion'); $condicion = $condicionTabla->where('id_consulta', $idtable)->first();
		$dataExplode = ['CODIGO_CLIENTE', 'CODIGO_PRODUCTO', 'consecutivo_factura']; // explode(',', $condicion['condicion']);

		foreach ($data as $value) {

			echo "=====> [ CONSULTADO DATO REPETIDO CONDICION ] <===== \n";

			if ($consTabla->where('codigo', '<>', $value['codigo'])->where(['CODIGO_CLIENTE' => $value['CODIGO_CLIENTE'], 'CODIGO_PRODUCTO' => $value['CODIGO_PRODUCTO'], 'consecutivo_factura' => $value['consecutivo_factura']])->exists()) {

				$dataRepeat = $consTabla->where('codigo', '<>', $value['codigo'])->where(['CODIGO_CLIENTE' => $value['CODIGO_CLIENTE'], 'CODIGO_PRODUCTO' => $value['CODIGO_PRODUCTO'], 'consecutivo_factura' => $value['consecutivo_factura']])->first();

				if ($consTabla->where('codigo', $dataRepeat['codigo'])->delete()) {
					echo "[ DELETE => CODIGO: " . $dataRepeat['codigo'] . " / CLIENTE: " . $value['CODIGO_CLIENTE'] . " / PRODUCTO: " . $value['CODIGO_PRODUCTO'] . " / CONSECUTIVO: " . $value['consecutivo_factura'] . " ] \n";
				}

			}

		}

		dd("PROCESO FINALIZADO");

	}

}