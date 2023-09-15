<?php

namespace Database\Seeders;

use App\Models\Nationalitie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NationalitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('nationalities')->delete();

        $nationals = [
            'Saudi Arabian', 'Syrian', 'Afghan', 'Algerian', 'Argentinian', 'Bahraini', 'Bangladeshi', 'Belarusian', 'Belgian', 'Egyptian',   'Indian', 'Iraqi',
            'Irish',    'Italian', 'Jordanian', 'Kuwaiti', 'Libyan', 'Moroccan', 'Pakistani', 'Palestinian', 'Filipino',
            'Polish', 'Portuguese', 'Portuguese', 'Romanian', 'Qatari', 'Russian', 'Singaporean', 'Somali', 'Sudanese', 'Swedish', 'Swiss', 'Taiwanese',
            'Tajikistani', 'Thai', 'Tunisian', 'Turkish', 'Ukrainian', 'Emirati', 'British', 'American', 'Uruguayan', 'Uzbek', 'Venezuelan',
            'Yemeni', 'South African', 'Serbian', 'Panamanian', 'Omani', 'Norwegian', 'Nigerian', 'Dutch', 'Mexican', 'Mauritanian', 'Malaysian', 'Japanese'
        ];

        foreach ($nationals as $n) {
            Nationalitie::create(['Name' => $n]);
        }
    }
}