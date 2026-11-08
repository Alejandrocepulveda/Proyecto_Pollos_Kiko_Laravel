<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    /**
     * Ancho del papel de la nota, en milímetros.
     * 80 = impresora térmica de ticket (la más común), 58 = ticket angosto, 100 = ticket grande.
     */
    private const ANCHO_NOTA = 80;

    public function index(Request $request)
    {
        $usuario = $request->user();
        $esAdmin = $usuario->esAdmin();

        $filtros = $request->validate([
            'desde'   => ['nullable', 'date'],
            'hasta'   => ['nullable', 'date'],
            'cliente' => ['nullable', 'string', 'max:10'],
            'cajero'  => ['nullable', 'integer'],
        ]);

        $desde = $filtros['desde'] ?? null;
        $hasta = $filtros['hasta'] ?? null;

        // Si las fechas vienen al revés, se acomodan
        if ($desde && $hasta && $hasta < $desde) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $cliente = $filtros['cliente'] ?? '';
        $cajero  = $esAdmin ? ($filtros['cajero'] ?? null) : null;

        // El administrador ve todo; el cajero solo sus ventas
        $consulta = Venta::query()
            ->when(! $esAdmin, fn ($q) => $q->where('user_id', $usuario->id))
            ->when($cajero, fn ($q) => $q->where('user_id', $cajero))
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->when($cliente === 'general', fn ($q) => $q->whereNull('cliente_id'))
            ->when(ctype_digit($cliente), fn ($q) => $q->where('cliente_id', (int) $cliente));

        // Resumen de TODO lo filtrado (no solo de la página actual)
        $totalVendido = (float) (clone $consulta)->sum('total');
        $notas        = (clone $consulta)->count();
        $unidades     = (int) DetalleVenta::whereIn('venta_id', (clone $consulta)->select('id'))->sum('cantidad');
        $ticket       = $notas > 0 ? $totalVendido / $notas : 0;

        $ventas = $consulta
            ->with(['cliente', 'user', 'detalles.producto'])
            ->latest()
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $clientes   = Cliente::orderBy('nombre')->get(['id', 'nombre']);
        $cajeros    = $esAdmin ? User::orderBy('name')->get(['id', 'name', 'apellidos']) : collect();
        $hayFiltros = $desde || $hasta || $cliente !== '' || $cajero;

        return view('ventas.index', compact(
            'ventas', 'clientes', 'cajeros', 'esAdmin', 'desde', 'hasta', 'cliente', 'cajero',
            'hayFiltros', 'totalVendido', 'notas', 'unidades', 'ticket'
        ));
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

    public function nota(Request $request, Venta $venta)
    {
        // El administrador abre cualquier nota; el cajero solo las suyas
        abort_unless(
            $request->user()->esAdmin() || $venta->user_id === $request->user()->id,
            403,
            'Solo puedes ver las notas de tus propias ventas.'
        );

        $venta->load(['detalles.producto', 'cliente', 'user']);

        $ancho = self::ANCHO_NOTA;       // ancho del papel en mm
        $m     = 4;                      // margen a cada lado
        $u     = $ancho - 2 * $m;        // ancho útil para escribir
        $e     = 20;                     // ancho de las etiquetas (Folio:, Fecha:...)

        // La altura se calcula según los productos. Debe ser MAYOR que el ancho,
        // porque FPDF intercambia las medidas si el ancho supera al alto.
        $alto = max(88 + 10 * $venta->detalles->count(), $ancho + 10);

        $t = fn (string $s) => iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $s);

        $pdf = new \FPDF('P', 'mm', [$ancho, $alto]);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins($m, $m, $m);
        $pdf->AddPage();

        // Fondo crema y encabezado naranja
        $pdf->SetFillColor(255, 253, 224);
        $pdf->Rect(0, 0, $ancho, $alto, 'F');
        $pdf->SetFillColor(255, 82, 51);
        $pdf->Rect($m, $m, $u, 18, 'F');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetY($m + 3);
        $pdf->Cell($u, 6, 'POLLOS KIKO', 0, 1, 'C');
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell($u, 5, '*** NOTA DE VENTA ***', 0, 1, 'C');
        $pdf->SetY($m + 18 + 5);

        // Datos generales
        $datos = [
            'Folio:'    => '#' . $venta->id,
            'Fecha:'    => $venta->created_at->format('d/m/Y H:i'),
            'Cliente:'  => $venta->cliente->nombre ?? 'Cliente General',
            'Atendió:'  => $venta->user->name ?? '',
        ];
        foreach ($datos as $etiqueta => $valor) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell($e, 5, $t($etiqueta), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell($u - $e, 5, $t(mb_strimwidth($valor, 0, (int) floor(($u - $e) / 1.8), '...')), 0, 1);
        }
        $pdf->Ln(4);

        // Encabezado de la tabla
        $pdf->SetDrawColor(255, 82, 51);
        $pdf->SetLineWidth(0.5);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($u * 0.6, 6, 'Producto', 'B', 0, 'L');
        $pdf->Cell($u * 0.4, 6, 'Importe', 'B', 1, 'R');

        // Un producto por bloque: nombre arriba, "cantidad x precio" e importe abajo
        foreach ($venta->detalles as $d) {
            $subtotal = $d->precio_unitario * $d->cantidad;

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell($u, 5, $t(mb_strimwidth($d->producto->nombre, 0, (int) floor($u / 1.9), '...')), 0, 1, 'L');

            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell($u * 0.6, 5, $d->cantidad . ' x $' . number_format($d->precio_unitario, 2), 0, 0, 'L');
            $pdf->Cell($u * 0.4, 5, '$' . number_format($subtotal, 2), 0, 1, 'R');
        }

        $pdf->Line($m, $pdf->GetY() + 1, $ancho - $m, $pdf->GetY() + 1);
        $pdf->Ln(3);

        // Total
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell($u * 0.5, 8, 'TOTAL:', 0, 0, 'L');
        $pdf->SetTextColor(204, 0, 0);
        $pdf->Cell($u * 0.5, 8, '$' . number_format($venta->total, 2), 0, 1, 'R');

        $pdf->Ln(5);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($u, 5, $t('¡Gracias por su compra!'), 0, 1, 'C');

        return response($pdf->Output('S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Venta_' . $venta->id . '.pdf"',
        ]);
    }
}
