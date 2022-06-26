<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Modelo\Tabla;
use App\Modelo\Funciones;
use App\Modelo\Plano;
use App\Modelo\Consulta;

class GenerarCentinela extends Command
{
    protected $signature = 'integracion:ejecutar-centinela';
    protected $description = 'Generar archivo centinela de planos generados';

    public function __construct(){ parent::__construct(); }

    public function handle(){
        
        $consPlanoCentinela = Plano::where('nombre',"LIKE",'%centinela%')->first();
        $listaConsulta = Consulta::where('estado',1)->get(); $dataPlano = null;

        foreach ($listaConsulta as $value) {
            $consPlano = Plano::where('codigo',$value->id_plano)->first();
            $dataPlano .= "97".$consPlano['separador'];
            $dataPlano .= $consPlano['seccion_a'].$consPlano['seccion_b'].$consPlano['extension'];
            $dataPlano .= $consPlano['seccion_a'].$consPlano['seccion_b'].$consPlano['extension'];

            $rutaPlano = $consPlano['ruta'].$consPlano['seccion_a'].$consPlano['seccion_b'].$consPlano['extension'];
            $totaLineas = Funciones::contarLineasArchivo($rutaPlano);

            $dataPlano .= $totaLineas.$consPlano['extension']; 
            $dataPlano .= date("Y-m-d")."\n";

        }

        if ($dataPlano != null) {
            $nombreFile = Funciones::NombreArchivo($consPlanoCentinela); 
            $rutaFile = $consPlanoCentinela['ruta'].$nombreFile; $dataPlano = str_replace('\/',"/", $dataPlano);
            Funciones::crearTXT($dataPlano,$rutaFile,$nombreFile,$consPlanoCentinela['ftp'],$consPlanoCentinela['sftp']);
        } 

    }  

}