<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Member;
use App\Models\Account;
use App\Models\MemberNextOfKin;


class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $members = [
            [
                'user_data' => [
                    'name' => 'Mary Wanjiku',
                    'email' => 'mary.wanjiku@example.com',
                    'password' => Hash::make('password123'),
                    'phone' => '+254720000001',
                    'role' => 'member',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
                'member_data' => [
                    'membership_id' => 'MEM001',
                    'first_name' => 'Mary',
                    'last_name' => 'Wanjiku',
                    'middle_name' => 'Njeri',
                    'id_number' => '12345678',
                    'id_type' => 'national_id',
                    'date_of_birth' => '1985-05-15',
                    'gender' => 'female',
                    'marital_status' => 'married',
                    'occupation' => 'Teacher',
                    'employer' => 'Machakos Primary School',
                    'monthly_income' => 45000.00,
                    'physical_address' => 'Machakos Town, Machakos County',
                    'postal_address' => 'P.O. Box 456, Machakos',
                    'city' => 'Machakos',
                    'county' => 'Machakos',
                    'country' => 'Kenya',
                    'emergency_contact_name' => 'John Wanjiku',
                    'emergency_contact_phone' => '+254720000011',
                    'emergency_contact_relationship' => 'Husband',
                    'membership_status' => 'active',
                    'membership_date' => '2024-06-01',
                ],
                'next_of_kin' => [
                    'name' => 'John Wanjiku',
                    'relationship' => 'Husband',
                    'phone' => '+254720000011',
                    'email' => 'john.wanjiku@example.com',
                    'address' => 'Machakos Town, Machakos County',
                    'allocation_percentage' => 100.00,
                    'is_primary' => true,
                ]
            ],
            [
                'user_data' => [
                    'name' => 'James Mutua',
                    'email' => 'james.mutua@example.com',
                    'password' => Hash::make('password123'),
                    'phone' => '+254720000002',
                    'role' => 'member',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
                'member_data' => [
                    'membership_id' => 'MEM002',
                    'first_name' => 'James',
                    'last_name' => 'Mutua',
                    'middle_name' => 'Kioko',
                    'id_number' => '87654321',
                    'id_type' => 'national_id',
                    'date_of_birth' => '1980-08-22',
                    'gender' => 'male',
                    'marital_status' => 'single',
                    'occupation' => 'Business Owner',
                    'employer' => 'Self Employed',
                    'monthly_income' => 75000.00,
                    'physical_address' => 'Kathiani, Machakos County',
                    'postal_address' => 'P.O. Box 789, Kathiani',
                    'city' => 'Kathiani',
                    'county' => 'Machakos',
                    'country' => 'Kenya',
                    'emergency_contact_name' => 'Grace Mutua',
                    'emergency_contact_phone' => '+254720000022',
                    'emergency_contact_relationship' => 'Sister',
                    'membership_status' => 'active',
                    'membership_date' => '2024-01-15',
                ],
                'next_of_kin' => [
                    'name' => 'Grace Mutua',
                    'relationship' => 'Sister',
                    'phone' => '+254720000022',
                    'email' => 'grace.mutua@example.com',
                    'address' => 'Kathiani, Machakos County',
                    'allocation_percentage' => 100.00,
                    'is_primary' => true,
                ]
            ],
            [
                'user_data' => [
                    'name' => 'Sarah Mwangi',
                    'email' => 'sarah.mwangi@example.com',
                    'password' => Hash::make('password123'),
                    'phone' => '+254720000003',
                    'role' => 'member',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
                'member_data' => [
                    'membership_id' => 'MEM003',
                    'first_name' => 'Sarah',
                    'last_name' => 'Mwangi',
                    'middle_name' => 'Wanjiru',
                    'id_number' => '11223344',
                    'id_type' => 'national_id',
                    'date_of_birth' => '1990-12-10',
                    'gender' => 'female',
                    'marital_status' => 'single',
                    'occupation' => 'Nurse',
                    'employer' => 'Machakos Level 5 Hospital',
                    'monthly_income' => 55000.00,
                    'physical_address' => 'Mavoko, Machakos County',
                    'postal_address' => 'P.O. Box 321, Mavoko',
                    'city' => 'Mavoko',
                    'county' => 'Machakos',
                    'country' => 'Kenya',
                    'emergency_contact_name' => 'Peter Mwangi',
                    'emergency_contact_phone' => '+254720000033',
                    'emergency_contact_relationship' => 'Father',
                    'membership_status' => 'active',
                    'membership_date' => '2024-03-20',
                ],
                'next_of_kin' => [
                    'name' => 'Peter Mwangi',
                    'relationship' => 'Father',
                    'phone' => '+254720000033',
                    'email' => 'peter.mwangi@example.com',
                    'address' => 'Mavoko, Machakos County',
                    'allocation_percentage' => 100.00,
                    'is_primary' => true,
                ]
            ],
        ];

        foreach ($members as $memberInfo) {
            // Create user
            $user = User::updateOrCreate(
                ['email' => $memberInfo['user_data']['email']],
                $memberInfo['user_data']
            );

            // Create member
            $memberData = $memberInfo['member_data'];
            $memberData['user_id'] = $user->id;
            
            $member = Member::updateOrCreate(
                ['membership_id' => $memberData['membership_id']],
                $memberData
            );

            // Create accounts for the member
            $accountTypes = ['savings', 'shares', 'deposits'];
            foreach ($accountTypes as $accountType) {
                $accountNumber = 'SEPU' . str_pad($member->id, 4, '0', STR_PAD_LEFT) . strtoupper(substr($accountType, 0, 1));
                
                Account::updateOrCreate(
                    [
                        'account_number' => $accountNumber
                    ],
                    [
                        'member_id' => $member->id,
                        'account_type' => $accountType,
                        'balance' => $accountType === 'shares' ? 5000.00 : 1000.00,
                        'available_balance' => $accountType === 'shares' ? 5000.00 : 1000.00,
                        'is_active' => true,
                        'last_transaction_at' => now(),
                    ]
                );
            }

            // Create next of kin
            $nextOfKinData = $memberInfo['next_of_kin'];
            $nextOfKinData['member_id'] = $member->id;
            
            MemberNextOfKin::updateOrCreate(
                [
                    'member_id' => $member->id,
                    'name' => $nextOfKinData['name']
                ],
                $nextOfKinData
            );
        }
    }
    
}