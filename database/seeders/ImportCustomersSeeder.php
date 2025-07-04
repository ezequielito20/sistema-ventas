<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportCustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Datos de customers desde MySQL
        $customers = [
            [1,'Jairo',null,null,null,0,1,'2025-03-26 16:33:57','2025-03-29 18:30:05'],
            [2,'Mi Niña',null,null,null,0,1,'2025-03-26 17:27:34','2025-03-29 18:31:00'],
            [3,'Yohana Ivic',null,'(424) 130-2149',null,0,1,'2025-03-26 17:27:43','2025-03-29 17:56:41'],
            [4,'Orlando Ivic',null,null,null,0,1,'2025-03-26 17:27:52','2025-03-26 18:01:56'],
            [5,'Maricel Ivic',null,null,null,0,1,'2025-03-26 17:28:00','2025-03-26 18:02:13'],
            [6,'Astrid Ivic',null,'(412) 033-4524',null,0,1,'2025-03-26 17:28:07','2025-03-31 17:55:44'],
            [7,'Mirangel Ivic',null,null,null,0,1,'2025-03-26 17:28:15','2025-03-29 18:00:30'],
            [8,'Tia Yoma',null,null,null,0,1,'2025-03-26 17:28:28','2025-03-26 18:03:04'],
            [9,'Elizabeth Hermana De Yoma',null,null,null,0,1,'2025-03-26 17:29:06','2025-03-26 18:03:21'],
            [10,'Hector Cecom Ivic',null,null,null,0,1,'2025-03-26 17:29:19','2025-03-26 18:03:32'],
            [11,'Marinel Amiga Vivi',null,'(414) 205-4758',null,0,1,'2025-03-26 17:29:35','2025-03-26 18:16:50'],
            [12,'Fabi Pelo Rojo Vivi',null,null,null,0,1,'2025-03-26 17:44:08','2025-03-29 18:02:03'],
            [13,'Garrido Compañero De Vivi',null,null,null,0,1,'2025-03-26 17:44:20','2025-03-29 18:02:26'],
            [14,'Vivi Tia De Gene',null,null,null,0,1,'2025-03-26 17:44:39','2025-03-29 18:37:32'],
            [15,'Jonathan Ivic',null,null,null,0,1,'2025-03-26 17:44:57','2025-03-26 18:05:06'],
            [16,'Nadian Proyectos Ivic',null,'(414) 282-9638',null,0,1,'2025-03-26 17:45:12','2025-03-29 18:04:32'],
            [17,'Luis Alvarez Proyectos Ivic',null,'(412) 956-2306',null,0,1,'2025-03-26 17:45:25','2025-03-29 18:05:52'],
            [18,'Yulisbeh Proyectos Ivic',null,null,null,0,1,'2025-03-26 17:45:53','2025-03-29 18:06:35'],
            [19,'Yusmely Proyectos Ivic',null,null,null,0,1,'2025-03-26 17:46:00','2025-03-26 18:06:14'],
            [20,'Naile Proyectos Ivic',null,null,null,0,1,'2025-03-26 17:46:12','2025-03-29 18:08:41'],
            [21,'Antonio Proyectos Ivic',null,null,null,0,1,'2025-03-26 17:46:23','2025-03-31 17:11:08'],
            [22,'Yarisbeth Ivic',null,null,null,0,1,'2025-03-26 17:46:33','2025-03-26 18:07:04'],
            [23,'Yoselyn Finanzas Ivic',null,null,null,0,1,'2025-03-26 17:47:11','2025-03-26 18:07:19'],
            [24,'Pedro Compañero De Vivi',null,null,null,0,1,'2025-03-26 18:24:16','2025-03-26 18:25:29'],
            [25,'Ikabaru',null,null,null,0,1,'2025-03-29 17:53:37','2025-03-29 17:53:56'],
            [26,'Edith Ivic',null,null,null,0,1,'2025-03-29 17:54:30','2025-03-29 17:56:05'],
            [27,'Otra Maricel Ivic',null,null,null,0,1,'2025-03-29 17:57:41','2025-03-29 17:58:14'],
            [28,'David Coll Subdirector Ivic',null,null,null,0,1,'2025-03-29 17:58:42','2025-03-29 17:58:59'],
            [29,'Rubelman Compañero De Vivi',null,null,null,0,1,'2025-03-29 18:03:01','2025-03-29 18:03:20'],
            [30,'Ana Victoria Proyectos',null,null,null,0,1,'2025-03-29 18:04:57','2025-03-31 17:30:49'],
            [31,'Michel Rrhh Ivic',null,null,null,0,1,'2025-03-29 18:13:53','2025-03-29 18:16:37'],
            [32,'Feryi Rrhh Ivic',null,null,null,0,1,'2025-03-29 18:14:08','2025-03-29 18:16:51'],
            [33,'Otra Muchacha Rrhh Cumplio Año',null,null,null,0,1,'2025-03-29 18:14:33','2025-03-29 18:17:04'],
            [34,'Mirla Proyectos Ivic',null,null,null,0,1,'2025-03-29 18:14:52','2025-03-29 18:17:22'],
            [35,'Filomena Ivic',null,null,null,0,1,'2025-03-29 18:53:44','2025-03-29 18:53:44']
        ];

        foreach ($customers as $customer) {
            DB::table('customers')->insert([
                'id' => $customer[0],
                'name' => $customer[1],
                'nit_number' => $customer[2],
                'phone' => $customer[3],
                'email' => $customer[4],
                'total_debt' => $customer[5],
                'company_id' => $customer[6],
                'created_at' => $customer[7],
                'updated_at' => $customer[8]
            ]);
        }

        // Ajustar el auto-increment para PostgreSQL
        $this->adjustAutoIncrement('customers');

        $this->command->info('Se han importado ' . count($customers) . ' clientes.');
    }

    /**
     * Ajusta el auto-increment de la tabla para PostgreSQL
     */
    private function adjustAutoIncrement(string $table): void
    {
        if (DB::getDriverName() === 'pgsql') {
            $maxId = DB::table($table)->max('id') ?? 0;
            $nextId = $maxId + 1;
            DB::statement("ALTER SEQUENCE {$table}_id_seq RESTART WITH {$nextId}");
        }
    }
}
