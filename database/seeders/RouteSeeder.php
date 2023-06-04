<?php

namespace Database\Seeders;
use App\Models\Route;
use App\Models\Page;
use App\Models\Category;

use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Default Category
        $category = new Category();
        $category->title = 'Default';
        $category->description = 'Paginas necesarias creadas por el sistema.';
        $category->status = false;
        $category->save();

        // Create Index Page
        $page = new Page();
        $page->title = 'Index Page';
        $page->description = '-';
        $page->category_id = $category->id;
        $page->status = false;
        $page->save();

    }
}
