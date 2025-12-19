<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SetorPesquisaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = DB::table('setor_pesquis');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('descricao', 'like', "%{$search}%");
        }

        $setoresPesquisa = $query->orderBy('descricao')->paginate(10);

        return Inertia::render('setores-pesquisa/index', [
            'setoresPesquisa' => $setoresPesquisa,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): Response
    {
        $setorPesquisa = DB::table('setor_pesquis')->where('cod', $id)->first();

        if (!$setorPesquisa) {
            abort(404);
        }

        return Inertia::render('setores-pesquisa/show', [
            'setorPesquisa' => $setorPesquisa,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('setores-pesquisa/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:128'],
        ]);

        DB::table('setor_pesquis')->insert([
            'descricao' => $validated['descricao'],
        ]);

        return redirect()->route('setores-pesquisa.index')
            ->with('success', 'Setor de Pesquisa criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {
        $setorPesquisa = DB::table('setor_pesquis')
            ->where('cod', $id)
            ->first();

        if (!$setorPesquisa) {
            abort(404);
        }

        return Inertia::render('setores-pesquisa/edit', [
            'setorPesquisa' => $setorPesquisa,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:128'],
        ]);

        DB::table('setor_pesquis')
            ->where('cod', $id)
            ->update([
                'descricao' => $validated['descricao'],
            ]);

        return redirect()->route('setores-pesquisa.index')
            ->with('success', 'Setor de Pesquisa atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        DB::table('setor_pesquis')->where('cod', $id)->delete();

        return redirect()->route('setores-pesquisa.index')
            ->with('success', 'Setor de Pesquisa excluído com sucesso!');
    }
}

