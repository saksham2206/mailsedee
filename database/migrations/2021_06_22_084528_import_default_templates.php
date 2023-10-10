<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Acelle\Model\TemplateCategory;
use Acelle\Model\Template;

class ImportDefaultTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Clean up
        Template::shared()->delete();
        TemplateCategory::query()->delete();

        // Cateogries
        $categoryBasic = TemplateCategory::create(['name' => 'Basic']);
        $categoryFeatured = TemplateCategory::create(['name' => 'Featured']);
        $categoryTheme = TemplateCategory::create(['name' => 'Themes']);
        $categoryWoo = TemplateCategory::create(['name' => 'WooCommerce']);

        // Default templates
        $templates = [
            [
                'name' => 'Blank',
                'dir' => database_path('templates/basic/6037a0a8583a7'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => 'Pricing Table',
                'dir' => database_path('templates/basic/6037a2135b974'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => 'Listing & Tables',
                'dir' => database_path('templates/basic/6037a2250a3a3'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => 'One column layout',
                'dir' => database_path('templates/basic/6037a28418c95'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => '1-2 column layout',
                'dir' => database_path('templates/basic/6037a24ebdbd6'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => '1-2-1 column layout',
                'dir' => database_path('templates/basic/6037a2401b055'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => '1-3 column layout',
                'dir' => database_path('templates/basic/6037a275bf375'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => '1-3-1 column layout',
                'dir' => database_path('templates/basic/6037a25ddce80'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => '1-3-2 column layout',
                'dir' => database_path('templates/basic/6037a26b0a286'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => 'Two columns layout',
                'dir' => database_path('templates/basic/6037a2b67ed27'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => '2-1 column layout',
                'dir' => database_path('templates/basic/6037a2aa315d4'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => '2-1-2 column layout',
                'dir' => database_path('templates/basic/6037a29a35e05'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => 'Three columns layout',
                'dir' => database_path('templates/basic/6037a2dcb6c56'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => '3-1-3 column layout',
                'dir' => database_path('templates/basic/6037a2c3d7fa1'),
                'category' => $categoryBasic,
                'builder' => true,
            ], [
                'name' => 'Abandoned Cart Email #2',
                'dir' => database_path('templates/woos/woo-2'),
                'category' => $categoryWoo,
                'builder' => true,
            ], [
                'name' => 'Abandoned Cart Email #3',
                'dir' => database_path('templates/woos/woo-3'),
                'category' => $categoryWoo,
                'builder' => true,
            ], [
                'name' => 'Abandoned Cart Email #4',
                'dir' => database_path('templates/woos/woo-4'),
                'category' => $categoryWoo,
                'builder' => true,
            ], [
                'name' => 'Certified Yoga Therapist',
                'dir' => database_path('templates/themes/5d7b525bc015d'),
                'category' => $categoryTheme,
                'builder' => true,
            ], [
                'name' => 'Color Print - Your Print Companion',
                'dir' => database_path('templates/themes/5d7b526bbfd4c'),
                'category' => $categoryTheme,
                'builder' => true,
            ], [
                'name' => 'Gift Card!',
                'dir' => database_path('templates/themes/5d7b527be2bd2'),
                'category' => $categoryTheme,
                'builder' => true,
            ],
        ];

        foreach ($templates as $meta) {
            $template = \Acelle\Model\Template::createFromDirectory($meta, $meta['dir']);
            $template->categories()->attach($meta['category']->id);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
