<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\State;
use Illuminate\Support\Facades\DB;

class StateTableSeeder extends Seeder
{
    protected $statesArray = [
        ['id' => 1, 'country_id' => 296, 'name' => 'Amazonas', 'iso_3366_2' => 'VE-X', 'category' => 'Estado', 'zoom' => '6.7', 'region' => '', 'latitude_center' => 4.34328979885553, 'longitude_center' => -65.87793406640446, 'temp_state_id_mincyt' => '02'],
        ['id' => 2, 'country_id' => 296, 'name' => 'Anzoátegui', 'iso_3366_2' => 'VE-B', 'category' => 'Estado', 'zoom' => '8', 'region' => '', 'latitude_center' => 9.099049053761691, 'longitude_center' => -64.2081664302623, 'temp_state_id_mincyt' => '03'],
        ['id' => 3, 'country_id' => 296, 'name' => 'Apure', 'iso_3366_2' => 'VE-C', 'category' => 'Estado', 'zoom' => '7.5', 'region' => '', 'latitude_center' => 7.315797283844404, 'longitude_center' => -69.21729563279075, 'temp_state_id_mincyt' => '04'],
        ['id' => 4, 'country_id' => 296, 'name' => 'Aragua', 'iso_3366_2' => 'VE-D', 'category' => 'Estado', 'zoom' => '9', 'region' => '', 'latitude_center' => 10.051515009980042, 'longitude_center' => -67.14893456466858, 'temp_state_id_mincyt' => '05'],
        ['id' => 5, 'country_id' => 296, 'name' => 'Barinas', 'iso_3366_2' => 'VE-E', 'category' => 'Estado', 'zoom' => '8', 'region' => '', 'latitude_center' => 8.293703904058729, 'longitude_center' => -69.72120707288398, 'temp_state_id_mincyt' => '06'],
        ['id' => 6, 'country_id' => 296, 'name' => 'Bolívar', 'iso_3366_2' => 'VE-F', 'category' => 'Estado', 'zoom' => '7', 'region' => '', 'latitude_center' => 6.240181127106679, 'longitude_center' => -63.483118190345365, 'temp_state_id_mincyt' => '07'],
        ['id' => 7, 'country_id' => 296, 'name' => 'Carabobo', 'iso_3366_2' => 'VE-G', 'category' => 'Estado', 'zoom' => '9', 'region' => '', 'latitude_center' => 10.167780718469126, 'longitude_center' => -68.06628210648745, 'temp_state_id_mincyt' => '08'],
        ['id' => 8, 'country_id' => 296, 'name' => 'Cojedes', 'iso_3366_2' => 'VE-H', 'category' => 'Estado', 'zoom' => '8.8', 'region' => '', 'latitude_center' => 9.404523227775739, 'longitude_center' => -68.38214465557358, 'temp_state_id_mincyt' => '09'],
        ['id' => 9, 'country_id' => 296, 'name' => 'Delta Amacuro', 'iso_3366_2' => 'VE-Y', 'category' => 'Estado', 'zoom' => '8', 'region' => '', 'latitude_center' => 8.912872796719038, 'longitude_center' => -61.36060313370347, 'temp_state_id_mincyt' => '10'],
        ['id' => 10, 'country_id' => 296, 'name' => 'Falcón', 'iso_3366_2' => 'VE-I', 'category' => 'Estado', 'zoom' => '8.2', 'region' => '', 'latitude_center' => 11.152115326128591, 'longitude_center' => -69.88599600490045, 'temp_state_id_mincyt' => '11'],
        ['id' => 11, 'country_id' => 296, 'name' => 'Guárico', 'iso_3366_2' => 'VE-J', 'category' => 'Estado', 'zoom' => '8', 'region' => '', 'latitude_center' => 8.880311174904532, 'longitude_center' => -66.60107865439922, 'temp_state_id_mincyt' => '12'],
        ['id' => 12, 'country_id' => 296, 'name' => 'Lara', 'iso_3366_2' => 'VE-K', 'category' => 'Estado', 'zoom' => '9', 'region' => '', 'latitude_center' => 10.153379942838153, 'longitude_center' => -69.75319139496216, 'temp_state_id_mincyt' => '13'],
        ['id' => 13, 'country_id' => 296, 'name' => 'Mérida', 'iso_3366_2' => 'VE-L', 'category' => 'Estado', 'zoom' => '8', 'region' => '', 'latitude_center' => 8.516471068595997, 'longitude_center' => -71.21985973012148, 'temp_state_id_mincyt' => '14'],
        ['id' => 14, 'country_id' => 296, 'name' => 'Miranda', 'iso_3366_2' => 'VE-M', 'category' => 'Estado', 'zoom' => '9', 'region' => '', 'latitude_center' => 10.364188144252429, 'longitude_center' => -66.4243186240746, 'temp_state_id_mincyt' => '15'],
        ['id' => 15, 'country_id' => 296, 'name' => 'Monagas', 'iso_3366_2' => 'VE-N', 'category' => 'Estado', 'zoom' => '8', 'region' => '', 'latitude_center' => 9.514714890753556, 'longitude_center' => -62.91426839995992, 'temp_state_id_mincyt' => '16'],
        ['id' => 16, 'country_id' => 296, 'name' => 'Nueva Esparta', 'iso_3366_2' => 'VE-O', 'category' => 'Estado', 'zoom' => '10.7', 'region' => '', 'latitude_center' => 10.96022545050653, 'longitude_center' => -64.02719780001561, 'temp_state_id_mincyt' => '17'],
        ['id' => 17, 'country_id' => 296, 'name' => 'Portuguesa', 'iso_3366_2' => 'VE-P', 'category' => 'Estado', 'zoom' => '8.7', 'region' => '', 'latitude_center' => 9.01051206876335, 'longitude_center' => -69.25880475750635, 'temp_state_id_mincyt' => '18'],
        ['id' => 18, 'country_id' => 296, 'name' => 'Sucre', 'iso_3366_2' => 'VE-R', 'category' => 'Estado', 'zoom' => '8.9', 'region' => '', 'latitude_center' => 10.485448798372925, 'longitude_center' => -63.41404450205198, 'temp_state_id_mincyt' => '19'],
        ['id' => 19, 'country_id' => 296, 'name' => 'Táchira', 'iso_3366_2' => 'VE-S', 'category' => 'Estado', 'zoom' => '9', 'region' => '', 'latitude_center' => 7.941079425553923, 'longitude_center' => -72.01859231058509, 'temp_state_id_mincyt' => '20'],
        ['id' => 20, 'country_id' => 296, 'name' => 'Trujillo', 'iso_3366_2' => 'VE-T', 'category' => 'Estado', 'zoom' => '9', 'region' => '', 'latitude_center' => 9.434325246417801, 'longitude_center' => -70.51624151441797, 'temp_state_id_mincyt' => '21'],
        ['id' => 21, 'country_id' => 296, 'name' => 'La Guaira', 'iso_3366_2' => 'VE-W', 'category' => 'Estado', 'zoom' => '10', 'region' => '', 'latitude_center' => 10.591938380528376, 'longitude_center' => -66.82758240799845, 'temp_state_id_mincyt' => '24'],
        ['id' => 22, 'country_id' => 296, 'name' => 'Yaracuy', 'iso_3366_2' => 'VE-U', 'category' => 'Estado', 'zoom' => '9', 'region' => '', 'latitude_center' => 10.278602856006819, 'longitude_center' => -68.733708091489, 'temp_state_id_mincyt' => '22'],
        ['id' => 23, 'country_id' => 296, 'name' => 'Zulia', 'iso_3366_2' => 'VE-V', 'category' => 'Estado', 'zoom' => '7.5', 'region' => '', 'latitude_center' => 9.747613546942501, 'longitude_center' => -71.86355453444074, 'temp_state_id_mincyt' => '23'],
        ['id' => 24, 'country_id' => 296, 'name' => 'Distrito Capital', 'iso_3366_2' => 'VE-A', 'category' => 'Estado', 'zoom' => '12', 'region' => '', 'latitude_center' => 10.471164171498925, 'longitude_center' => -66.99085235595705, 'temp_state_id_mincyt' => '01'],
        ['id' => 25, 'country_id' => 296, 'name' => 'Esequibo', 'iso_3366_2' => 'VE-Z', 'category' => 'Estado', 'zoom' => '6.5', 'region' => '', 'latitude_center' => 5.137278050194342, 'longitude_center' => -59.46197235013147, 'temp_state_id_mincyt' => '26'],
        ['id' => 26, 'country_id' => 296, 'name' => 'Dependencia Federal', 'iso_3366_2' => 'VE-A', 'category' => 'Estado', 'zoom' => '12', 'region' => '', 'latitude_center' => 10.471164171498925, 'longitude_center' => -66.99085235595705, 'temp_state_id_mincyt' => '25'],
       
        //Colombia
        ['id' => 1425, 'country_id' => 142, 'name' => 'Antioquia', 'iso_3366_2' => 'CO-ANT', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Eje Cafetero - Antioquia'],
        ['id' => 1428, 'country_id' => 142, 'name' => 'Atlántico', 'iso_3366_2' => 'CO-ATL', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Caribe'],
        ['id' => 14211, 'country_id' => 142, 'name' => 'Bogotá D.C.', 'iso_3366_2' => 'CO-DC', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Centro Oriente'],
        ['id' => 14213, 'country_id' => 142, 'name' => 'Bolívar', 'iso_3366_2' => 'CO-BOL', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Caribe'],
        ['id' => 14215, 'country_id' => 142, 'name' => 'Boyacá', 'iso_3366_2' => 'CO-BOY', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Centro Oriente'],
        ['id' => 14217, 'country_id' => 142, 'name' => 'Caldas', 'iso_3366_2' => 'CO-CAL', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Eje Cafetero - Antioquia'],
        ['id' => 14218, 'country_id' => 142, 'name' => 'Caquetá', 'iso_3366_2' => 'CO-CAQ', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Centro Sur'],
        ['id' => 14219, 'country_id' => 142, 'name' => 'Cauca', 'iso_3366_2' => 'CO-CAU', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Pacífico'],
        ['id' => 14220, 'country_id' => 142, 'name' => 'Cesar', 'iso_3366_2' => 'CO-CES', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Caribe'],
        ['id' => 14223, 'country_id' => 142, 'name' => 'Córdoba', 'iso_3366_2' => 'CO-COR', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Caribe'],
        ['id' => 14225, 'country_id' => 142, 'name' => 'Cundinamarca', 'iso_3366_2' => 'CO-CUN', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Centro Oriente'],
        ['id' => 14227, 'country_id' => 142, 'name' => 'Chocó', 'iso_3366_2' => 'CO-CHO', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Pacífico'],
        ['id' => 14241, 'country_id' => 142, 'name' => 'Huila', 'iso_3366_2' => 'CO-HUI', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Centro Sur'],
        ['id' => 14244, 'country_id' => 142, 'name' => 'La Guajira', 'iso_3366_2' => 'CO-LAG', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Caribe'],
        ['id' => 14247, 'country_id' => 142, 'name' => 'Magdalena', 'iso_3366_2' => 'CO-MAG', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Caribe'],
        ['id' => 14250, 'country_id' => 142, 'name' => 'Meta', 'iso_3366_2' => 'CO-MET', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Llano'],
        ['id' => 14252, 'country_id' => 142, 'name' => 'Nariño', 'iso_3366_2' => 'CO-NAR', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Pacífico'],
        ['id' => 14254, 'country_id' => 142, 'name' => 'Norte de Santander', 'iso_3366_2' => 'CO-NSA', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Centro Oriente'],
        ['id' => 14263, 'country_id' => 142, 'name' => 'Quindío', 'iso_3366_2' => 'CO-QUI', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Eje Cafetero - Antioquia'],
        ['id' => 14266, 'country_id' => 142, 'name' => 'Risaralda', 'iso_3366_2' => 'CO-RIS', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Eje Cafetero - Antioquia'],
        ['id' => 14268, 'country_id' => 142, 'name' => 'Santander', 'iso_3366_2' => 'CO-SAN', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Centro Oriente'],
        ['id' => 14270, 'country_id' => 142, 'name' => 'Sucre', 'iso_3366_2' => 'CO-SUC', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Caribe'],
        ['id' => 14273, 'country_id' => 142, 'name' => 'Tolima', 'iso_3366_2' => 'CO-TOL', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Centro Sur'],
        ['id' => 14276, 'country_id' => 142, 'name' => 'Valle del Cauca', 'iso_3366_2' => 'CO-VAC', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Pacífico'],
        ['id' => 14281, 'country_id' => 142, 'name' => 'Arauca', 'iso_3366_2' => 'CO-ARA', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Llano'],
        ['id' => 14285, 'country_id' => 142, 'name' => 'Casanare', 'iso_3366_2' => 'CO-CAS', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Llano'],
        ['id' => 14286, 'country_id' => 142, 'name' => 'Putumayo', 'iso_3366_2' => 'CO-PUT', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Centro Sur'],
        ['id' => 14288, 'country_id' => 142, 'name' => 'Archipiélago de San Andrés, Providencia y Santa Catalina', 'iso_3366_2' => 'CO-SAP', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Caribe'],
        ['id' => 14291, 'country_id' => 142, 'name' => 'Amazonas', 'iso_3366_2' => 'CO-AMA', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Centro Sur'],
        ['id' => 14294, 'country_id' => 142, 'name' => 'Guainía', 'iso_3366_2' => 'CO-GUA', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Llano'],
        ['id' => 14295, 'country_id' => 142, 'name' => 'Guaviare', 'iso_3366_2' => 'CO-GUV', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Llano'],
        ['id' => 14297, 'country_id' => 142, 'name' => 'Vaupés', 'iso_3366_2' => 'CO-VAU', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Llano'],
        ['id' => 14299, 'country_id' => 142, 'name' => 'Vichada', 'iso_3366_2' => 'CO-VID', 'category' => 'Departamento', 'zoom' => '9', 'region' => 'Región Llano'],
        
        //mexico
        ['id' => 22601, 'country_id' => 226, 'name' => 'Aguascalientes', 'iso_3366_2' => 'MX-AGU', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22602, 'country_id' => 226, 'name' => 'Baja California', 'iso_3366_2' => 'MX-BCN', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22603, 'country_id' => 226, 'name' => 'Baja California Sur', 'iso_3366_2' => 'MX-BCS', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22604, 'country_id' => 226, 'name' => 'Campeche', 'iso_3366_2' => 'MX-CAM', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22605, 'country_id' => 226, 'name' => 'Coahuila de Zaragoza', 'iso_3366_2' => 'MX-COA', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22606, 'country_id' => 226, 'name' => 'Colima', 'iso_3366_2' => 'MX-COL', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22607, 'country_id' => 226, 'name' => 'Chiapas', 'iso_3366_2' => 'MX-CHP', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22608, 'country_id' => 226, 'name' => 'Chihuahua', 'iso_3366_2' => 'MX-CHH', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22609, 'country_id' => 226, 'name' => 'Ciudad de México', 'iso_3366_2' => 'MX-CMX', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22610, 'country_id' => 226, 'name' => 'Durango', 'iso_3366_2' => 'MX-DUR', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22611, 'country_id' => 226, 'name' => 'Guanajuato', 'iso_3366_2' => 'MX-GUA', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22612, 'country_id' => 226, 'name' => 'Guerrero', 'iso_3366_2' => 'MX-GRO', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22613, 'country_id' => 226, 'name' => 'Hidalgo', 'iso_3366_2' => 'MX-HID', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22614, 'country_id' => 226, 'name' => 'Jalisco', 'iso_3366_2' => 'MX-JAL', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22615, 'country_id' => 226, 'name' => 'México', 'iso_3366_2' => 'MX-MEX', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22616, 'country_id' => 226, 'name' => 'Michoacán de Ocampo', 'iso_3366_2' => 'MX-MIC', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22617, 'country_id' => 226, 'name' => 'Morelos', 'iso_3366_2' => 'MX-MOR', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22618, 'country_id' => 226, 'name' => 'Nayarit', 'iso_3366_2' => 'MX-NAY', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22619, 'country_id' => 226, 'name' => 'Nuevo León', 'iso_3366_2' => 'MX-NLE', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22620, 'country_id' => 226, 'name' => 'Oaxaca', 'iso_3366_2' => 'MX-OAX', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22621, 'country_id' => 226, 'name' => 'Puebla', 'iso_3366_2' => 'MX-PUE', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22622, 'country_id' => 226, 'name' => 'Querétaro', 'iso_3366_2' => 'MX-QUE', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22623, 'country_id' => 226, 'name' => 'Quintana Roo', 'iso_3366_2' => 'MX-ROO', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22624, 'country_id' => 226, 'name' => 'San Luis Potosí', 'iso_3366_2' => 'MX-SLP', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22625, 'country_id' => 226, 'name' => 'Sinaloa', 'iso_3366_2' => 'MX-SIN', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22626, 'country_id' => 226, 'name' => 'Sonora', 'iso_3366_2' => 'MX-SON', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22627, 'country_id' => 226, 'name' => 'Tabasco', 'iso_3366_2' => 'MX-TAB', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22628, 'country_id' => 226, 'name' => 'Tamaulipas', 'iso_3366_2' => 'MX-TAM', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22629, 'country_id' => 226, 'name' => 'Tlaxcala', 'iso_3366_2' => 'MX-TLA', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22630, 'country_id' => 226, 'name' => 'Veracruz de Ignacio de la Llave', 'iso_3366_2' => 'MX-VER', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22631, 'country_id' => 226, 'name' => 'Yucatán', 'iso_3366_2' => 'MX-YUC', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22632, 'country_id' => 226, 'name' => 'Zacatecas', 'iso_3366_2' => 'MX-ZAC', 'category' => '', 'zoom' => '9', 'region' => ''],
    
        //España
        ['id' => 22633, 'country_id' => 161, 'name' => 'Albacete', 'iso_3366_2' => 'ES-AB', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22634, 'country_id' => 161, 'name' => 'Alicante/Alacant', 'iso_3366_2' => 'ES-A', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22635, 'country_id' => 161, 'name' => 'Almería', 'iso_3366_2' => 'ES-AL', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22636, 'country_id' => 161, 'name' => 'Araba/Álava', 'iso_3366_2' => 'ES-VI', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22637, 'country_id' => 161, 'name' => 'Asturias', 'iso_3366_2' => 'ES-O', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22638, 'country_id' => 161, 'name' => 'Ávila', 'iso_3366_2' => 'ES-AV', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22639, 'country_id' => 161, 'name' => 'Badajoz', 'iso_3366_2' => 'ES-BA', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22640, 'country_id' => 161, 'name' => 'Balears, Illes', 'iso_3366_2' => 'ES-IB', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22641, 'country_id' => 161, 'name' => 'Barcelona', 'iso_3366_2' => 'ES-B', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22642, 'country_id' => 161, 'name' => 'Bizkaia', 'iso_3366_2' => 'ES-BI', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22643, 'country_id' => 161, 'name' => 'Burgos', 'iso_3366_2' => 'ES-BU', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22644, 'country_id' => 161, 'name' => 'Cáceres', 'iso_3366_2' => 'ES-CC', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22645, 'country_id' => 161, 'name' => 'Cádiz', 'iso_3366_2' => 'ES-CA', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22646, 'country_id' => 161, 'name' => 'Cantabria', 'iso_3366_2' => 'ES-S', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22647, 'country_id' => 161, 'name' => 'Castellón/Castelló', 'iso_3366_2' => 'ES-CS', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22648, 'country_id' => 161, 'name' => 'Ciudad Real', 'iso_3366_2' => 'ES-CR', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22649, 'country_id' => 161, 'name' => 'Córdoba', 'iso_3366_2' => 'ES-CO', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22650, 'country_id' => 161, 'name' => 'Coruña, A', 'iso_3366_2' => 'ES-C', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22651, 'country_id' => 161, 'name' => 'Cuenca', 'iso_3366_2' => 'ES-CU', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22652, 'country_id' => 161, 'name' => 'Gipuzkoa', 'iso_3366_2' => 'ES-SS', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22653, 'country_id' => 161, 'name' => 'Girona', 'iso_3366_2' => 'ES-GI', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22654, 'country_id' => 161, 'name' => 'Granada', 'iso_3366_2' => 'ES-GR', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22655, 'country_id' => 161, 'name' => 'Guadalajara', 'iso_3366_2' => 'ES-GU', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22656, 'country_id' => 161, 'name' => 'Huelva', 'iso_3366_2' => 'ES-H', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22657, 'country_id' => 161, 'name' => 'Huesca', 'iso_3366_2' => 'ES-HU', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22658, 'country_id' => 161, 'name' => 'Jaén', 'iso_3366_2' => 'ES-J', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22659, 'country_id' => 161, 'name' => 'León', 'iso_3366_2' => 'ES-LE', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22660, 'country_id' => 161, 'name' => 'Lleida', 'iso_3366_2' => 'ES-L', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22661, 'country_id' => 161, 'name' => 'Lugo', 'iso_3366_2' => 'ES-LU', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22662, 'country_id' => 161, 'name' => 'Madrid', 'iso_3366_2' => 'ES-M', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22663, 'country_id' => 161, 'name' => 'Málaga', 'iso_3366_2' => 'ES-MA', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22664, 'country_id' => 161, 'name' => 'Murcia', 'iso_3366_2' => 'ES-MU', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22665, 'country_id' => 161, 'name' => 'Navarra', 'iso_3366_2' => 'ES-NC', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22666, 'country_id' => 161, 'name' => 'Ourense', 'iso_3366_2' => 'ES-OR', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22667, 'country_id' => 161, 'name' => 'Palencia', 'iso_3366_2' => 'ES-P', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22668, 'country_id' => 161, 'name' => 'Palmas, Las', 'iso_3366_2' => 'ES-GC', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22669, 'country_id' => 161, 'name' => 'Pontevedra', 'iso_3366_2' => 'ES-PO', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22670, 'country_id' => 161, 'name' => 'Rioja, La', 'iso_3366_2' => 'ES-LO', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22671, 'country_id' => 161, 'name' => 'Salamanca', 'iso_3366_2' => 'ES-SA', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22672, 'country_id' => 161, 'name' => 'Santa Cruz de Tenerife', 'iso_3366_2' => 'ES-TF', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22673, 'country_id' => 161, 'name' => 'Segovia', 'iso_3366_2' => 'ES-SG', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22674, 'country_id' => 161, 'name' => 'Sevilla', 'iso_3366_2' => 'ES-SE', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22675, 'country_id' => 161, 'name' => 'Soria', 'iso_3366_2' => 'ES-SO', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22676, 'country_id' => 161, 'name' => 'Tarragona', 'iso_3366_2' => 'ES-T', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22677, 'country_id' => 161, 'name' => 'Teruel', 'iso_3366_2' => 'ES-TE', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22678, 'country_id' => 161, 'name' => 'Toledo', 'iso_3366_2' => 'ES-TO', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22679, 'country_id' => 161, 'name' => 'Valencia/València', 'iso_3366_2' => 'ES-V', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22680, 'country_id' => 161, 'name' => 'Valladolid', 'iso_3366_2' => 'ES-VA', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22681, 'country_id' => 161, 'name' => 'Zamora', 'iso_3366_2' => 'ES-ZA', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22682, 'country_id' => 161, 'name' => 'Zaragoza', 'iso_3366_2' => 'ES-Z', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22683, 'country_id' => 161, 'name' => 'Ceuta', 'iso_3366_2' => 'ES-CE', 'category' => '', 'zoom' => '9', 'region' => ''],
        ['id' => 22684, 'country_id' => 161, 'name' => 'Melilla', 'iso_3366_2' => 'ES-ML', 'category' => '', 'zoom' => '9', 'region' => ''],       
        
        // Estados Unidos
        ['id' => 30001, 'country_id' => 162, 'name' => 'Alabama', 'iso_3366_2' => 'US-AL', 'category' => 'State', 'zoom' => '7', 'region' => 'South'],
        ['id' => 30002, 'country_id' => 162, 'name' => 'Alaska', 'iso_3366_2' => 'US-AK', 'category' => 'State', 'zoom' => '4', 'region' => 'West'],
        ['id' => 30003, 'country_id' => 162, 'name' => 'Arizona', 'iso_3366_2' => 'US-AZ', 'category' => 'State', 'zoom' => '7', 'region' => 'West'],
        ['id' => 30004, 'country_id' => 162, 'name' => 'Arkansas', 'iso_3366_2' => 'US-AR', 'category' => 'State', 'zoom' => '7', 'region' => 'South'],
        ['id' => 30005, 'country_id' => 162, 'name' => 'California', 'iso_3366_2' => 'US-CA', 'category' => 'State', 'zoom' => '6', 'region' => 'West'],
        ['id' => 30006, 'country_id' => 162, 'name' => 'Colorado', 'iso_3366_2' => 'US-CO', 'category' => 'State', 'zoom' => '7', 'region' => 'West'],
        ['id' => 30007, 'country_id' => 162, 'name' => 'Connecticut', 'iso_3366_2' => 'US-CT', 'category' => 'State', 'zoom' => '9', 'region' => 'Northeast'],
        ['id' => 30008, 'country_id' => 162, 'name' => 'Delaware', 'iso_3366_2' => 'US-DE', 'category' => 'State', 'zoom' => '9', 'region' => 'Northeast'],
        ['id' => 30009, 'country_id' => 162, 'name' => 'Florida', 'iso_3366_2' => 'US-FL', 'category' => 'State', 'zoom' => '7', 'region' => 'South'],
        ['id' => 30010, 'country_id' => 162, 'name' => 'Georgia', 'iso_3366_2' => 'US-GA', 'category' => 'State', 'zoom' => '7', 'region' => 'South'],
        ['id' => 30011, 'country_id' => 162, 'name' => 'Hawaii', 'iso_3366_2' => 'US-HI', 'category' => 'State', 'zoom' => '7', 'region' => 'West'],
        ['id' => 30012, 'country_id' => 162, 'name' => 'Idaho', 'iso_3366_2' => 'US-ID', 'category' => 'State', 'zoom' => '7', 'region' => 'West'],
        ['id' => 30013, 'country_id' => 162, 'name' => 'Illinois', 'iso_3366_2' => 'US-IL', 'category' => 'State', 'zoom' => '7', 'region' => 'Midwest'],
        ['id' => 30014, 'country_id' => 162, 'name' => 'Indiana', 'iso_3366_2' => 'US-IN', 'category' => 'State', 'zoom' => '7', 'region' => 'Midwest'],
        ['id' => 30015, 'country_id' => 162, 'name' => 'Iowa', 'iso_3366_2' => 'US-IA', 'category' => 'State', 'zoom' => '7', 'region' => 'Midwest'],
        ['id' => 30016, 'country_id' => 162, 'name' => 'Kansas', 'iso_3366_2' => 'US-KS', 'category' => 'State', 'zoom' => '7', 'region' => 'Midwest'],
        ['id' => 30017, 'country_id' => 162, 'name' => 'Kentucky', 'iso_3366_2' => 'US-KY', 'category' => 'State', 'zoom' => '7', 'region' => 'South'],
        ['id' => 30018, 'country_id' => 162, 'name' => 'Louisiana', 'iso_3366_2' => 'US-LA', 'category' => 'State', 'zoom' => '7', 'region' => 'South'],
        ['id' => 30019, 'country_id' => 162, 'name' => 'Maine', 'iso_3366_2' => 'US-ME', 'category' => 'State', 'zoom' => '7', 'region' => 'Northeast'],
        ['id' => 30020, 'country_id' => 162, 'name' => 'Maryland', 'iso_3366_2' => 'US-MD', 'category' => 'State', 'zoom' => '8', 'region' => 'Northeast'],
        ['id' => 30021, 'country_id' => 162, 'name' => 'Massachusetts', 'iso_3366_2' => 'US-MA', 'category' => 'State', 'zoom' => '8', 'region' => 'Northeast'],
        ['id' => 30022, 'country_id' => 162, 'name' => 'Michigan', 'iso_3366_2' => 'US-MI', 'category' => 'State', 'zoom' => '7', 'region' => 'Midwest'],
        ['id' => 30023, 'country_id' => 162, 'name' => 'Minnesota', 'iso_3366_2' => 'US-MN', 'category' => 'State', 'zoom' => '7', 'region' => 'Midwest'],
        ['id' => 30024, 'country_id' => 162, 'name' => 'Mississippi', 'iso_3366_2' => 'US-MS', 'category' => 'State', 'zoom' => '7', 'region' => 'South'],
        ['id' => 30025, 'country_id' => 162, 'name' => 'Missouri', 'iso_3366_2' => 'US-MO', 'category' => 'State', 'zoom' => '7', 'region' => 'Midwest'],
        ['id' => 30026, 'country_id' => 162, 'name' => 'Montana', 'iso_3366_2' => 'US-MT', 'category' => 'State', 'zoom' => '7', 'region' => 'West'],
        ['id' => 30027, 'country_id' => 162, 'name' => 'Nebraska', 'iso_3366_2' => 'US-NE', 'category' => 'State', 'zoom' => '7', 'region' => 'Midwest'],
        ['id' => 30028, 'country_id' => 162, 'name' => 'Nevada', 'iso_3366_2' => 'US-NV', 'category' => 'State', 'zoom' => '7', 'region' => 'West'],
        ['id' => 30029, 'country_id' => 162, 'name' => 'New Hampshire', 'iso_3366_2' => 'US-NH', 'category' => 'State', 'zoom' => '8', 'region' => 'Northeast'],
        ['id' => 30030, 'country_id' => 162, 'name' => 'New Jersey', 'iso_3366_2' => 'US-NJ', 'category' => 'State', 'zoom' => '8', 'region' => 'Northeast'],
        ['id' => 30031, 'country_id' => 162, 'name' => 'New Mexico', 'iso_3366_2' => 'US-NM', 'category' => 'State', 'zoom' => '7', 'region' => 'West'],
        ['id' => 30032, 'country_id' => 162, 'name' => 'New York', 'iso_3366_2' => 'US-NY', 'category' => 'State', 'zoom' => '7', 'region' => 'Northeast'],
        ['id' => 30033, 'country_id' => 162, 'name' => 'North Carolina', 'iso_3366_2' => 'US-NC', 'category' => 'State', 'zoom' => '7', 'region' => 'South'],
        ['id' => 30034, 'country_id' => 162, 'name' => 'North Dakota', 'iso_3366_2' => 'US-ND', 'category' => 'State', 'zoom' => '7', 'region' => 'Midwest'],
        ['id' => 30035, 'country_id' => 162, 'name' => 'Ohio', 'iso_3366_2' => 'US-OH', 'category' => 'State', 'zoom' => '7', 'region' => 'Midwest'],
        ['id' => 30036, 'country_id' => 162, 'name' => 'Oklahoma', 'iso_3366_2' => 'US-OK', 'category' => 'State', 'zoom' => '7', 'region' => 'South'],
        ['id' => 30037, 'country_id' => 162, 'name' => 'Oregon', 'iso_3366_2' => 'US-OR', 'category' => 'State', 'zoom' => '7', 'region' => 'West'],
        ['id' => 30038, 'country_id' => 162, 'name' => 'Pennsylvania', 'iso_3366_2' => 'US-PA', 'category' => 'State', 'zoom' => '7', 'region' => 'Northeast'],
        ['id' => 30039, 'country_id' => 162, 'name' => 'Rhode Island', 'iso_3366_2' => 'US-RI', 'category' => 'State', 'zoom' => '9', 'region' => 'Northeast'],
        ['id' => 30040, 'country_id' => 162, 'name' => 'South Carolina', 'iso_3366_2' => 'US-SC', 'category' => 'State', 'zoom' => '8', 'region' => 'South'],
        ['id' => 30041, 'country_id' => 162, 'name' => 'South Dakota', 'iso_3366_2' => 'US-SD', 'category' => 'State', 'zoom' => '7', 'region' => 'Midwest'],
        ['id' => 30042, 'country_id' => 162, 'name' => 'Tennessee', 'iso_3366_2' => 'US-TN', 'category' => 'State', 'zoom' => '7', 'region' => 'South'],
        ['id' => 30043, 'country_id' => 162, 'name' => 'Texas', 'iso_3366_2' => 'US-TX', 'category' => 'State', 'zoom' => '6', 'region' => 'South'],
        ['id' => 30044, 'country_id' => 162, 'name' => 'Utah', 'iso_3366_2' => 'US-UT', 'category' => 'State', 'zoom' => '7', 'region' => 'West'],
        ['id' => 30045, 'country_id' => 162, 'name' => 'Vermont', 'iso_3366_2' => 'US-VT', 'category' => 'State', 'zoom' => '8', 'region' => 'Northeast'],
        ['id' => 30046, 'country_id' => 162, 'name' => 'Virginia', 'iso_3366_2' => 'US-VA', 'category' => 'State', 'zoom' => '7', 'region' => 'South'],
        ['id' => 30047, 'country_id' => 162, 'name' => 'Washington', 'iso_3366_2' => 'US-WA', 'category' => 'State', 'zoom' => '7', 'region' => 'West'],
        ['id' => 30048, 'country_id' => 162, 'name' => 'West Virginia', 'iso_3366_2' => 'US-WV', 'category' => 'State', 'zoom' => '8', 'region' => 'South'],
        ['id' => 30049, 'country_id' => 162, 'name' => 'Wisconsin', 'iso_3366_2' => 'US-WI', 'category' => 'State', 'zoom' => '7', 'region' => 'Midwest'],
        ['id' => 30050, 'country_id' => 162, 'name' => 'Wyoming', 'iso_3366_2' => 'US-WY', 'category' => 'State', 'zoom' => '7', 'region' => 'West'],
        ['id' => 30051, 'country_id' => 162, 'name' => 'District of Columbia', 'iso_3366_2' => 'US-DC', 'category' => 'Federal District', 'zoom' => '10', 'region' => 'Northeast'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->statesArray as $value) {
            State::create($value);
        }
        $this->adjustAutoIncrement('states');
    }

    protected function adjustAutoIncrement(string $table)
    {
        $maxId = DB::table($table)->max('id') + 1;

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $sequenceName = $table . '_id_seq';
            DB::statement("SELECT setval('$sequenceName', $maxId)");
        } elseif ($driver === 'mysql' or $driver === 'mariadb') {
            DB::statement("ALTER TABLE $table AUTO_INCREMENT = $maxId");
        } elseif ($driver === 'sqlite') {
            DB::statement("UPDATE sqlite_sequence SET seq = $maxId WHERE name = '$table'");
        } else {
            throw new \Exception('Unsupported database driver: ' . $driver);
        }
    }
}
