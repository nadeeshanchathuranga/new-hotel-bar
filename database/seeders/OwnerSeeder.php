<?php

namespace Database\Seeders;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\OwnerItem;

class OwnerSeeder extends Seeder
{
    public function run()
    {
        // Owners list
        $ownersData = [
            ['name' => 'Ayodhya Wijethunga',      'code' => 'OWN-001'],
            ['name' => 'Kasun Dissanayaka',    'code' => 'OWN-002'],
            ['name' => 'Shashika Meegoda',   'code' => 'OWN-003'],
            ['name' => 'Buddika Katudeniya',  'code' => 'OWN-004'],
        ];

        // Insert or update owners by code
        foreach ($ownersData as $ownerData) {
            DB::table('owners')->updateOrInsert(
                ['code' => $ownerData['code']], // match by code
                [
                    'name'       => $ownerData['name'],
                    'code'       => $ownerData['code'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Month = first day of current month
        $month = Carbon::now()->startOfMonth()->toDateString(); // e.g., '2025-08-01'

        // Insert or update OwnerItems for each owner
        $ownerIds = DB::table('owners')->pluck('id');
        foreach ($ownerIds as $ownerId) {
            OwnerItem::updateOrCreate(
                [
                    'owner_id' => $ownerId,
                    'month'    => $month, // ensure same month
                ],
                [
                    'discount_value'   => 25000, // fixed value
                    'current_discount' => 0,
                    'status'           => 'active',
                ]
            );
        }
    }
}
