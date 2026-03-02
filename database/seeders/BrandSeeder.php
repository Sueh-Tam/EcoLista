<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            'Nestlé', 'Coca-Cola', 'Sadia', 'Perdigão', 'Seara', 
            'Bauducco', 'Unilever', 'Procter & Gamble', 'Colgate', 
            'Bombril', 'Ypê', 'Camil', 'Tio João', 'Dona Benta',
            'Renata', 'Adria', 'Vigor', 'Danone', 'Itambé', 'Piracanjuba',
            'Qualitá', 'Taeq', 'Great Value', 'Carrefour', 'Dia'
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['name' => $brand]);
        }
    }
}
