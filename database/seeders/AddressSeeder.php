<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all buyers
        $buyers = User::where('role', 'buyer')->get();
        
        foreach ($buyers as $buyer) {
            // Create 1-3 addresses for each buyer
            $addressCount = rand(1, 3);
            
            for ($i = 0; $i < $addressCount; $i++) {
                Address::create([
                    'user_id' => $buyer->id,
                    'name' => $i === 0 ? $buyer->name : $buyer->name . ' ' . ['Work', 'Office', 'Parent\'s', 'Second Home'][array_rand(['Work', 'Office', 'Parent\'s', 'Second Home'])],
                    'phone' => $buyer->phone ?? '08' . rand(100000000, 999999999),
                    'address_line1' => 'Jl. ' . $this->getRandomStreetName() . ' No. ' . rand(1, 999),
                    'address_line2' => rand(0, 1) ? 'RT ' . rand(1, 20) . ' RW ' . rand(1, 10) : null,
                    'city' => $this->getRandomCity(),
                    'state' => $this->getRandomProvince(),
                    'postal_code' => rand(10000, 99999),
                    'is_default' => $i === 0, // First address is default
                ]);
            }
        }
    }
    
    /**
     * Get a random Indonesian street name.
     */
    private function getRandomStreetName(): string
    {
        $streets = [
            'Sudirman', 'Thamrin', 'Gatot Subroto', 'Diponegoro', 'Ahmad Yani',
            'Pahlawan', 'Merdeka', 'Kebon Sirih', 'Hayam Wuruk', 'Gajah Mada',
            'Asia Afrika', 'Veteran', 'Pemuda', 'Kartini', 'Imam Bonjol',
            'Cendrawasih', 'Antasari', 'Sisingamangaraja', 'Panglima Polim', 'Senopati'
        ];
        
        return $streets[array_rand($streets)];
    }
    
    /**
     * Get a random Indonesian city.
     */
    private function getRandomCity(): string
    {
        $cities = [
            'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang',
            'Makassar', 'Palembang', 'Tangerang', 'Depok', 'Bekasi',
            'Bogor', 'Malang', 'Yogyakarta', 'Solo', 'Denpasar',
            'Balikpapan', 'Padang', 'Pontianak', 'Banjarmasin', 'Manado'
        ];
        
        return $cities[array_rand($cities)];
    }
    
    /**
     * Get a random Indonesian province.
     */
    private function getRandomProvince(): string
    {
        $provinces = [
            'DKI Jakarta', 'Jawa Barat', 'Jawa Timur', 'Jawa Tengah', 'Sumatera Utara',
            'Sulawesi Selatan', 'Sumatera Selatan', 'Banten', 'Bali', 'Kalimantan Timur',
            'Sumatera Barat', 'Kalimantan Barat', 'Kalimantan Selatan', 'Sulawesi Utara', 'DI Yogyakarta'
        ];
        
        return $provinces[array_rand($provinces)];
    }
}