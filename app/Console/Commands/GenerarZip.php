<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Modelo\Tabla;
use App\Modelo\Funciones;
use App\Modelo\Plano;
use App\Modelo\Consulta;

use ZipArchive;

class GenerarZip extends Command
{
    protected $signature = 'integracion:generar-zip';
    protected $description = 'Generar archivo centinela de planos generados';

    public function __construct(){ parent::__construct(); }

    public function handle(){
        
        $zip = new ZipArchive();
        $nombreZip = date("Ymd")."_"."D000086"; 
        $ruta = 'public/plano/';
        $ruta_enviado = 'public/plano_enviado/';
        $nombreArchivoZip = $ruta.$nombreZip.".zip";

        if (!$zip->open($nombreArchivoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
		    exit("Error abriendo ZIP en $nombreArchivoZip");
		}

		if (is_dir($ruta)){
	        $gestor = opendir($ruta);
	        while (($archivo = readdir($gestor)) !== false)  {	                
                echo "ARCHIVO: ".$archivo." \n";
	            $ruta_completa = $ruta . "/" . $archivo;
	            $ruta_completa_nueva = $ruta_enviado . "/" . $archivo;
	            if ($archivo != "." && $archivo != "..") {
	                $nombre = basename($ruta_completa);
					$zip->addFile($ruta_completa, $nombre);
	                // rename($ruta_completa, $ruta_completa_nueva);
	            }else{
                    echo "NO SE ENCONTRO ARCHIVOS \n";
                }
	        }
	        closedir($gestor);
	    }else{
            echo "NO SE ENCONTRO LA RUTA \n";
        }
		// No olvides cerrar el archivo
		$resultado = $zip->close();

		if (is_dir($ruta)){
	        $gestor = opendir($ruta);
	        while (($archivo = readdir($gestor)) !== false)  {	                
                echo "ARCHIVO: ".$archivo." \n";
	            $ruta_completa = $ruta . "/" . $archivo;
	            $ruta_completa_nueva = $ruta_enviado . "/" . $archivo;
	            $isZip = explode('.', $archivo);
	            if ($archivo != "." && $archivo != ".." && $isZip[1] != 'zip') {
	                rename($ruta_completa, $ruta_completa_nueva);
	            }else{
                    echo "NO SE ENCONTRO ARCHIVOS \n";
                }
	        }
	        closedir($gestor);
	    }else{
            echo "NO SE ENCONTRO LA RUTA \n";
        }

		if ($resultado) {
		    echo "Archivo creado\n";
		} else {
		    echo "Error creando archivo\n";
		}

    }  

}
