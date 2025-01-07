<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'company_name' => 'Amazon Inc.',
                'company_address' => '410 Terry Ave N, Seattle, WA 98109, United States',
                'company_phone' => '+1 206-922-0880',
                'company_email' => 'contact@amazon.com',
                'supplier_name' => 'Jeff Bezos',
                'supplier_phone' => '+1 206-266-1000',
                'company_id' => 1
            ],
            [
                'company_name' => 'Walmart Inc.',
                'company_address' => '702 SW 8th St, Bentonville, AR 72712, United States',
                'company_phone' => '+1 800-925-6278',
                'company_email' => 'contact@walmart.com',
                'supplier_name' => 'Doug McMillon',
                'supplier_phone' => '+1 479-273-4000',
                'company_id' => 1
            ],
            [
                'company_name' => 'Alibaba Group',
                'company_address' => '969 West Wen Yi Road, Yu Hang District, Hangzhou 311121, China',
                'company_phone' => '+86 571-8502-2088',
                'company_email' => 'contact@alibaba.com',
                'supplier_name' => 'Daniel Zhang',
                'supplier_phone' => '+86 571-8502-2077',
                'company_id' => 1
            ],
            [
                'company_name' => 'Samsung Electronics',
                'company_address' => '129 Samsung-ro, Yeongtong-gu, Suwon-si, Gyeonggi-do, South Korea',
                'company_phone' => '+82 2-2053-3000',
                'company_email' => 'contact@samsung.com',
                'supplier_name' => 'Kim Ki Nam',
                'supplier_phone' => '+82 2-2053-3008',
                'company_id' => 1
            ],
            [
                'company_name' => 'Apple Inc.',
                'company_address' => 'One Apple Park Way, Cupertino, CA 95014, United States',
                'company_phone' => '+1 408-996-1010',
                'company_email' => 'contact@apple.com',
                'supplier_name' => 'Tim Cook',
                'supplier_phone' => '+1 408-996-1020',
                'company_id' => 1
            ],
            [
                'company_name' => 'Microsoft Corporation',
                'company_address' => 'One Microsoft Way, Redmond, WA 98052, United States',
                'company_phone' => '+1 425-882-8080',
                'company_email' => 'contact@microsoft.com',
                'supplier_name' => 'Satya Nadella',
                'supplier_phone' => '+1 425-882-8090',
                'company_id' => 1
            ],
            [
                'company_name' => 'Toyota Motor Corporation',
                'company_address' => '1 Toyota-Cho, Toyota City, Aichi Prefecture 471-8571, Japan',
                'company_phone' => '+81 565-28-2121',
                'company_email' => 'contact@toyota.com',
                'supplier_name' => 'Akio Toyoda',
                'supplier_phone' => '+81 565-28-2111',
                'company_id' => 1
            ],
            [
                'company_name' => 'Intel Corporation',
                'company_address' => '2200 Mission College Blvd, Santa Clara, CA 95054, United States',
                'company_phone' => '+1 408-765-8080',
                'company_email' => 'contact@intel.com',
                'supplier_name' => 'Pat Gelsinger',
                'supplier_phone' => '+1 408-765-8090',
                'company_id' => 1
            ],
            [
                'company_name' => 'Sony Corporation',
                'company_address' => '1-7-1 Konan, Minato-ku, Tokyo 108-0075, Japan',
                'company_phone' => '+81 3-6748-2111',
                'company_email' => 'contact@sony.com',
                'supplier_name' => 'Kenichiro Yoshida',
                'supplier_phone' => '+81 3-6748-2180',
                'company_id' => 1
            ],
            [
                'company_name' => 'Google LLC',
                'company_address' => '1600 Amphitheatre Parkway, Mountain View, CA 94043, United States',
                'company_phone' => '+1 650-253-0000',
                'company_email' => 'contact@google.com',
                'supplier_name' => 'Sundar Pichai',
                'supplier_phone' => '+1 650-253-0001',
                'company_id' => 1
            ]
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
