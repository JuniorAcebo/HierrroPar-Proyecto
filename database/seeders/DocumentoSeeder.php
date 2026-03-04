<?php

namespace Database\Seeders;

use App\Models\Documento;
use Illuminate\Database\Seeder;

class DocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $documentos = [
            ['tipo_documento' => 'CI'],
            ['tipo_documento' => 'Pasaporte'],
            ['tipo_documento' => 'NIT'],
            ['tipo_documento' => 'Carnet Extranjería'],
        ];

        foreach ($documentos as $doc) {
            Documento::create($doc);
        }
    }
}