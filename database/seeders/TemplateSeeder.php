<?php

namespace Westlinks\Wlcms\Database\Seeders;

use Illuminate\Database\Seeder;
use Westlinks\Wlcms\Services\TemplateManager;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding default templates...');

        try {
            // Persist registered templates to database
            TemplateManager::persistRegisteredTemplates();
            
            $count = TemplateManager::getAll(false)->count();
            
            $this->command->info("✓ Successfully seeded {$count} templates");
            
            // Display template summary
            $templates = TemplateManager::getAll(false);
            
            $this->command->newLine();
            $this->command->info('Seeded Templates:');
            $this->command->table(
                ['Identifier', 'Name', 'Category', 'Active'],
                $templates->map(function ($template) {
                    return [
                        $template->identifier,
                        $template->name,
                        $template->category,
                        $template->active ? 'Yes' : 'No',
                    ];
                })->toArray()
            );
            
        } catch (\Exception $e) {
            $this->command->error('Failed to seed templates: ' . $e->getMessage());
        }
    }
}
