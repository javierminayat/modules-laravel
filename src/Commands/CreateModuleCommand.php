<?php
/**TODO AL CREAR EL PROVIDER NO GENERA EL NAMESPACE */
namespace JMinayaT\Modules\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use JMinayaT\Modules\Util\GeneratorCommand;
use JMinayaT\Modules\Models\Module;

class CreateModuleCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(){}

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $name = $this->getStudly();
        $name_case = $this->getTitleCase();
        if($this->moduleExists($name)) {
            $this->error('Module already exists!');
            return false;
        }
        $dcp = $this->ask('module description?');
        $path = $this->getBasePath();
        $this->makeDirectory($path);
        $this->makeDirectory($path.'Http/Controllers');
        $this->makeDirectory($path.'Providers');
        $this->makeDirectory($path.'Resources/assets/js');
        $this->makeDirectory($path.'Resources/assets/css');
        $this->makeDirectory($path.'Resources/views');
        $this->makeDirectory($path.'Resources/lang');
        $this->makeDirectory($path.'Models');
        $this->makeDirectory($path.'Routes');
        $this->makeDirectory($path.'Config');
        $this->makeDirectory($path.'Database/Migrations');
        $this->makeDirectory($path.'Database/Factories');
        $this->makeDirectory($path.'Database/Seeds');

        $this->files->put($path.'Routes/web.php', $this->files->get(__DIR__.'/stubs/route.web.stub'));
        $this->files->put($path.'Routes/api.php', $this->files->get(__DIR__.'/stubs/route.api.stub'));

        $this->files->put($path.'Database/Seeds/DatabaseSeeder.php', $this->dataBaseSeederbuildClass($name)); 
        $this->files->put($path.'Providers/'.$name.'ServiceProvider.php', $this->buildProvider($name)); 
        $this->files->put($path.'Config/'.strtolower($name).'.php', $this->buildConfig($name)); 
        $this->files->put($path.'module.json', $this->buildJson($name_case, $this->getStudly(), $dcp));
        $this->moduledt->registerModuleDB($name_case, $dcp, $this->getStudly());
        $this->info('Module created successfully.');

    }
    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function dataBaseSeederbuildClass($name)
    {
        $stub = $this->files->get(__DIR__.'/stubs/database-seeder.stub');

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }
    

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }
        return $path;
    }

    protected function getOptions()
    {
        return [
            ['controller', 'c',  InputOption::VALUE_OPTIONAL, 'create controller module'],
            ['model',      'd',  InputOption::VALUE_OPTIONAL, 'create model module'],
            ['migration',  'm',  InputOption::VALUE_OPTIONAL, 'create migration for model module'],
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
            ['module',   InputArgument::REQUIRED, 'The name of the module'],
        ];
    }

    protected function buildJson($name, $alias, $description)
    {
        $getStub = $this->files->get(__DIR__.'/stubs/module.json.stub');
        $stub = str_replace(
                ['DummyName', 'DummyAlias','DummyDescription'],
                [$name, $alias, $description],
                $getStub
        );
        return $stub;
    }

    protected function buildProvider($name) {
        $stub = $this->files->get(__DIR__.'/stubs/main-provider.stub');
        return $this->replaceNamespaceMainProvider($stub, $name)->replaceClass($stub, $name.'ServiceProvider');
    }

    protected function buildConfig($name) {
        $stub = $this->files->get(__DIR__.'/stubs/config.stub');
        return $this->replaceNameConfig($stub, $name)->replaceClass($stub, $name);;
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespaceMainProvider(&$stub, $name)
    {
        $stub = str_replace(
            ['DummyNamespace', 'DummyModule','DummyLowerModule'],
            [$this->rootNamespace().'Providers' ,str_replace($this->getNamespace($name).'\\', '', $name),strtolower(str_replace($this->getNamespace($name).'\\', '', $name))],
            $stub
        );

        return $this;
    }

    protected function replaceNameConfig(&$stub, $name)
    {
        $stub = str_replace(
            ['DummyModule'],
            [str_replace($this->getNamespace($name).'\\', '', $name)],
            $stub
        );

        return $this;
    }
}