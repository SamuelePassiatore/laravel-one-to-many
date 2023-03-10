<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use App\Models\Project;
use App\Models\Type;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Faker $faker): void
    {
        $type_ids = Type::select('id')->pluck('id')->toArray();
        $type_ids[] = null;

        for ($i = 0; $i < 50; $i++) {
            $project = new Project();

            $project->type_id = Arr::random($type_ids);
            $project->title = $faker->sentence;
            $project->description = $faker->paragraph();
            // $project->image = "https://picsum.photos/id/" . $faker->numberBetween(1, 50) . "/200";
            $project->slug = Str::slug($project->title, '-');
            $project->url = $faker->url();
            $project->is_public = $faker->boolean();
            $project->save();
        }
    }
}
