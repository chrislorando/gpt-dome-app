<?php

namespace App\Console\Commands;

use App\Models\AiModel;
use App\Services\OpenAiService;
use Illuminate\Console\Command;

class FetchOpenAiModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-open-ai-models {--force : Force update existing models}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and save OpenAI models to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching OpenAI models...');

        try {
            $openAiService = app(OpenAiService::class);
            $models = $openAiService->fetchModels();

            $this->info('Found ' . count($models) . ' models');

            $saved = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($models as $modelData) {
                $existingModel = AiModel::find($modelData['id']);

                if ($existingModel) {
                    if ($this->option('force')) {
                        $existingModel->update($modelData);
                        $updated++;
                        $this->line("Updated: {$modelData['id']}");
                    } else {
                        $skipped++;
                        $this->line("Skipped (exists): {$modelData['id']}");
                    }
                } else {
                    AiModel::create($modelData);
                    $saved++;
                    $this->line("Saved: {$modelData['id']}");
                }
            }

            $this->info("Completed: {$saved} saved, {$updated} updated, {$skipped} skipped");

        } catch (\Exception $e) {
            $this->error('Failed to fetch models: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
