<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    public function show($path)
    {
        // Verifica se o arquivo existe no disco 'media'
        if (!Storage::disk('media')->exists($path)) {
            abort(404);
        }

        // Pega o arquivo do disco
        $file = Storage::disk('media')->get($path);

        // Determina o tipo MIME do arquivo
        $type = Storage::disk('media')->mimeType($path);

        // Retorna a resposta com o arquivo e o tipo MIME correto
        return response($file, 200)->header('Content-Type', $type);
    }
}
