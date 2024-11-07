<?php

namespace Twigmac\Cli;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Will remove one of two identical files in the first of the two given
 * directories. Identical means the files must have the same relative path
 * and name (e. g. 'dir-a/data/file.txt' and 'dir-b/data/file1.txt') and
 * they must have the same MD5 sum.
 *
 * @package Twigmac\Cli
 */
class RemoveDuplicateFiles
{
    public const DEFAULT_LIMIT = 100;

    private int $limit = self::DEFAULT_LIMIT;
    private string $keepDir;
    private string $sweepDir;
    private string $md5Sum;
    private InputInterface $input;
    private OutputInterface $output;

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->sweepDir = $input->getArgument('sweepDir');
        $this->keepDir = $input->getArgument('keepDir');

        if ($input->getOption('limit') !== null) {
            $this->limit = intval($input->getOption('limit'));
        }

        if ($this->limit < 1) {
            $this->printMsg('Please select a limit greater than 0.', true);
            return Command::FAILURE;
        }

        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->sweepDir)
            ),
            '/^.*$/',
            RegexIterator::GET_MATCH
        );

        $iterations = 0;

        foreach ($iterator as $fileResult) {

            if (count($fileResult) > 1) {
                $this->printLine();
                $this->printMsg('More than one file. Please check:');
                $fileResultJson = json_encode($fileResult);
                if (is_string($fileResultJson)) {
                    $this->printMsg($fileResultJson);
                } else {
                    throw new \Exception('Unexpected error: 87c12cd6-10e4-4c82-a447-2753a0e777a6');
                }
                continue;
            }

            $removeFile = $fileResult[0];

            if (
                strpos($removeFile, '/..') === (strlen($removeFile) - 3)
                || strpos($removeFile, '/.') === (strlen($removeFile) - 2)
            ) {
                continue; // ignore `..` and `.` directories
            }

            $keepFile = str_replace(
                $this->sweepDir,
                $this->keepDir,
                $removeFile
            );

            if ($this->areIdentical($removeFile, $keepFile)) {

                $iterations++;

                if ($iterations > $this->limit) {
                    $this->printLine();
                    $this->printMsg('Limit of ' . $this->limit . ' file(s) reached.');
                    break;
                }

                $this->printLine();
                $this->printMsg('Found identical files (MD5 ' . $this->md5Sum . ')');
                $this->printMsg('(keep)   ' . $keepFile);
                $this->printMsg('(delete) ' . $removeFile);

                if ($input->getOption('really') !== true) {
                    $this->printMsg('DRY-RUN: ' . $removeFile . ' not deleted.');
                    continue;
                }

                if (
                    $input->getOption('force') === true
                    || $this->askDelete() === true
                ) {
                    $this->removeFile($removeFile);
                }
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Checks whether the two given files are identical or not.
     * 
     * @return bool `true` if both files exist and have the same MD5 sum
     */
    private function areIdentical(string $fileA, string $fileB): bool
    {
        if (!is_file($fileA) || !is_file($fileB)) {
            throw new \Exception('Unexpected error: 9ce7e562-7636-433c-a7eb-83f34fed5c2e');
        }

        $md5FileA = md5_file($fileA);
        $md5FileB = md5_file($fileB);

        if ($md5FileA === false || $md5FileB === false) {
            throw new \Exception('Unexpected error: 80482b30-9340-4c7a-bc88-062a6f7fab16');
        }

        if ($md5FileA === $md5FileB) {
            $this->md5Sum = $md5FileA;
            return true;
        }
        $this->md5Sum = '<NOT_SET>';
        return false;
    }

    private function askDelete(): bool
    {
        $questionHelper = new QuestionHelper;
        $areYouSure = new Question('Delete file? y[es] or n[o] ', false);
        $answer = $questionHelper->ask($this->input, $this->output, $areYouSure);
        if (!is_string($answer)) {
            return false;
        }
        $answer = strtolower($answer);
        return $answer === 'y' || $answer === 'yes';
    }

    private function removeFile(string $aDirFile): void
    {
        if (unlink($aDirFile)) {
            $this->printMsg($aDirFile . ' removed.');
        } else {
            $this->printMsg('ERROR removing ' . $aDirFile, true);
        }
    }

    private function printLine(): void
    {
        $this->output->writeln(
            '-----------------------------------------------------------------',
            OutputInterface::VERBOSITY_VERBOSE
        );
    }

    private function printMsg(string $msg = '', bool $force = false): void
    {
        if ($force) {
            $this->output->writeln($msg, OutputInterface::VERBOSITY_NORMAL);
            return;
        }
        $this->output->writeln($msg, OutputInterface::VERBOSITY_VERBOSE);
    }
}
