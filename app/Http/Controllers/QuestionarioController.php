<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class QuestionarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = DB::table('questionario')
            ->join('dados_do_paciente', 'questionario.cod_paciente', '=', 'dados_do_paciente.id')
            ->join('users', 'questionario.cod_usuario', '=', 'users.id')
            ->select(
                'questionario.cod_paciente',
                'dados_do_paciente.nome',
                'dados_do_paciente.email',
                'dados_do_paciente.telefone',
                DB::raw('MAX(questionario.data_questionario) as data_questionario'),
                DB::raw('MAX(users.name) as usuario_nome'),
                DB::raw('COUNT(*) as total_respostas')
            )
            ->groupBy(
                'questionario.cod_paciente',
                'dados_do_paciente.nome',
                'dados_do_paciente.email',
                'dados_do_paciente.telefone'
            );

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('dados_do_paciente.nome', 'like', "%{$search}%")
                    ->orWhere('dados_do_paciente.email', 'like', "%{$search}%")
                    ->orWhere('dados_do_paciente.telefone', 'like', "%{$search}%");
            });
        }

        $questionarios = $query
            ->orderBy('data_questionario', 'desc')
            ->orderBy('dados_do_paciente.nome')
            ->paginate(10);

        return Inertia::render('questionarios/index', [
            'questionarios' => $questionarios,
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
        $perguntas = DB::table('perguntas_descricao')
            ->select('cod', 'descricao', 'cod_setor_pesquis', 'cod_tipo_pergunta')
            ->orderBy('descricao')
            ->get();

        $satisfacoes = DB::table('satisfacao')
            ->select('cod', 'descricao', 'cod_tipo_pergunta')
            ->orderBy('descricao')
            ->get();

        $tiposConvenio = DB::table('tipoconvenio')
            ->orderBy('tipo_descricao')
            ->get();

        $setores = DB::table('setor')
            ->orderBy('descricao')
            ->get();

        $leitos = DB::table('leito')
            ->select('cod', 'descricao', 'cod_setor')
            ->orderBy('descricao')
            ->get();

        // Buscar setores de pesquisa das perguntas
        $perguntasComSetor = DB::table('perguntas_descricao')
            ->whereNotNull('cod_setor_pesquis')
            ->distinct()
            ->pluck('cod_setor_pesquis')
            ->toArray();

        $setoresPesquisa = DB::table('setor_pesquis')
            ->whereIn('cod', $perguntasComSetor)
            ->orderBy('descricao')
            ->get();

        return Inertia::render('questionarios/create', [
            'perguntas' => $perguntas->map(function ($pergunta) {
                return [
                    'cod' => $pergunta->cod,
                    'descricao' => $pergunta->descricao,
                    'cod_setor_pesquis' => $pergunta->cod_setor_pesquis ?? null,
                    'cod_tipo_pergunta' => $pergunta->cod_tipo_pergunta ?? null,
                ];
            }),
            'satisfacoes' => $satisfacoes->map(function ($satisfacao) {
                return [
                    'cod' => $satisfacao->cod,
                    'descricao' => $satisfacao->descricao,
                    'cod_tipo_pergunta' => $satisfacao->cod_tipo_pergunta ?? null,
                ];
            }),
            'tiposConvenio' => $tiposConvenio,
            'setores' => $setores,
            'leitos' => $leitos,
            'setoresPesquisa' => $setoresPesquisa,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Dados do paciente
            'nome' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'sexo' => ['required', 'string', 'max:10'],
            'tipo_paciente' => ['nullable', 'string', 'max:50'],
            'idade' => ['required', 'integer', 'min:0'],
            'leito' => ['nullable', 'integer', 'exists:leito,cod'],
            'cod_setor' => ['required', 'integer', 'exists:setor,cod'],
            'renda' => ['nullable', 'string', 'max:100'],
            'tp_cod_convenio' => ['nullable', 'integer', 'exists:tipoconvenio,cod'],
            
            // Dados do questionário
            'data_questionario' => ['nullable', 'date'],
            'data_isretroativa' => ['nullable', 'boolean'],
            'data_retroativa' => ['nullable', 'date'],
            'cod_setor_pesquis' => ['nullable', 'integer', 'exists:setor_pesquis,cod'],
            'observacao' => ['nullable', 'string', 'max:1000'],
            
            // Respostas (array de perguntas e respostas)
            'respostas' => ['required', 'array'],
            'respostas.*.cod_pergunta' => ['required', 'exists:perguntas_descricao,cod'],
            'respostas.*.resposta' => ['required', 'exists:satisfacao,cod'],
        ]);

        try {
            // Usar transação para garantir que tudo seja salvo ou nada seja salvo
            DB::beginTransaction();

            // Buscar descrições
            $setorDescricao = DB::table('setor')
                ->where('cod', $validated['cod_setor'])
                ->value('descricao');
            
            $leitoDescricao = null;
            if (!empty($validated['leito'])) {
                $leitoDescricao = DB::table('leito')
                    ->where('cod', $validated['leito'])
                    ->value('descricao');
            }

            // 1. Criar paciente na tabela 'dados_do_paciente'
            $pacienteId = DB::table('dados_do_paciente')->insertGetId([
                'nome' => $validated['nome'],
                'telefone' => $validated['telefone'],
                'email' => $validated['email'],
                'sexo' => $validated['sexo'],
                'tipo_paciente' => $validated['tipo_paciente'] ?? null,
                'idade' => (int) $validated['idade'],
                'leito' => $leitoDescricao,
                'setor' => $setorDescricao,
                'renda' => $validated['renda'] ?? null,
                'tp_cod_convenio' => $validated['tp_cod_convenio'] ? (int) $validated['tp_cod_convenio'] : null,
            ]);

            // 2. Criar registros do questionário na tabela 'questionario' para cada resposta
            $usuarioId = auth()->id();
            if (!$usuarioId) {
                DB::rollBack();
                return redirect()->back()
                    ->withErrors(['error' => 'Usuário não autenticado.'])
                    ->withInput();
            }

            $dataQuestionario = $validated['data_questionario'] ?? now()->format('Y-m-d');

            foreach ($validated['respostas'] as $resposta) {
                DB::table('questionario')->insert([
                    'cod_pergunta' => (int) $resposta['cod_pergunta'],
                    'resposta' => (int) $resposta['resposta'],
                    'cod_paciente' => (int) $pacienteId,
                    'cod_usuario' => (int) $usuarioId,
                    'data_questionario' => $dataQuestionario,
                    'data_isretroativa' => $validated['data_isretroativa'] ?? false,
                    'data_retroativa' => $validated['data_retroativa'] ?? null,
                    'cod_setor_pesquis' => $validated['cod_setor_pesquis'] ? (int) $validated['cod_setor_pesquis'] : null,
                    'observacao' => $validated['observacao'] ?? null,
                ]);
            }

            // Confirmar transação
            DB::commit();

            return redirect()->route('questionarios.index')
                ->with('success', 'Questionário criado com sucesso!');
        } catch (\Exception $e) {
            // Reverter transação em caso de erro
            DB::rollBack();
            Log::error('Erro ao criar questionário: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao salvar questionário. Por favor, tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $pacienteId): Response
    {
        $paciente = DB::table('dados_do_paciente')
            ->leftJoin('tipoconvenio', 'dados_do_paciente.tp_cod_convenio', '=', 'tipoconvenio.cod')
            ->where('dados_do_paciente.id', $pacienteId)
            ->select(
                'dados_do_paciente.*',
                'tipoconvenio.tipo_descricao'
            )
            ->first();

        if (!$paciente) {
            abort(404);
        }

        $respostas = DB::table('questionario')
            ->join('perguntas_descricao', 'questionario.cod_pergunta', '=', 'perguntas_descricao.cod')
            ->join('satisfacao', 'questionario.resposta', '=', 'satisfacao.cod')
            ->join('users', 'questionario.cod_usuario', '=', 'users.id')
            ->where('questionario.cod_paciente', $pacienteId)
            ->select(
                'questionario.*',
                'perguntas_descricao.descricao as pergunta_descricao',
                'satisfacao.descricao as resposta_descricao',
                'users.name as usuario_nome'
            )
            ->orderBy('perguntas_descricao.descricao')
            ->get();

        return Inertia::render('questionarios/show', [
            'paciente' => $paciente,
            'respostas' => $respostas,
        ]);
    }
}

