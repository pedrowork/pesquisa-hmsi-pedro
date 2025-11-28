<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetorPesquisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $setoresPesquis = [
            ['cod' => 1, 'descricao' => 'Higienização e Limpeza'],
            ['cod' => 2, 'descricao' => 'Enfermagem'],
            ['cod' => 3, 'descricao' => 'Medicina'],
            ['cod' => 4, 'descricao' => 'Serviço Social'],
            ['cod' => 5, 'descricao' => 'Nutrição e Dietética'],
            ['cod' => 6, 'descricao' => 'Manutenção'],
            ['cod' => 7, 'descricao' => 'Geral'],
        ];

        foreach ($setoresPesquis as $setor) {
            DB::table('setor_pesquis')->updateOrInsert(
                ['cod' => $setor['cod']],
                [
                    'descricao' => $setor['descricao'],
                ]
            );
        }

        $this->command->info('Setores de Pesquisa criados/atualizados: ' . count($setoresPesquis) . ' registros');
    }
}


