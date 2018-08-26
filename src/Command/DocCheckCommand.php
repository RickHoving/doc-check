<?php
/**
 * This File is the part of Doc-Check
 *
 * @see the link to the documentation
 */

namespace DocCheck\Command;

use DocCheck\Command\Result\Target;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\File\LocalFile;
use phpDocumentor\Reflection\Php\ProjectFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class
 *
 * @see expected link
 */
class DocCheckCommand extends Command
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        // Ensure that linked files are skipped as they are not supported
        // @todo show an error message when links are found and continue?
        $adapter = new Local(getcwd(), LOCK_EX, Local::SKIP_LINKS);
        $this->fileSystem = new Filesystem($adapter);
    }


    protected function configure()
    {
        $this->setName('DocCheck');
        $this->setDescription('Get the percentage of documentation coverage');
        $this->addOption('target', 't', InputOption::VALUE_REQUIRED,
            'The target where the documentation coverage is checked from');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targets = explode(',', $input->getOption('target'));

        $style = new SymfonyStyle($input, $output);
        $this->progressBar = new ProgressBar($output);

        $validationResult = $this->validateTargets($targets);

        if (count($validationResult)) {
            $this->showError($validationResult, $style);
            return;
        }

        $totalFiles = 0;
        $targetFiles = [];
        foreach ($targets as $target) {
            $targetFiles[$target] = $this->fileSystem->listContents($target, true);
            $totalFiles = +count($targetFiles[$target]);
        }

        $this->progressBar->setMaxSteps($totalFiles);

        $style->writeln("Now processing $totalFiles files:");
        $this->progressBar->start();

        $result = new Result($targets, $this->fileSystem);
        $this->progressBar->finish();
        $this->showOutput($style, $result);
    }

    /**
     * @param string[] $targets
     * @param SymfonyStyle $style
     * @link some link to the documentation
     */
    private function showError(array $targets, SymfonyStyle $style)
    {
        $errorMessage = 'Target(s) not found:';
        foreach ($targets as $target) {
            $errorMessage .= PHP_EOL . "- $target";
        }
        $style->getErrorStyle()->error($errorMessage);
    }


    /**
     * @param SymfonyStyle $style
     * @param Result $result
     */
    private function showOutput(SymfonyStyle $style, Result $result)
    {
        $unparsedFiles = $result->getUnparsedFiles();
        $style->title('Files missing documentation:');
        $style->listing($result->getFailedFiles());
        $style->newLine();
        if(count($unparsedFiles) > 0) {
            $style->title('Unparsed files:');
            $style->listing($unparsedFiles);
        }
        $style->newLine();
        $style->title('Coverage:');
        $style->table(
            array('Target', 'No. files', 'Percentage'),$result->getTotals()
        );
    }

    
    /**
     * @param $targets string[]
     * @return string[]
     */
    private function validateTargets(array $targets): array
    {
        $validationResult = [];

        foreach ($targets as $target) {
            if(empty($target)){
                continue;
            }
            if (!$this->fileSystem->has($target)) {
                $validationResult[] = $target;
            }
        }

        return $validationResult;
    }
}