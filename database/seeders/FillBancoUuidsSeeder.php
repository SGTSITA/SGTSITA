<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bancos;
use Illuminate\Support\Str;

class FillBancoUuidsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bancos::whereNull('uuid')->get()->each(function ($banco) {
            $banco->uuid = Str::uuid();
            $banco->save();
        });
    }
}
