<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

// This command imports json.gz files from GHArchives for the date passed as a REQUIRED option of the command
class ImportGHArchivesDatasCommand extends Command
{
    protected static $defaultName = 'app:import-gharchive-datas';

    public function __construct(string $date = null)
    {
        $this->date = $date ?? date('Y-m-d');

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('GHArchives Events Download')
            ->addArgument('date', InputArgument::REQUIRED, 'Date for datas')
            ->setDescription('Imports datas from GHArchives to DB')
            ->setHelp(
                'This command allows you to download event datas 
                      from GHArchives for the date passed as an argument'
            )
            ->addUsage('bin/console app:import-gharchive-datas 2015-01-15')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = $input->getArgument('date');
        $output->writeln('Importing GH Events for date: ' . $date);
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)) {
            $output->writeln('Date must be of format YYYY-MM-DD-HH');

            return Command::FAILURE;
        }

        $dates = explode('-', $date);
        if (!checkdate((int) $dates[1], (int) $dates[2], (int) $dates[0])) {
            $output->writeln('Date must be valid');

            return Command::FAILURE;
        }
        $progressBar = new ProgressBar($output, 24);
        $githubUrl = 'wget https://data.gharchive.org/' . $date . '-{0..23}.json.gz -P ./gharchives' . $date . '/';
        $process = Process::fromShellCommandline($githubUrl);

        try {
            $output->writeln([
                'Importing datas from GHArchives to folder /gharchives' . $date,
                '============',
                '',
            ]);
            $progressBar->start();
            $process->mustRun();
            $progressBar->finish();
        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
