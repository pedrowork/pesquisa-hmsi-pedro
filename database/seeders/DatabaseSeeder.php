<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Seeders de permissões e usuários
            PermissionSeeder::class,
            AdminSeeder::class,
            
            // Seeders de dados do sistema (ordem importante para foreign keys)
            SetorPesquisSeeder::class,
            TipoConvenioSeeder::class,
            SetorSeeder::class,
            SatisfacaoSeeder::class,
            LeitoSeeder::class,
            PerguntasDescricaoSeeder::class,
        ]);
    }
}
