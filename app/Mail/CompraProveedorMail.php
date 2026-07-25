<?php

namespace App\Mail;

use App\Models\Compra;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompraProveedorMail extends Mailable
{
    use Queueable, SerializesModels;

    public $compra;
    public $carrito_temporal;

    /**
     * Create a new message instance.
     */
    public function __construct(Compra $compra, $carrito_temporal = [])
    {
        $this->compra = $compra;
        $this->carrito_temporal = $carrito_temporal;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Determinar qué productos mostrar
        $productos = [];

        if ($this->compra->detalles && $this->compra->detalles->isNotEmpty()) {
            // Si la compra ya tiene detalles guardados en DB
            $productos = $this->compra->detalles;
        } elseif (!empty($this->carrito_temporal)) {
            // Si es un carrito temporal (antes de confirmar)
            $productos = $this->carrito_temporal;
        }

        return $this->view('emails.compra-proveedor')
                    ->subject('Solicitud de compra #' . $this->compra->id)
                    ->with([
                        'compra' => $this->compra,
                        'productos' => $productos
                    ]);
    }
}
