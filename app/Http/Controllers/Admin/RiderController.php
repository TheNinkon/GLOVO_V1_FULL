<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Http\Requests\Admin\RiderStoreRequest;
use App\Http\Requests\Admin\RiderUpdateRequest;
use Illuminate\Http\Request; // Asegúrate de importar Request
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class RiderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Rider::class);

        if ($request->ajax()) {
            $data = Rider::select(['id', 'full_name', 'dni', 'city', 'status', 'email']);
            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $editUrl = route('admin.riders.edit', $row->id);
                    $deleteUrl = route('admin.riders.destroy', $row->id);

                    // Botón Editar (ya corregido)
                    $editBtn = '<a href="' . $editUrl . '" class="btn btn-sm btn-icon item-edit"><i class="ti tabler-pencil"></i></a>';

                    // Botón Eliminar: Ahora es un <a> con data-attributes para manejarlo con JS
                    $deleteBtn = '<a href="javascript:;" class="btn btn-sm btn-icon delete-rider-btn" data-url="' . $deleteUrl . '"><i class="ti tabler-trash"></i></a>';

                    return $editBtn . $deleteBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('content.admin.riders.index');
    }

    public function create()
    {
        $this->authorize('create', Rider::class);
        return view('content.admin.riders.create');
    }

    public function store(RiderStoreRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        Rider::create($data);
        return redirect()->route('admin.riders.index')->with('success', 'Rider creado con éxito.');
    }

    public function edit(Rider $rider)
    {
        $this->authorize('update', $rider);
        return view('content.admin.riders.edit', compact('rider'));
    }

    public function update(RiderUpdateRequest $request, Rider $rider)
    {
        $data = $request->validated();
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $rider->update($data);
        return redirect()->route('admin.riders.index')->with('success', 'Rider actualizado con éxito.');
    }

    // MÉTODO DESTROY ACTUALIZADO
    public function destroy(Request $request, Rider $rider)
    {
        $this->authorize('delete', $rider);
        $rider->delete();

        // Si la petición es AJAX, devolvemos JSON
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Rider eliminado con éxito.']);
        }

        // Si no, redirigimos (comportamiento por defecto)
        return redirect()->route('admin.riders.index')->with('success', 'Rider eliminado con éxito.');
    }
}
