<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['cliente', 'user'])->latest()->limit(100)->get();
        return view('ventas.index', compact('ventas'));
    }

    public function create()
    {
        $productos = Producto::where('stock', '>', 0)->orderBy('nombre')->get()
            ->map(fn ($p) => [
                'id'     => $p->id,
                'nombre' => $p->nombre,
                'precio' => (float) $p->precio_venta,
                'stock'  => $p->stock,
            ])
            ->values();

        $clientes = Cliente::orderBy('nombre')->get(['id', 'nombre']);

        return view('ventas.create', compact('productos', 'clientes'));
    }

    public function store(Request $request, VentaService $servicio)
    {
        $datos = $request->validate([
            'cliente_id'          => ['nullable', 'exists:clientes,id'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'exists:productos,id'],
            'items.*.cantidad'    => ['required', 'integer', 'min:1'],
        ]);

        $venta = $servicio->registrar(
            $request->user()->id,
            $datos['cliente_id'] ?? null,
            $datos['items']
        );

        return redirect()->route('ventas.create')
            ->with('exito', "¡Venta #{$venta->id} registrada con éxito!")
            ->with('nota', route('ventas.nota', $venta));
    }

    public function nota(Venta $venta)
    {
        $venta->load(['detalles.producto', 'cliente']);

        $alto = 85 + 8 * $venta->detalles->count();
        $t = fn (string $s) => iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $s);

        $pdf = new \FPDF('P', 'mm', [100, $alto]);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        $pdf->SetFillColor(255, 253, 224);
        $pdf->Rect(0, 0, 100, $alto, 'F');
        $pdf->SetFillColor(255, 82, 51);
        $pdf->Rect(5, 5, 90, 18, 'F');

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetY(8);
        $pdf->Cell(90, 6, 'POLLOS KIKO', 0, 1, 'C');
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(90, 5, '*** NOTA DE VENTA ***', 0, 1, 'C');
        $pdf->Ln(5);

        $datos = [
            'Folio:'        => '#' . $venta->id,
            'Fecha:'        => $venta->created_at->format('d/m/Y H:i'),
            'Tipo Cliente:' => $venta->cliente ? 'Cliente Frecuente' : 'Cliente General',
            'Nombre:'       => $venta->cliente->nombre ?? 'Cliente General',
        ];
        foreach ($datos as $etiqueta => $valor) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(22, 5, $t($etiqueta), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(68, 5, $t($valor), 0, 1);
        }
        $pdf->Ln(4);

        $pdf->SetDrawColor(255, 82, 51);
        $pdf->SetLineWidth(0.5);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(40, 6, 'Producto', 'B', 0, 'L');
        $pdf->Cell(10, 6, 'Cant.', 'B', 0, 'C');
        $pdf->Cell(18, 6, 'Precio', 'B', 0, 'R');
        $pdf->Cell(22, 6, 'Total', 'B', 1, 'R');

        $pdf->SetFont('Arial', '', 9);
        foreach ($venta->detalles as $d) {
            $subtotal = $d->precio_unitario * $d->cantidad;
            $pdf->Cell(40, 8, $t(mb_strimwidth($d->producto->nombre, 0, 22, '...')), 0, 0, 'L');
            $pdf->Cell(10, 8, $d->cantidad, 0, 0, 'C');
            $pdf->Cell(18, 8, '$' . number_format($d->precio_unitario, 2), 0, 0, 'R');
            $pdf->Cell(22, 8, '$' . number_format($subtotal, 2), 0, 1, 'R');
        }
        $pdf->Line(5, $pdf->GetY(), 95, $pdf->GetY());
        $pdf->Ln(3);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(68, 8, 'TOTAL A PAGAR:', 0, 0, 'R');
        $pdf->SetTextColor(204, 0, 0);
        $pdf->Cell(22, 8, '$' . number_format($venta->total, 2), 0, 1, 'R');

        $pdf->Ln(6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(90, 5, $t('¡Gracias por su compra!'), 0, 1, 'C');

        return response($pdf->Output('S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Venta_' . $venta->id . '.pdf"',
        ]);
    }
}