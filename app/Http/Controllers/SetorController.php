<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SetorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = DB::table('setor');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('descricao', 'like', "%{$search}%");
        }

        $setores = $query->orderBy('descricao')->paginate(10);

        return Inertia::render('app/setores/index', [
            'setores' => $setores,
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
        $setor = DB::table('setor')->where('cod', $id)->first();

        if (!$setor) {
            abort(404);
        }

        return Inertia::render('app/setores/show', [
            'setor' => $setor,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('app/setores/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
        ]);

        DB::table('setor')->insert([
            'descricao' => $validated['descricao'],
        ]);

        return redirect()->route('setores.index')
            ->with('success', 'Setor criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {
        $setor = DB::table('setor')->where('cod', $id)->first();

        if (!$setor) {
            abort(404);
        }

        return Inertia::render('app/setores/edit', [
            'setor' => $setor,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
        ]);

        DB::table('setor')
            ->where('cod', $id)
            ->update([
                'descricao' => $validated['descricao'],
            ]);

        return redirect()->route('setores.index')
            ->with('success', 'Setor atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        DB::table('setor')->where('cod', $id)->delete();

        return redirect()->route('setores.index')
            ->with('success', 'Setor excluído com sucesso!');
    }
}

