<?php

use Illuminate\Database\Seeder;

use App\Models\Contact;
use App\Models\SolarProject;

class DatabaseSeeder extends Seeder
{
    const NUMBER_OF_CONTACTS = 50;
    const NUMBER_OF_PROJECTS = 50;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker\Factory::create();
        $this->emptyDatabase();
        $this->seedContacts();
        $this->seedProjects();
        $this->seedContactProjects();
    }

    protected function emptyDatabase()
    {
        DB::table('contact_solar_project')->truncate();
        (new Contact)->newQueryWithoutScopes()->forceDelete();
        (new SolarProject)->newQueryWithoutScopes()->forceDelete();
    }

    protected function seedContacts()
    {
        for ($i = 0; $i < self::NUMBER_OF_CONTACTS; $i += 1) {
            $c = factory(Contact::class)->create();
        }
    }

    protected function seedProjects()
    {
        for ($i = 0; $i < self::NUMBER_OF_PROJECTS; $i += 1) {
            $p = factory(SolarProject::class)->create();
        }
    }

    protected function seedContactProjects()
    {
        $projects = SolarProject::all();
        foreach ($projects as $project) {
            $n = $this->faker->randomElement([1, 2, 3]);
            $contacts = Contact::all()->shuffle()->take($n);
            $project->contacts()->sync($contacts);
        }
    }
}
