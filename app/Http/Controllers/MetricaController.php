<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MetricaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isAdmin = $user && method_exists($user, 'isAdmin') ? $user->isAdmin() : false;

        // Filtros
        $from = $request->input('from');         // yyyy-mm-dd
        $to = $request->input('to');             // yyyy-mm-dd
        $setor = $request->input('setor');       // descrição do setor (em dados_do_paciente.setor)
        $tipoPaciente = $request->input('tipo_paciente'); // 'Paciente' | 'Acompanhante'
        $convenio = $request->input('convenio'); // cod em tipoconvenio

        // Helper para aplicar filtros em uma query base questionario + paciente
        $applyFilters = function ($query) use ($from, $to, $setor, $tipoPaciente, $convenio) {
            if ($from) {
                $query->whereDate('questionario.data_questionario', '>=', $from);
            }
            if ($to) {
                $query->whereDate('questionario.data_questionario', '<=', $to);
            }
            if ($setor) {
                $query->where('dados_do_paciente.setor', $setor);
            }
            if ($tipoPaciente) {
                $query->where('dados_do_paciente.tipo_paciente', $tipoPaciente);
            }
            if ($convenio) {
                $query->where('dados_do_paciente.tp_cod_convenio', (int) $convenio);
            }
        };

        // Opções de filtro
        $setoresOptions = DB::table('setor')->orderBy('descricao')->pluck('descricao');
        $conveniosOptions = DB::table('tipoconvenio')->orderBy('tipo_descricao')->get(['cod', 'tipo_descricao']);
        $tiposPacienteOptions = ['Paciente', 'Acompanhante'];

        $payload = [
            'filters' => [
                'from' => $from,
                'to' => $to,
                'setor' => $setor,
                'tipo_paciente' => $tipoPaciente,
                'convenio' => $convenio,
            ],
            'filterOptions' => [
                'setores' => $setoresOptions,
                'convenios' => $conveniosOptions,
                'tiposPaciente' => $tiposPacienteOptions,
            ],
        ];

        // Visão geral
        if ($isAdmin || $user->hasPermission('metricas.overview')) {
            $base = DB::table('questionario')
                ->join('dados_do_paciente', 'questionario.cod_paciente', '=', 'dados_do_paciente.id');
            $applyFilters($base);

            $totalQuestionarios = (clone $base)
                ->select('questionario.cod_paciente')
                ->distinct()->count('questionario.cod_paciente');

            $totalRespostas = (clone $base)->count();

            $satisfacaoMedia = (clone $base)
                ->join('satisfacao', 'questionario.resposta', '=', 'satisfacao.cod')
                ->join('perguntas_descricao', 'questionario.cod_pergunta', '=', 'perguntas_descricao.cod')
                ->where('perguntas_descricao.cod_tipo_pergunta', 3)
                ->whereBetween('satisfacao.cod', [10, 20])
                ->selectRaw('AVG(satisfacao.cod - 10) as media')
                ->value('media');

            $payload['overview'] = [
                'totalQuestionarios' => $totalQuestionarios,
                'totalRespostas' => $totalRespostas,
                'satisfacaoMedia' => $satisfacaoMedia !== null ? round($satisfacaoMedia, 2) : 0,
            ];
        }

        // NPS (pergunta cod=10 por padrão)
        if ($isAdmin || $user->hasPermission('metricas.nps')) {
            // Descobrir dinamicamente o ID da pergunta de NPS pela descrição
            $npsDescription = "Em uma escala de 0 a 10, quanto você recomendaria o Hospital e Maternidade Santa Isabel para um familiar ou amigo?";
            $npsQuestionId = DB::table('perguntas_descricao')
                ->where('descricao', $npsDescription)
                ->value('cod');

            // Fallback por aproximação caso a descrição exata não bata
            if (!$npsQuestionId) {
                $npsQuestionId = DB::table('perguntas_descricao')
                    ->whereRaw("LOWER(descricao) LIKE LOWER('%recomendaria%')")
                    ->orderBy('cod')
                    ->value('cod');
            }

            $npsBase = DB::table('questionario')
                ->join('dados_do_paciente', 'questionario.cod_paciente', '=', 'dados_do_paciente.id')
                ->join('satisfacao', 'questionario.resposta', '=', 'satisfacao.cod')
                ->where('questionario.cod_pergunta', $npsQuestionId)
                ->whereBetween('satisfacao.cod', [10, 20]);
            $applyFilters($npsBase);

            // Nota numérica derivada da descrição da satisfação (0..10)
            $notas = $npsBase->selectRaw('CAST(satisfacao.descricao AS INTEGER) AS nota')->pluck('nota');
            $total = max(1, $notas->count());
            $promotores = $notas->filter(fn ($n) => $n >= 9)->count();
            $detratores = $notas->filter(fn ($n) => $n <= 6)->count();
            $neutros = $notas->filter(fn ($n) => $n >= 7 && $n <= 8)->count();
            $nps = (100 * $promotores / $total) - (100 * $detratores / $total);

            $payload['nps'] = round($nps, 2);
            // Média 0–10 pela própria coleção (evita diferenças entre SGBDs)
            $npsMean = $notas->count() ? $notas->avg() : null;
            $payload['npsMean'] = $npsMean !== null ? round($npsMean, 2) : null;
            $payload['npsDetail'] = [
                'total' => $notas->count(),
                'promotores' => $promotores,
                'neutros' => $neutros,
                'detratores' => $detratores,
                'percPromotores' => $notas->count() ? round(100 * $promotores / $notas->count(), 2) : 0,
                'percDetratores' => $notas->count() ? round(100 * $detratores / $notas->count(), 2) : 0,
            ];

            // NPS por setor
            $npsSetorQuery = DB::table('questionario')
                ->join('dados_do_paciente', 'questionario.cod_paciente', '=', 'dados_do_paciente.id')
                ->join('satisfacao', 'questionario.resposta', '=', 'satisfacao.cod')
                ->where('questionario.cod_pergunta', $npsQuestionId)
                ->whereBetween('satisfacao.cod', [10, 20]);
            $applyFilters($npsSetorQuery);

            $npsBySetorRaw = $npsSetorQuery
                ->select(
                    'dados_do_paciente.setor as setor',
                    DB::raw("SUM(CASE WHEN CAST(satisfacao.descricao AS INTEGER) >= 9 THEN 1 ELSE 0 END) as promotores"),
                    DB::raw("SUM(CASE WHEN CAST(satisfacao.descricao AS INTEGER) BETWEEN 7 AND 8 THEN 1 ELSE 0 END) as neutros"),
                    DB::raw("SUM(CASE WHEN CAST(satisfacao.descricao AS INTEGER) <= 6 THEN 1 ELSE 0 END) as detratores"),
                    DB::raw("COUNT(*) as total")
                )
                ->whereNotNull('dados_do_paciente.setor')
                ->groupBy('dados_do_paciente.setor')
                ->get();

            $npsBySetor = $npsBySetorRaw->map(function ($row) {
                $total = max(1, (int) $row->total);
                $nps = (100 * ((int) $row->promotores) / $total) - (100 * ((int) $row->detratores) / $total);
                return [
                    'setor' => $row->setor,
                    'promotores' => (int) $row->promotores,
                    'neutros' => (int) $row->neutros,
                    'detratores' => (int) $row->detratores,
                    'total' => (int) $row->total,
                    'nps' => round($nps, 2),
                ];
            })->sortByDesc('nps')->values();

            $payload['npsDetail']['bySetor'] = $npsBySetor;
        }

        // Médias por setor e ranking
        if ($isAdmin || $user->hasPermission('metricas.setores')) {
            $porSetor = DB::table('questionario')
                ->join('dados_do_paciente', 'questionario.cod_paciente', '=', 'dados_do_paciente.id')
                ->join('satisfacao', 'questionario.resposta', '=', 'satisfacao.cod')
                ->join('perguntas_descricao', 'questionario.cod_pergunta', '=', 'perguntas_descricao.cod')
                ->where('perguntas_descricao.cod_tipo_pergunta', 3)
                ->whereBetween('satisfacao.cod', [10, 20]);
            $applyFilters($porSetor);

            $mediasPorSetor = (clone $porSetor)
                ->select('dados_do_paciente.setor as setor', DB::raw('ROUND(AVG(satisfacao.cod - 10), 2) AS media'))
                ->whereNotNull('dados_do_paciente.setor')
                ->groupBy('dados_do_paciente.setor')
                ->orderByDesc('media')
                ->get();

            $rankingSetores = DB::table('questionario')
                ->join('dados_do_paciente', 'questionario.cod_paciente', '=', 'dados_do_paciente.id');
            $applyFilters($rankingSetores);
            $rankingSetores = $rankingSetores
                ->select('dados_do_paciente.setor as setor', DB::raw('COUNT(*) as total'))
                ->whereNotNull('dados_do_paciente.setor')
                ->groupBy('dados_do_paciente.setor')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            $payload['setores'] = [
                'medias' => $mediasPorSetor,
                'ranking' => $rankingSetores,
            ];
        }

        // Médias por dimensão/pergunta (tipo 3)
        if ($isAdmin || $user->hasPermission('metricas.dimensoes')) {
            $porPergunta = DB::table('questionario')
                ->join('dados_do_paciente', 'questionario.cod_paciente', '=', 'dados_do_paciente.id')
                ->join('satisfacao', 'questionario.resposta', '=', 'satisfacao.cod')
                ->join('perguntas_descricao', 'questionario.cod_pergunta', '=', 'perguntas_descricao.cod')
                ->where('perguntas_descricao.cod_tipo_pergunta', 3)
                ->whereBetween('satisfacao.cod', [10, 20]);
            $applyFilters($porPergunta);

            $mediasPorPergunta = $porPergunta
                ->select(
                    'perguntas_descricao.cod as cod',
                    'perguntas_descricao.descricao as descricao',
                    DB::raw('ROUND(AVG(satisfacao.cod - 10), 2) AS media')
                )
                ->groupBy('perguntas_descricao.cod', 'perguntas_descricao.descricao')
                ->orderByRaw('CASE WHEN perguntas_descricao.ordem IS NULL THEN 1 ELSE 0 END')
                ->orderBy('perguntas_descricao.ordem', 'asc')
                ->orderBy('perguntas_descricao.cod')
                ->get();

            $payload['dimensoes'] = $mediasPorPergunta;
        }

        // Distribuições
        if ($isAdmin || $user->hasPermission('metricas.distribuicoes')) {
            $distBase = DB::table('dados_do_paciente');
            if ($tipoPaciente) {
                $distBase->where('tipo_paciente', $tipoPaciente);
            }
            if ($convenio) {
                $distBase->where('tp_cod_convenio', (int) $convenio);
            }
            if ($setor) {
                $distBase->where('setor', $setor);
            }
            // Para data, filtramos via questionario para os pacientes com respostas no período
            if ($from || $to) {
                $distBase->whereIn('dados_do_paciente.id', function ($q) use ($from, $to) {
                    $q->from('questionario')
                        ->select('cod_paciente');
                    if ($from) {
                        $q->whereDate('data_questionario', '>=', $from);
                    }
                    if ($to) {
                        $q->whereDate('data_questionario', '<=', $to);
                    }
                });
            }

            // tipo_paciente
            $tipoPacienteDist = (clone $distBase)
                ->select('tipo_paciente', DB::raw('COUNT(*) as total'))
                ->whereNotNull('tipo_paciente')
                ->groupBy('tipo_paciente')
                ->get();

            // sexo
            $sexoDist = (clone $distBase)
                ->select('sexo', DB::raw('COUNT(*) as total'))
                ->whereNotNull('sexo')
                ->groupBy('sexo')
                ->get();

            // renda
            $rendaDist = (clone $distBase)
                ->select('renda', DB::raw('COUNT(*) as total'))
                ->whereNotNull('renda')
                ->groupBy('renda')
                ->get();

            // faixa etária
            $faixaEtariaDist = (clone $distBase)
                ->select(
                    DB::raw("CASE
                        WHEN idade BETWEEN 0 AND 10 THEN '0-10'
                        WHEN idade BETWEEN 11 AND 20 THEN '11-20'
                        WHEN idade BETWEEN 21 AND 30 THEN '21-30'
                        WHEN idade BETWEEN 31 AND 40 THEN '31-40'
                        WHEN idade BETWEEN 41 AND 50 THEN '41-50'
                        ELSE '51+' END as faixa"
                    ),
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('faixa')
                ->orderByRaw("MIN(idade)")
                ->get();

            // convênio
            $convenioDist = (clone $distBase)
                ->leftJoin('tipoconvenio', 'dados_do_paciente.tp_cod_convenio', '=', 'tipoconvenio.cod')
                ->select('tipoconvenio.tipo_descricao as convenio', DB::raw('COUNT(*) as total'))
                ->groupBy('tipoconvenio.tipo_descricao')
                ->get();

            $payload['distribuicoes'] = [
                'tipoPaciente' => $tipoPacienteDist,
                'sexo' => $sexoDist,
                'renda' => $rendaDist,
                'faixaEtaria' => $faixaEtariaDist,
                'convenio' => $convenioDist,
            ];
        }

        // Evolução mensal
        if ($isAdmin || $user->hasPermission('metricas.temporal')) {
            $temporal = DB::table('questionario')
                ->join('dados_do_paciente', 'questionario.cod_paciente', '=', 'dados_do_paciente.id')
                ->join('satisfacao', 'questionario.resposta', '=', 'satisfacao.cod')
                ->join('perguntas_descricao', 'questionario.cod_pergunta', '=', 'perguntas_descricao.cod')
                ->where('perguntas_descricao.cod_tipo_pergunta', 3)
                ->whereBetween('satisfacao.cod', [10, 20]);
            $applyFilters($temporal);

            // Cross-DB expressions for year/month (SQLite vs MySQL vs PostgreSQL)
            $driver = DB::connection()->getDriverName();
            if ($driver === 'sqlite') {
                $yearExpr = "CAST(strftime('%Y', questionario.data_questionario) AS INTEGER)";
                $monthExpr = "CAST(strftime('%m', questionario.data_questionario) AS INTEGER)";
            } elseif ($driver === 'pgsql') {
                // PostgreSQL uses EXTRACT function
                $yearExpr = "EXTRACT(YEAR FROM questionario.data_questionario)::INTEGER";
                $monthExpr = "EXTRACT(MONTH FROM questionario.data_questionario)::INTEGER";
            } else {
                // Works for MySQL and MariaDB
                $yearExpr = 'YEAR(questionario.data_questionario)';
                $monthExpr = 'MONTH(questionario.data_questionario)';
            }

            $serie = $temporal
                ->selectRaw("$yearExpr as ano, $monthExpr as mes, ROUND(AVG(satisfacao.cod - 10), 2) as media")
                ->groupByRaw("$yearExpr, $monthExpr")
                ->orderBy('ano')
                ->orderBy('mes')
                ->get();

            $payload['temporal'] = $serie;
        }

        return Inertia::render('app/metricas/index', $payload);
    }
}

