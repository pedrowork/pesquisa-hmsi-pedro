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
        // Placeholder - implementar lógica de métricas depois
        $totalQuestionarios = DB::table('questionario')->count();
        $totalPacientes = DB::table('dados_do_paciente')->count();
        $totalSatisfacoes = DB::table('satisfacao')->count();

        return Inertia::render('metricas/index', [
            'stats' => [
                'totalQuestionarios' => $totalQuestionarios,
                'totalPacientes' => $totalPacientes,
                'totalSatisfacoes' => $totalSatisfacoes,
            ],
        ]);
    }
}

