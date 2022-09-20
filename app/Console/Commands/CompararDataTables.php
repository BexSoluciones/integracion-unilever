<?php

namespace App\Console\Commands;

use App\Modelo\Tabla;
use Illuminate\Console\Command;

class CompararDataTables extends Command {
	// php artisan integracion:delete-repeat-data 2 tbl_ws_union_ventas
	protected $signature = 'integracion:comparar-tabla {campo} {tabla1} {campoLike1} {textLike1} {tabla2} {campoLike2} {textLike2}';
	protected $description = 'Consulta & verifica datos entre 2 tablas';

	public function __construct() {
		parent::__construct();
	}

	public function handle() {

		$campo = $this->argument('campo');
		$campoLike1 = $this->argument('campoLike1');
		$campoLike2 = $this->argument('campoLike2');
		$textLike1 = $this->argument('textLike1');
		$textLike2 = $this->argument('textLike2');

		$tabla1 = $this->argument('tabla1');
		echo "\n =====> [ CONSULTADO DATOS TABLA 1: $tabla1 ] <===== \n";
		$consTbl1 = new Tabla;
		$consTbl1->getTable();
		$consTbl1->bind($tabla1);
		$tbl1 = $consTbl1->get();
		$tbl1 = $campoLike1 != 'null' ? $consTbl1->where($campoLike1, 'LIKE', $textLike1 . "%")->get() : $consTbl1->get();
		$totalTbl1 = count($tbl1);

		$tabla2 = $this->argument('tabla2');
		echo "\n =====> [ CONSULTADO DATOS TABLA 2: $tabla2 ] <===== \n";
		$consTbl2 = new Tabla;
		$consTbl2->getTable();
		$consTbl2->bind($tabla2);
		// $tbl2 = $campoLike2 != 'null' ? $consTbl2->where($campoLike2, 'LIKE', $textLike2 . "%")->get() : $consTbl2->get();

		echo "\n =====> [ COMPARANDO CAMPO: $campo / DATOS TABLA 1: $tabla1 / RESPECTO TABLA 2: $tabla2 / TOT: " . $totalTbl1 . " ] <===== \n";
		$sum = 0;
		foreach ($tbl1 as $campoTbl1) {
			$porcentaje = ($sum * 100) / $totalTbl1;
			echo " => [ " . $porcentaje . " % ] \n";

			$dataEncontrado = false;
			$cons = substr($campoTbl1[$campo], 3);
			if ($consTbl2->where('CODIGO_PRODUCTO', $cons)->exists()) {
				$dataEncontrado = true;
			}

			// echo $dataEncontrado === true ? "=> DATA: " . $campoTbl1[$campo] . " TRUE \n" : "=> DATA: " . $campoTbl1[$campo] . " FALSE \n";
			$sum++;
			echo $dataEncontrado === true ? "" : $cons . " => FALSE \n";
		}

		dd("PROCESO FINALIZADO");

	}

}