<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic railway network data.
     */
    public function run(): void
    {
        // 1. Seed Users (Admin & Passenger)
        $admin = User::updateOrCreate(
            ['email' => 'admin@railway.com'],
            [
                'name' => 'System Administrator',
                'role' => User::ROLE_ADMIN,
                'phone' => '+8801700000001',
                'password' => Hash::make('password'),
            ]
        );

        $passenger = User::updateOrCreate(
            ['email' => 'passenger@railway.com'],
            [
                'name' => 'Demo Passenger',
                'role' => User::ROLE_PASSENGER,
                'phone' => '+8801700000002',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Seed Major Railway Stations
        $stationsData = [
            ['name' => 'Dhaka (Kamalapur)', 'code' => 'DA', 'location' => 'Kamalapur, Dhaka'],
            ['name' => 'Chittagong Railway Station', 'code' => 'CTG', 'location' => 'Station Road, Chittagong'],
            ['name' => 'Sylhet Railway Station', 'code' => 'SYL', 'location' => 'Kadamtoli, Sylhet'],
            ['name' => 'Rajshahi Railway Station', 'code' => 'RAJ', 'location' => 'Shirodail, Rajshahi'],
            ['name' => 'Khulna Railway Station', 'code' => 'KLN', 'location' => 'Station Road, Khulna'],
            ['name' => 'Cox\'s Bazar Iconic Station', 'code' => 'CXB', 'location' => 'Jhilongja, Cox\'s Bazar'],
        ];

        $stations = [];
        foreach ($stationsData as $data) {
            $stations[$data['code']] = Station::updateOrCreate(['code' => $data['code']], $data);
        }

        // 3. Seed Realistic Trains
        $trainsData = [
            [
                'train_number' => '701',
                'train_name' => 'Subarna Express',
                'train_type' => Train::TYPE_INTERCITY,
                'status' => Train::STATUS_ACTIVE,
            ],
            [
                'train_number' => '703',
                'train_name' => 'Mohanagar Provati',
                'train_type' => Train::TYPE_INTERCITY,
                'status' => Train::STATUS_ACTIVE,
            ],
            [
                'train_number' => '709',
                'train_name' => 'Parabat Express',
                'train_type' => Train::TYPE_INTERCITY,
                'status' => Train::STATUS_ACTIVE,
            ],
            [
                'train_number' => '753',
                'train_name' => 'Silk City Express',
                'train_type' => Train::TYPE_INTERCITY,
                'status' => Train::STATUS_ACTIVE,
            ],
            [
                'train_number' => '813',
                'train_name' => 'Cox\'s Bazar Express',
                'train_type' => Train::TYPE_INTERCITY,
                'status' => Train::STATUS_ACTIVE,
            ],
        ];

        $trains = [];
        foreach ($trainsData as $tData) {
            $train = Train::updateOrCreate(['train_number' => $tData['train_number']], $tData);
            $trains[$tData['train_number']] = $train;

            // 4. Seed Seats for each train (40 seats across 3 classes)
            if ($train->seats()->count() === 0) {
                $seatList = [];

                // Coach KA: SNIGDHA (AC Chair) - 10 seats
                for ($i = 1; $i <= 10; $i++) {
                    $seatList[] = [
                        'train_id' => $train->id,
                        'coach' => 'KA',
                        'seat_number' => (string) $i,
                        'seat_class' => Seat::CLASS_SNIGDHA,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Coach KHA: SHOVON_CHAIR - 15 seats
                for ($i = 1; $i <= 15; $i++) {
                    $seatList[] = [
                        'train_id' => $train->id,
                        'coach' => 'KHA',
                        'seat_number' => (string) $i,
                        'seat_class' => Seat::CLASS_SHOVON_CHAIR,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Coach GA: SHOVON_CHAIR - 15 seats
                for ($i = 1; $i <= 15; $i++) {
                    $seatList[] = [
                        'train_id' => $train->id,
                        'coach' => 'GA',
                        'seat_number' => (string) $i,
                        'seat_class' => Seat::CLASS_SHOVON_CHAIR,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Seat::insert($seatList);
                $train->update(['total_seats' => count($seatList)]);
            }
        }

        // 5. Seed Train Schedules (upcoming dates)
        $dates = [
            now()->format('Y-m-d'),
            now()->addDay()->format('Y-m-d'),
            now()->addDays(2)->format('Y-m-d'),
            now()->addDays(3)->format('Y-m-d'),
        ];

        $schedules = [];
        foreach ($dates as $date) {
            // Dhaka -> Chittagong (Subarna Express)
            $schedules[] = TrainSchedule::updateOrCreate(
                [
                    'train_id' => $trains['701']->id,
                    'departure_station_id' => $stations['DA']->id,
                    'arrival_station_id' => $stations['CTG']->id,
                    'journey_date' => $date,
                ],
                [
                    'departure_time' => '07:00:00',
                    'arrival_time' => '12:30:00',
                    'fare' => 420.00,
                    'status' => TrainSchedule::STATUS_SCHEDULED,
                ]
            );

            // Chittagong -> Dhaka (Subarna Express)
            $schedules[] = TrainSchedule::updateOrCreate(
                [
                    'train_id' => $trains['701']->id,
                    'departure_station_id' => $stations['CTG']->id,
                    'arrival_station_id' => $stations['DA']->id,
                    'journey_date' => $date,
                ],
                [
                    'departure_time' => '15:00:00',
                    'arrival_time' => '20:30:00',
                    'fare' => 420.00,
                    'status' => TrainSchedule::STATUS_SCHEDULED,
                ]
            );

            // Dhaka -> Sylhet (Parabat Express)
            $schedules[] = TrainSchedule::updateOrCreate(
                [
                    'train_id' => $trains['709']->id,
                    'departure_station_id' => $stations['DA']->id,
                    'arrival_station_id' => $stations['SYL']->id,
                    'journey_date' => $date,
                ],
                [
                    'departure_time' => '06:20:00',
                    'arrival_time' => '13:00:00',
                    'fare' => 380.00,
                    'status' => TrainSchedule::STATUS_SCHEDULED,
                ]
            );

            // Dhaka -> Cox's Bazar (Cox's Bazar Express)
            $schedules[] = TrainSchedule::updateOrCreate(
                [
                    'train_id' => $trains['813']->id,
                    'departure_station_id' => $stations['DA']->id,
                    'arrival_station_id' => $stations['CXB']->id,
                    'journey_date' => $date,
                ],
                [
                    'departure_time' => '22:30:00',
                    'arrival_time' => '06:45:00',
                    'fare' => 695.00,
                    'status' => TrainSchedule::STATUS_SCHEDULED,
                ]
            );
        }

        // 6. Seed Sample Bookings & Passenger info
        $firstSchedule = $schedules[0];
        $sampleSeat = $trains['701']->seats()->first();

        if ($sampleSeat && $firstSchedule) {
            $booking = Booking::updateOrCreate(
                [
                    'train_schedule_id' => $firstSchedule->id,
                    'seat_id' => $sampleSeat->id,
                ],
                [
                    'user_id' => $passenger->id,
                    'booking_code' => 'BK-DEMO1001',
                    'booking_date' => now(),
                    'total_fare' => $firstSchedule->fare,
                    'status' => Booking::STATUS_CONFIRMED,
                ]
            );

            Passenger::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'name' => 'Rahim Ahmed',
                    'phone' => '+8801711223344',
                    'nid_or_passport' => '1995876543210',
                    'age' => 29,
                    'gender' => Passenger::GENDER_MALE,
                ]
            );
        }
    }
}
