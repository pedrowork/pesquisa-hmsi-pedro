<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TipoConvenioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = DB::table('tipoconvenio');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('tipo_descricao', 'like', "%{$search}%");
        }

        $tiposConvenio = $query->orderBy('tipo_descricao')->paginate(10);

        return Inertia::render('tipos-convenio/index', [
            'tiposConvenio' => $tiposConvenio,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('tipos-convenio/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipo_descricao' => ['required', 'string', 'max:100'],
        ]);

        DB::table('tipoconvenio')->insert([
            'tipo_descricao' => $validated['tipo_descricao'],
        ]);

        return redirect()->route('tipos-convenio.index')
            ->with('success', 'Tipo de Convênio criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {
        $tipoConvenio = DB::table('tipoconvenio')->where('cod', $id)->first();

        if (!$tipoConvenio) {
            abort(404);
        }

        return Inertia::render('tipos-convenio/edit', [
            'tipoConvenio' => $tipoConvenio,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'tipo_descricao' => ['required', 'string', 'max:100'],
        ]);

        DB::table('tipoconvenio')
            ->where('cod', $id)
            ->update([
                'tipo_descricao' => $validated['tipo_descricao'],
            ]);

        return redirect()->route('tipos-convenio.index')
            ->with('success', 'Tipo de Convênio atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        DB::table('tipoconvenio')->where('cod', $id)->delete();

        return redirect()->route('tipos-convenio.index')
            ->with('success', 'Tipo de Convênio excluído com sucesso!');
    }
}

