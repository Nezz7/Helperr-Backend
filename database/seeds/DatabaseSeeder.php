<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $skills = ['"php"','"3D"','"English"','"writing"','"photoshop"','"geometry"','"relationships"','"java"'];
        $helpmeskills= ['"C#"','"frontend"','"french"','"php"','"editing"'];
        $status = ["open","selective"];
        $n =10;
        $scores= array();
        $faker = Faker::create();
        
        for ($x = 1; $x <= $n; $x++){
            $scores[$x]= rand(1,6);
        }
        for ($x = 1; $x <= $n; $x++){
            
            $myskills = array();
            foreach ($skills as $skill) {
                if (rand(0,1)) {array_push($myskills,$skill);}
            }


        DB::table('users')->insert([
            'name' => $faker->name,
            'email' => $x . '@gmail.com',
            'password' => bcrypt('p'),
            'skills' => "[" . implode( ", ", $myskills ) . "]",
            "hp" => rand(3,15),
            "score" => $scores[$x]
        ]);

        }

        for($x = 1; $x <=$n ;$x++){

                for($y = 2; $y <= rand(1,20);$y++){

                    $myskills = array();
                    foreach ($helpmeskills as $skill) {
                        if (rand(0,1)) {array_push($myskills,$skill);}
                    }

                    $mystatus =  $status[array_rand($status)];
                    if (in_array($mystatus,["pending","failed","succeeded"]))
                             {do {
                                  $hid = rand(1,$n);
                              } while($hid == $x);
                    }else $hid=null;
                    
                    $myqueue=array();
                    if ($mystatus=="selective"){
                        for($z=2;$z <= rand(1,20);$z++){
                        do{
                            $hqid = rand(1,$n);
                        }while ($hqid==$x);
                        array_push($myqueue,$hqid);
                        }
                        $myqueue=array_unique($myqueue);
                    }
                    

                    DB::table('help_me_requests')->insert([
                        'short_description' => $faker->realText(rand(10,20)),
                        'skills' => "[" . implode( ", ", $myskills ) . "]",
                        'helper_queue' => "[" . implode( ", ", $myqueue ) . "]",
                        'cost' => rand(1,3),
                        'status' => $mystatus,
                        'maker_id' => $x,
                        'helper_id' => null,
                        'score' => $scores[$x],
                        'title' => $faker->realText(rand(10,16)),
                    ]);
            }
        
        }

    }
    }

