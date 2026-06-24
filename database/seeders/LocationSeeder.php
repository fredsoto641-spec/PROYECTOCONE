<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            'Amazonas' => ['Chachapoyas'],
            'Áncash' => ['Chimbote', 'Huaraz', 'Nuevo Chimbote'],
            'Arequipa' => ['Alto Selva Alegre', 'Arequipa', 'Cayma', 'Cerro Colorado', 'José Luis Bustamante y Rivero', 'Miraflores', 'Paucarpata', 'Sachaca', 'Yanahuara'],
            'Ayacucho' => ['Ayacucho'],
            'Cajamarca' => ['Cajamarca', 'Jaén'],
            'Callao' => ['Bellavista', 'Callao', 'Carmen de la Legua Reynoso', 'La Perla', 'La Punta', 'Mi Perú', 'Ventanilla'],
            'Cusco' => ['Cusco', 'San Jerónimo', 'San Sebastián', 'Santiago', 'Wanchaq'],
            'Huancavelica' => ['Huancavelica'],
            'Huánuco' => ['Amarilis', 'Huánuco', 'Pillco Marca'],
            'Ica' => ['Chincha Alta', 'Ica', 'Pisco'],
            'Junín' => ['Chilca', 'El Tambo', 'Huancayo'],
            'La Libertad' => ['El Porvenir', 'Florencia de Mora', 'Huanchaco', 'La Esperanza', 'Trujillo', 'Víctor Larco Herrera'],
            'Lambayeque' => ['Chiclayo', 'José Leonardo Ortiz', 'La Victoria', 'Lambayeque'],
            'Lima' => [
                'Ancón',
                'Ate',
                'Barranco',
                'Breña',
                'Carabayllo',
                'Chaclacayo',
                'Chorrillos',
                'Cieneguilla',
                'Comas',
                'El Agustino',
                'Independencia',
                'Jesús María',
                'La Molina',
                'La Victoria',
                'Lima',
                'Lince',
                'Los Olivos',
                'Lurín',
                'Magdalena del Mar',
                'Miraflores',
                'Pachacámac',
                'Pueblo Libre',
                'Puente Piedra',
                'Rímac',
                'San Borja',
                'San Isidro',
                'San Juan de Lurigancho',
                'San Juan de Miraflores',
                'San Luis',
                'San Martín de Porres',
                'San Miguel',
                'Santa Anita',
                'Santiago de Surco',
                'Surquillo',
                'Villa El Salvador',
                'Villa María del Triunfo',
            ],
            'Loreto' => ['Belén', 'Iquitos', 'Punchana', 'San Juan Bautista'],
            'Madre de Dios' => ['Tambopata'],
            'Moquegua' => ['Ilo', 'Moquegua'],
            'Pasco' => ['Chaupimarca'],
            'Piura' => ['Castilla', 'Catacaos', 'Piura', 'Sullana', 'Veintiséis de Octubre'],
            'Puno' => ['Juliaca', 'Puno'],
            'San Martín' => ['Moyobamba', 'Tarapoto'],
            'Tacna' => ['Alto de la Alianza', 'Ciudad Nueva', 'Gregorio Albarracín Lanchipa', 'Pocollay', 'Tacna'],
            'Tumbes' => ['Tumbes'],
            'Ucayali' => ['Callería', 'Manantay', 'Yarinacocha'],
        ];

        $sortOrder = 0;

        foreach ($locations as $department => $districts) {
            foreach ($districts as $district) {
                Location::query()->updateOrCreate(
                    ['name' => $district],
                    [
                        'department' => $department,
                        'sort_order' => $sortOrder++,
                    ],
                );
            }
        }
    }
}
