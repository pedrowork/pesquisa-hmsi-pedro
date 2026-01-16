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
        $query = DB::table('perguntas_descricao')
            ->leftJoin('questionario', 'perguntas_descricao.cod', '=', 'questionario.cod_pergunta')
            ->select(
                'perguntas_descricao.*',
                DB::raw('COUNT(DISTINCT questionario.id) as total_pesquisas')
            )
            ->groupBy('perguntas_descricao.cod', 'perguntas_descricao.descricao', 'perguntas_descricao.cod_setor_pesquis', 'perguntas_descricao.cod_tipo_pergunta', 'perguntas_descricao.ativo');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('perguntas_descricao.descricao', 'like', "%{$search}%");
        }

        $perguntas = $query->orderBy('perguntas_descricao.cod', 'asc')->paginate(10);

        return Inertia::render('perguntas/index', [
            'perguntas' => $perguntas,
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
        $pergunta = DB::table('perguntas_descricao')
            ->leftJoin('setor_pesquis', 'perguntas_descricao.cod_setor_pesquis', '=', 'setor_pesquis.cod')
            ->where('perguntas_descricao.cod', $id)
            ->select('perguntas_descricao.*', 'setor_pesquis.descricao as setor_pesquisa_descricao')
            ->first();

        if (!$pergunta) {
            abort(404);
        }

        return Inertia::render('perguntas/show', [
            'pergunta' => $pergunta,
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
            'ativo' => ['nullable', 'boolean'],
        ]);

        DB::table('perguntas_descricao')->insert([
            'descricao' => $validated['descricao'],
            'cod_setor_pesquis' => $validated['cod_setor_pesquis'] ?? null,
            'cod_tipo_pergunta' => $validated['cod_tipo_pergunta'] ?? null,
            'ativo' => $validated['ativo'] ?? true,
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
            'ativo' => ['nullable', 'boolean'],
        ]);

        DB::table('perguntas_descricao')
            ->where('cod', $id)
            ->update([
                'descricao' => $validated['descricao'],
                'cod_setor_pesquis' => $validated['cod_setor_pesquis'] ?? null,
                'cod_tipo_pergunta' => $validated['cod_tipo_pergunta'] ?? null,
                'ativo' => $validated['ativo'] ?? true,
            ]);

        return redirect()->route('perguntas.index')
            ->with('success', 'Pergunta atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     * Se a pergunta tiver pesquisas associadas, apenas desativa ao invés de deletar.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        // Verificar se há questionários (pesquisas) associados a esta pergunta
        $hasQuestionarios = DB::table('questionario')
            ->where('cod_pergunta', $id)
            ->exists();

        if ($hasQuestionarios) {
            // Se houver pesquisas, apenas desativar para manter o histórico
            DB::table('perguntas_descricao')
                ->where('cod', $id)
                ->update(['ativo' => false]);

            return redirect()->route('perguntas.index')
                ->with('warning', 'A pergunta foi desativada porque possui pesquisas associadas. O histórico precisa ser mantido.');
        }

        // Se não houver pesquisas, pode deletar normalmente
        DB::table('perguntas_descricao')->where('cod', $id)->delete();

        return redirect()->route('perguntas.index')
            ->with('success', 'Pergunta excluída com sucesso!');
    }
}

