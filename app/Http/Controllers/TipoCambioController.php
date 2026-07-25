<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\TipoCambio;

class TipoCambioController extends Controller
{


    public function index()
    {
        $tiposCambio = TipoCambio::all();

        return view('admin.tipo_cambio.index', compact('tiposCambio'));
    }
}
