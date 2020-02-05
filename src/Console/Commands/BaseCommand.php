<?php

namespace MortenScheel\LaravelStartup\Console\Commands;

use Illuminate\Console\Command;
use MortenScheel\LaravelStartup\Concerns\RunsProcessCommands;

abstract class BaseCommand extends Command
{
    use RunsProcessCommands;
}
