<?php

namespace MortenScheel\LaravelBlitz\Console\Commands;

use Illuminate\Console\Command;
use MortenScheel\LaravelBlitz\Concerns\RunsProcessCommands;

abstract class BaseCommand extends Command
{
    use RunsProcessCommands;
}
