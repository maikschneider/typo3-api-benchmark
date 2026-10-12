<?php

declare(strict_types=1);

namespace MaikSchneider\CampDemo\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsCommand(name: 'camp:edit', description: 'Change one field of a record through the DataHandler, the way a backend edit does')]
final class EditCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('table', InputArgument::REQUIRED);
        $this->addArgument('uid', InputArgument::REQUIRED);
        $this->addArgument('field', InputArgument::REQUIRED);
        $this->addArgument('value', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Bootstrap::initializeBackendAuthentication();

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([
            $input->getArgument('table') => [
                (int)$input->getArgument('uid') => [$input->getArgument('field') => $input->getArgument('value')],
            ],
        ], []);
        $dataHandler->process_datamap();

        if ($dataHandler->errorLog !== []) {
            $output->writeln($dataHandler->errorLog);
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
