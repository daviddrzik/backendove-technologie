<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
//            CategoriesTableSeeder::class,
//            NotesTableSeeder::class,
//            NoteCategoryTableSeeder::class,
        ]);

        // Načítanie používateľov zo seedera
        $users = User::all();

        // Vytvor 20 poznámok
        $notes = Note::factory(20)->create();

        // Vytvor 10 kategórií
        $categories = Category::factory(10)->create();

        // Pripojenie poznámok ku kategóriám
        $notes->each(function ($note) use ($categories) {
            $note->categories()->attach(
                $categories->random(rand(2, 3))->pluck('id')->toArray()
            );
        });

        // Rozdelíme poznámky medzi používateľov
//        $notes->each(function ($note, $index) use ($users) {
//            // Vyber náhodného používateľa
//            $user = $users[$index % $users->count()];
//
//            // Priradi poznámku používateľovi
//            $user->notes()->save($note);
//        });
    }
}
