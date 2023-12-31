<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Modelo\Tabla;
use App\Modelo\Funciones;
use App\Modelo\Plano;
use App\Modelo\Consulta;
use Illuminate\Support\Facades\Storage;

use ZipArchive;

class GenerarZip extends Command
{
    protected $signature = 'integracion:generar-zip';
    protected $description = 'Generar archivo centinela de planos generados';

    public function __construct(){ parent::__construct(); }

    public function handle(){
        
        $zip = new ZipArchive();
		$nombreZip = "CO-CBIA-DTR-0142_".date("Ymd");
        $nombreZipFTP = "CO-CBIA-DTR-0142_".date("Ymd").".zip";  
        $ruta = '/var/www/html/integracion-familia/public/plano';
        // $ruta = 'C:/laragon/www/integracion-familia/public/plano/';
        // $ruta_enviado = 'C:/laragon/www/integracion-familia/public/plano_enviado/';
        $ruta_enviado = '/var/www/html/integracion-familia/public/plano_enviado';
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

		if ($resultado) {
		    echo "=> Archivo creado \n";
		    if (Storage::disk('ftp')->put($nombreZipFTP, file_get_contents($nombreArchivoZip))) {
		    	echo "=> Subio el archivo FTP \n";
		    }else{
		    	echo "=> Hubo un problema al subir archivo FTP \n";
		    }
		} else {
		    echo "Error creando archivo\n";
		}

		echo "=> Se Movio archivos a enviados \n";

		if (is_dir($ruta)){
	        $gestor = opendir($ruta);
	        while (($archivo = readdir($gestor)) !== false)  {	                
                echo "ARCHIVO: ".$archivo." \n";
	            $ruta_completa = $ruta . "/" . $archivo;
	            $ruta_completa_nueva = $ruta_enviado . "/" . $archivo;
	            $isZip = explode('.', $archivo);
	            if ($archivo != "." && $archivo != ".." ) {
	                rename($ruta_completa, $ruta_completa_nueva);
	            }else{
                    echo "NO SE ENCONTRO ARCHIVOS \n";
                }
	        }
	        closedir($gestor);
	    }else{
            echo "NO SE ENCONTRO LA RUTA \n";
        }



    }  

}
