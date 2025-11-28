<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoConvenioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposConvenio = [
            ['cod' => 1, 'tipo_descricao' => 'SUS'],
            ['cod' => 2, 'tipo_descricao' => 'IPES'],
            ['cod' => 3, 'tipo_descricao' => 'PARTICULAR'],
            ['cod' => 4, 'tipo_descricao' => 'DIAVERUM'],
            ['cod' => 5, 'tipo_descricao' => 'OUTROS'],
        ];

        foreach ($tiposConvenio as $tipo) {
            DB::table('tipoconvenio')->updateOrInsert(
                ['cod' => $tipo['cod']],
                [
                    'tipo_descricao' => $tipo['tipo_descricao'],
                ]
            );
        }

        $this->command->info('Tipos de Convênio criados/atualizados: ' . count($tiposConvenio) . ' registros');
    }
}


