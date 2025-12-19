<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LeitoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = DB::table('leito');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('descricao', 'like', "%{$search}%");
        }

        $leitos = $query->orderBy('descricao')->paginate(10);

        return Inertia::render('leitos/index', [
            'leitos' => $leitos,
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
        $leito = DB::table('leito')
            ->leftJoin('setor', 'leito.cod_setor', '=', 'setor.cod')
            ->where('leito.cod', $id)
            ->select('leito.*', 'setor.descricao as setor_descricao')
            ->first();

        if (!$leito) {
            abort(404);
        }

        return Inertia::render('leitos/show', [
            'leito' => $leito,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $setores = DB::table('setor')->orderBy('descricao')->get();

        return Inertia::render('leitos/create', [
            'setores' => $setores,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'cod_setor' => ['nullable', 'exists:setor,cod'],
        ]);

        DB::table('leito')->insert([
            'descricao' => $validated['descricao'],
            'cod_setor' => $validated['cod_setor'] ?? null,
        ]);

        return redirect()->route('leitos.index')
            ->with('success', 'Leito criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {
        $leito = DB::table('leito')->where('cod', $id)->first();

        if (!$leito) {
            abort(404);
        }

        $setores = DB::table('setor')->orderBy('descricao')->get();

        return Inertia::render('leitos/edit', [
            'leito' => $leito,
            'setores' => $setores,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'cod_setor' => ['nullable', 'exists:setor,cod'],
        ]);

        DB::table('leito')
            ->where('cod', $id)
            ->update([
                'descricao' => $validated['descricao'],
                'cod_setor' => $validated['cod_setor'] ?? null,
            ]);

        return redirect()->route('leitos.index')
            ->with('success', 'Leito atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        DB::table('leito')->where('cod', $id)->delete();

        return redirect()->route('leitos.index')
            ->with('success', 'Leito excluído com sucesso!');
    }
}

