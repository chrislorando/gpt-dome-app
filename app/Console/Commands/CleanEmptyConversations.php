<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use Illuminate\Console\Command;

class CleanEmptyConversations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conversations:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all conversations that have no messages';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = Conversation::whereDoesntHave('messages')->count();
        
        if ($count === 0) {
            $this->info('No empty conversations found.');
            return self::SUCCESS;
        }

        if ($this->confirm("Delete {$count} empty conversation(s)?", true)) {
            Conversation::whereDoesntHave('messages')->delete();
            $this->info("Deleted {$count} empty conversation(s).");
        } else {
            $this->info('Operation cancelled.');
        }

        return self::SUCCESS;
    }
}
