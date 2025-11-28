<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $setores = [
            ['cod' => 1, 'descricao' => 'Flor de Lis'],
            ['cod' => 2, 'descricao' => 'Flor de Cerejeira'],
            ['cod' => 3, 'descricao' => 'Lírios'],
            ['cod' => 4, 'descricao' => 'Flor de Laranjeira'],
            ['cod' => 5, 'descricao' => 'Utin Amor Perfeito'],
            ['cod' => 6, 'descricao' => 'UCinco'],
            ['cod' => 7, 'descricao' => 'Ala Girassol'],
            ['cod' => 8, 'descricao' => 'Alfazema'],
            ['cod' => 9, 'descricao' => 'Jasmin'],
            ['cod' => 10, 'descricao' => 'Recepção Principal'],
            ['cod' => 11, 'descricao' => 'Recepção Pediatria'],
            ['cod' => 12, 'descricao' => 'Recepção Suplementar'],
            ['cod' => 13, 'descricao' => 'Recepção Maternidade'],
            ['cod' => 14, 'descricao' => 'Violeta'],
            ['cod' => 15, 'descricao' => 'Rosa'],
            ['cod' => 16, 'descricao' => 'Cravo'],
            ['cod' => 17, 'descricao' => 'Margarida'],
            ['cod' => 18, 'descricao' => 'Lótus'],
            ['cod' => 19, 'descricao' => 'UTI Hortênsia'],
            ['cod' => 20, 'descricao' => 'UTI Orquídea'],
            ['cod' => 21, 'descricao' => 'Lavanda'],
            ['cod' => 22, 'descricao' => 'Centro Cirúrgico'],
            ['cod' => 23, 'descricao' => 'Ortopedia'],
        ];

        foreach ($setores as $setor) {
            DB::table('setor')->updateOrInsert(
                ['cod' => $setor['cod']],
                [
                    'descricao' => $setor['descricao'],
                ]
            );
        }

        $this->command->info('Setores criados/atualizados: ' . count($setores) . ' registros');
    }
}


