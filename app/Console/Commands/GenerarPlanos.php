<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Modelo\Consulta;
use App\Modelo\Plano;
use App\Modelo\Funciones;
use App\Modelo\Tabla;
use App\Modelo\Formato;
use App\Modelo\PlanoFuncion;
use App\Modelo\CampoQuemado;

class GenerarPlanos extends Command
{
    protected $signature = 'integracion:generar-planos';
    protected $description = 'Generar planos de tablas registradas';

    public function __construct(){
        parent::__construct();
    }

    public function handle(){
        $listaConsulta = Consulta::where('estado',1)->get();
        foreach ($listaConsulta as $value) {
            
            $consPlano = Plano::where('codigo',$value->id_plano)->first();
            $consPlanoFuncion = PlanoFuncion::where('id_consulta',$value->codigo)->get();
            $consCampoQuemado = CampoQuemado::where('id_consulta',$value->codigo)->get();
            $consFormato = Formato::where('id_consulta',$value->codigo)->first();
            $consTabla = new Tabla; $consTabla->getTable(); $consTabla->bind($value->tabla_destino); 

            if ($value->group_by == '') {
                // $resCons = $consTabla->where('planoRegistro',0)->orderBy($value->orderBy,$value->orderType)->get();
                if (trim($value->tabla_destino) === "tbl_ws_union_ventas") {
                    $resCons = $consTabla->where('planoRegistro',0)->groupBy('FECHA','CODIGO_CLIENTE','ZONA','CODIGO_PRODUCTO')->orderBy($value->orderBy,$value->orderType)->get();
                    // dd($value->tabla_destino);
                    echo "=> #1 \n";
                }else{
                    $resCons = $consTabla->where('planoRegistro',0)->orderBy($value->orderBy,$value->orderType)->get();
                    echo "=> #2 \n";
                }
            }else{
                if (trim($value->tabla_destino) === "tbl_ws_union_ventas") {
                    $resCons = $consTabla->where('planoRegistro',0)->groupBy('FECHA','CODIGO_CLIENTE','ZONA','CODIGO_PRODUCTO')->orderBy($value->orderBy,$value->orderType)->get();
                    echo "=> #3 \n";
                }else{
                    $resCons = $consTabla->where('planoRegistro',0)->groupBy($value->group_by)->orderBy($value->orderBy,$value->orderType)->get();
                    echo "=> #4 \n";
                }
            }

            // dd($resCons);

            $dataPlan = null; $name_us = null; $sumR = 0;
            foreach ($resCons as $keya => $valueA) {                

                // unset($valueA['codigo']);
                // unset($valueA['consecutivo_factura']);
                // unset($valueA['planoRegistro']);
                // unset($valueA['created_at']);
                // unset($valueA['updated_at']);
                
                echo "===> DATA: $sumR  \n";

                $suma = 0; $array = explode(",", $valueA); $totalExpl = count($array) - 2;
                if ($consPlano['display_codigo'] == 0) { $sum = 1; }else{ $sum = 2; }    


                if (count($array) > 0) {

                    foreach ($array as $keyb => $valueB) {                  

                        // dd($keyb);
                        
                        $dataLost = (count($array) - 3);

                        if ($sum != 1 && $sum < $dataLost) {

                            $valueB = Funciones::caracterEspecial($valueB);
                            $valueB = Funciones::caracterEspecialSimbol($valueB);
                            $campoDpl = true; $pos = strpos($valueB, ':'); $pos++;
                            $valueB = substr($valueB, $pos); $valueB = Funciones::ReplaceText($valueB);
                
                            $tipo = explode(",", $consFormato['tipo']); 
                            $longitud = explode(",", $consFormato['longitud']); 
                            
                            // echo "VALUE FOREACH:".$valueB." | ".$suma." | "."\n";
                            // echo "STATE A: $campoDpl <br>";

                            if ($valueB == 'NO') { $valueB = ''; }

                            // echo "TOTAL: ".(count($tipo) - 2)." / SUMA: $sum / VAL: $valueB \n";
                            // echo "SUMA: $sum \n";

                            if (count($tipo) == $sum || count($tipo) < $sum || ($sum + 1) == $dataLost) { 
                                // echo "ESPACIADO NULL \n"; 
                                $separadorPlan = ""; 
                            }else{ 
                                // echo "SEPARADO ; \n"; 
                                $separadorPlan = $consPlano['separador']; 
                            }                           

                            // CAMPOS CON FUNCIONES ESPECIFICAS
                            foreach ($consPlanoFuncion as $planoFuncion) {
                                if ($planoFuncion->posicion == $suma) {                                
                                    if($planoFuncion->tipo == 'name_us'){
                                        $name_us = $valueB;
                                    }elseif($planoFuncion->tipo == 'buscar_codigo'){ 
                                        $campoDpl = false; 
                                        $tablaBuscar = Consulta::where('codigo',$planoFuncion->consulta)->first();
                                        $buscarTabla = new Tabla; $buscarTabla->getTable(); $buscarTabla->bind($tablaBuscar['tabla_destino']); $resBusc = $buscarTabla->where($planoFuncion->nombre,$valueB)->first();     
                                        if ($planoFuncion->tipo == 'texto') {
                                            $dataResplan = substr($resBusc['codigo'], 0, $planoFuncion->longitud);
                                            $dataPlan .= " ".$consPlano['entre_columna'].str_pad($dataResplan, $planoFuncion->longitud).$consPlano['entre_columna'].$separadorPlan;
                                        }else{ $dataPlan .= $resBusc['codigo'].$separadorPlan; }
                                    }else{
                                        $dataPlan .= Funciones::condicionPlano($planoFuncion,$valueB,$name_us,$consPlano);
                                        if ($dataPlan != false) { $campoDpl = false; }
                                    }
                                }
                            }

                            // echo "STATE B: $campoDpl <br>";

                            // CAMPOS QUEMADOS
                            if ($campoDpl == true) {
                                foreach ($consCampoQuemado as $campoQuemado) {
                                    if ($campoQuemado->posicion == $suma) {
                                        $campoDpl = false; // echo "$campoQuemado";
                                        if ($campoQuemado->tipo == 'texto') {
                                            $dataResplan = substr($campoQuemado->valor, 0, $campoQuemado->longitud);
                                            $dataPlan .= " ".$consPlano['entre_columna'].str_pad($dataResplan, $campoQuemado->longitud).$consPlano['entre_columna'].$separadorPlan;
                                        }else{ $dataPlan .= $campoQuemado->valor.$separadorPlan; }
                                    }
                                }
                            }

                            // echo "STATE C: $campoDpl <br>";

                            if ($campoDpl == true) {
                                // CAMPOS CONSULTA TABLA
                                if ($suma >= count($tipo)) {
                                    // echo "ALERTA: => LA CANTIDAD DE CAMPOS EN LA POSICION `$sum` DE LA TABLA `tbl_formato` SOBREPASA, NO CONCUERDA CON LA CANTIDAD DE CAMPOS QUE CONTIENE LA TABLA: `$value->tabla_destino` ES `".count($tipo)."` <br>";
                                }else{
                                    $tipoR = Funciones::ReplaceText($tipo[$suma]);  
                                    $longitudR = Funciones::ReplaceText($longitud[$suma]);
                                    

                                    if ($tipoR == 'texto') {
                                        if ($keyb == "PEDIDO") {
                                            $valueB = $consTabla->where('FECHA', $keyb['FECHA'])->where('CODIGO_CLIENTE', $keyb['CODIGO_CLIENTE'])->where('ZONA', $keyb['ZONA'])->where('CODIGO_PRODUCTO', $keyb['CODIGO_PRODUCTO'])->sum('PEDIDO');
                                        }else if ($keyb == "DESPACHADO") {
                                            $valueB = $consTabla->where('FECHA', $keyb['FECHA'])->where('CODIGO_CLIENTE', $keyb['CODIGO_CLIENTE'])->where('ZONA', $keyb['ZONA'])->where('CODIGO_PRODUCTO', $keyb['CODIGO_PRODUCTO'])->sum('DESPACHADO');
                                        }else if ($keyb == "VALOR") {
                                            $valueB = $consTabla->where('FECHA', $keyb['FECHA'])->where('CODIGO_CLIENTE', $keyb['CODIGO_CLIENTE'])->where('ZONA', $keyb['ZONA'])->where('CODIGO_PRODUCTO', $keyb['CODIGO_PRODUCTO'])->sum('VALOR');
                                        }
                                        $dataResplan = substr($valueB, 0, $longitudR);
                                        $dataPlan .= "".$consPlano['entre_columna'].str_pad($dataResplan, 0).$consPlano['entre_columna'].$separadorPlan;
                                    }else{ 
                                        if ($keyb == "PEDIDO") {
                                            $valueB = $consTabla->where('FECHA', $keyb['FECHA'])->where('CODIGO_CLIENTE', $keyb['CODIGO_CLIENTE'])->where('ZONA', $keyb['ZONA'])->where('CODIGO_PRODUCTO', $keyb['CODIGO_PRODUCTO'])->sum('PEDIDO');
                                        }else if ($keyb == "DESPACHADO") {
                                            $valueB = $consTabla->where('FECHA', $keyb['FECHA'])->where('CODIGO_CLIENTE', $keyb['CODIGO_CLIENTE'])->where('ZONA', $keyb['ZONA'])->where('CODIGO_PRODUCTO', $keyb['CODIGO_PRODUCTO'])->sum('DESPACHADO');
                                        }else if ($keyb == "VALOR") {
                                            $valueB = $consTabla->where('FECHA', $keyb['FECHA'])->where('CODIGO_CLIENTE', $keyb['CODIGO_CLIENTE'])->where('ZONA', $keyb['ZONA'])->where('CODIGO_PRODUCTO', $keyb['CODIGO_PRODUCTO'])->sum('VALOR');
                                        }
                                        $dataPlan .= $valueB.$separadorPlan; 
                                    }

                                }   
                            }
                                            
                            
                        } $sum++; $suma++;
                    }
                    // echo "<br>";
                    // echo "PLANO ANT FUNCTION: $dataPlan \n";
                    // echo "\n";

                    if ($consPlano['salto_linea'] == 1) { $dataPlan .= "\n\r"; }
                }   $sumR++;

            }

            if ($dataPlan != null) {
                $nombreFile = Funciones::NombreArchivo($consPlano); 
                //$rutaFile = "public/plano/".$nombreFile; 
                $rutaFile = $consPlano['ruta'].$nombreFile; $dataPlan = str_replace('\/',"/", $dataPlan);
                Funciones::crearTXT($dataPlan,$rutaFile,$nombreFile,$consPlano['ftp'],$consPlano['sftp']);
                // $consTabla->where('planoRegistro',0)->update(['planoRegistro' => 1]);        
            }        

        }

        $nombreFile = Funciones::NombreArchivo($consPlano); 

    }
}