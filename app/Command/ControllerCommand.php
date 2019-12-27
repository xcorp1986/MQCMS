<?php
declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Devtool\Generator\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Command()
 * Class ControllerCommand
 * @package App\Command
 */
class ControllerCommand extends GeneratorCommand
{
    protected $name = 'mq:controller';

    public function __construct()
    {
        parent::__construct($this->name);
        $this->setDescription('Create a new mqcms controller class');
    }

    /**
     * Execute the console command.
     *
     * @return null|bool
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $inputs = $this->getNameInput();
        $name = $this->qualifyClass($inputs['name']);

        $path = $this->getPath($name);

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if (($input->getOption('force') === false) && $this->alreadyExists($inputs['name'])) {
            $output->writeln(sprintf('<fg=red>%s</>', $name . ' already exists!'));
            return false;
        }

        if (!$this->getStub()) {
            $this->output->writeln(sprintf('<fg=red>%s</>', 'module ' . trim($this->input->getArgument('type')) . ' not exists!'));
            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        file_put_contents($path, $this->buildModelClass($name, $inputs['service']));

        $output->writeln(sprintf('<info>%s</info>', $name . ' created successfully.'));
    }

    /**
     * @param $name
     * @param $service
     * @return string|string[]
     */
    protected function buildModelClass($name, $service)
    {
        $stub = file_get_contents($this->getStub());
        $stub = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
        return $this->replaceService($stub, $service);
    }

    /**
     * @param $stub
     * @param $name
     * @return string|string[]
     */
    protected function replaceService($stub, $name)
    {
        return str_replace('%SERVICE%', $name, $stub);
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        $type = strtolower(trim($this->input->getArgument('type')));
        if (!in_array($type, ['api', 'admin'])) {
            return '';
        }
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/' . strtolower($type) . '_controller.stub';
    }

    /**
     * @return string
     */
    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Controller\\Admin';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return [
            'name' => trim($this->input->getArgument('name')),
            'service' => trim($this->input->getArgument('service')),
            'type' => trim($this->input->getArgument('type'))
        ];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
            ['service', InputArgument::REQUIRED, 'The name of the service class'],
            ['type', InputArgument::REQUIRED, 'module controller type, eg. admin or api ...'],
        ];
    }
}