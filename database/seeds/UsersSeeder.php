<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultData=[
            [
                'user_ID'=>Uuid::generate()->string,
                'username'=>'admin',
                'password'=>bcrypt('123'),
                'email'=>'cicctusjr@gmail.com',
                'firstname'=>'Rabsky',
                'lastname'=>'del Rav',
                'revcenter_ID'=>'kjn198uandlajsdnaknlak',
                'learningpath_ID'=>null,
                'gender'=>'Male',
                'isadmin'=>'1',
                'diagnostic'=>'0',
            ],
            [
                'user_ID'=>Uuid::generate()->string,
                'username'=>'chocolatefudge',
                'password'=>bcrypt('123'),
                'email'=>'usc@gmail.com',
                'firstname'=>'Mayormita',
                'lastname'=>'Lalala',
                'revcenter_ID'=>'1239i0anjkdnjnwoi1231',
                'learningpath_ID'=>null,
                'gender'=>'Female',
                'isadmin'=>'1',
                'diagnostic'=>'0',
            ]
            

        ];
        
        DB::table('users')->insert($defaultData);
    }
}
