<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SatisfacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = DB::table('satisfacao');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('descricao', 'like', "%{$search}%");
        }

        $satisfacoes = $query->orderBy('descricao')->paginate(10);

        return Inertia::render('app/satisfacao/index', [
            'satisfacoes' => $satisfacoes,
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
        $satisfacao = DB::table('satisfacao')->where('cod', $id)->first();

        if (!$satisfacao) {
            abort(404);
        }

        return Inertia::render('app/satisfacao/show', [
            'satisfacao' => $satisfacao,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('app/satisfacao/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'cod_tipo_pergunta' => ['nullable', 'integer'],
        ]);

        DB::table('satisfacao')->insert([
            'descricao' => $validated['descricao'],
            'cod_tipo_pergunta' => $validated['cod_tipo_pergunta'] ?? null,
        ]);

        return redirect()->route('satisfacao.index')
            ->with('success', 'Satisfação criada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {
        $satisfacao = DB::table('satisfacao')->where('cod', $id)->first();

        if (!$satisfacao) {
            abort(404);
        }

        return Inertia::render('app/satisfacao/edit', [
            'satisfacao' => $satisfacao,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'cod_tipo_pergunta' => ['nullable', 'integer'],
        ]);

        DB::table('satisfacao')
            ->where('cod', $id)
            ->update([
                'descricao' => $validated['descricao'],
                'cod_tipo_pergunta' => $validated['cod_tipo_pergunta'] ?? null,
            ]);

        return redirect()->route('satisfacao.index')
            ->with('success', 'Satisfação atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        DB::table('satisfacao')->where('cod', $id)->delete();

        return redirect()->route('satisfacao.index')
            ->with('success', 'Satisfação excluída com sucesso!');
    }
}

