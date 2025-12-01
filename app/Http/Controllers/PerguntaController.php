<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PerguntaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = DB::table('perguntas_descricao');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('descricao', 'like', "%{$search}%");
        }

        $perguntas = $query->orderBy('cod', 'asc')->paginate(10);

        return Inertia::render('perguntas/index', [
            'perguntas' => $perguntas,
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
        $setoresPesquisa = DB::table('setor_pesquis')
            ->orderBy('descricao')
            ->get();

        return Inertia::render('perguntas/create', [
            'setoresPesquisa' => $setoresPesquisa,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'cod_setor_pesquis' => ['nullable', 'exists:setor_pesquis,cod'],
            'cod_tipo_pergunta' => ['nullable', 'integer'],
        ]);

        DB::table('perguntas_descricao')->insert([
            'descricao' => $validated['descricao'],
            'cod_setor_pesquis' => $validated['cod_setor_pesquis'] ?? null,
            'cod_tipo_pergunta' => $validated['cod_tipo_pergunta'] ?? null,
        ]);

        return redirect()->route('perguntas.index')
            ->with('success', 'Pergunta criada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {
        $pergunta = DB::table('perguntas_descricao')
            ->where('cod', $id)
            ->first();

        if (!$pergunta) {
            abort(404);
        }

        $setoresPesquisa = DB::table('setor_pesquis')
            ->orderBy('descricao')
            ->get();

        return Inertia::render('perguntas/edit', [
            'pergunta' => $pergunta,
            'setoresPesquisa' => $setoresPesquisa,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'cod_setor_pesquis' => ['nullable', 'exists:setor_pesquis,cod'],
            'cod_tipo_pergunta' => ['nullable', 'integer'],
        ]);

        DB::table('perguntas_descricao')
            ->where('cod', $id)
            ->update([
                'descricao' => $validated['descricao'],
                'cod_setor_pesquis' => $validated['cod_setor_pesquis'] ?? null,
                'cod_tipo_pergunta' => $validated['cod_tipo_pergunta'] ?? null,
            ]);

        return redirect()->route('perguntas.index')
            ->with('success', 'Pergunta atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        DB::table('perguntas_descricao')->where('cod', $id)->delete();

        return redirect()->route('perguntas.index')
            ->with('success', 'Pergunta excluída com sucesso!');
    }
}

