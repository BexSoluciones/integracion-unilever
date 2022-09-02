<?php

namespace App\Console\Commands;

use App\Modelo\Tabla;
use Illuminate\Console\Command;

class DeleteRepeatData extends Command {
	// php integracion:delete-repeat-data 2 tbl_ws_union_ventas
	protected $signature = 'integracion:delete-repeat-data {idtable} {table}';
	protected $description = 'Consulta & verifica datos repetidos, elimina datos repetidos';

	public function __construct() {
		parent::__construct();
	}

	public function handle() {

		$table = $this->argument('table');
		$idtable = $this->argument('idtable');

		echo "=====> [ CONSULTADO DATOS: $table ] <===== \n";
		$consTabla = new Tabla;
		$consTabla->getTable();
		$consTabla->bind($table);
		$data = $consTabla->all();

		echo "=====> [ CONSULTADO CONDICIONES TABLA ] <===== \n";
		$condicionTabla = new Tabla;
		$condicionTabla->getTable();
		$condicionTabla->bind('tbl_consulta_condicion');
		$condicion = $condicionTabla->where('id_consulta', $idtable)->first();
		$dataExplode = explode(',', $condicion['condicion']);
		$counExplode = count($dataExplode);

		foreach ($data as $value) {

			echo "=====> [ RECORRIENDO DATOS ] <===== \n";
			$repeat = 0;
			$repeatRay = array();
			for ($i = 0; $i <= $counExplode; $i++) {
				echo "=====> [ CONSULTADO DATO REPETIDO CONDICION: " . $dataExplode[$i] . " ] <===== \n";
				if ($consTabla->where('codigo', '<>', $value['codigo'])->where($dataExplode[$i], $value[$dataExplode[$i]])->exists()) {
					echo "=====> [ EXISTE DATO REPETIDO " . $dataExplode[$i] . " ] <===== \n";
					$repeat++;
					$dataRepeat = $consTabla->where('codigo', '<>', $value['codigo'])->where($dataExplode[$i], $value[$dataExplode[$i]])->first();
					array_push($repeatRay, $dataRepeat['codigo']);
				}
			}

			if ($repeat == $counExplode) {
				echo "=====> [ ELIMINANDO DATOS REPETIDOS ] <===== \n";
				foreach ($repeatRay as $key => $value) {
					echo "=====> [ CODIGO REPETIDO $value ] <===== \n";
					$consTabla->where('codigo', $value)->delete();}
			}

		}

		dd("PROCESO FINALIZADO");

	}

}