<?php
namespace App\Maker;
use Doctrine\Common\Persistence\ObjectManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MakeAdmin extends AbstractMaker
{
    private $manager;
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }
    public static function getCommandName(): string
    {
        return 'app:make:admin';
    }
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Generates an admin class based on the given model class')
            ->addArgument('model', InputArgument::OPTIONAL, 'The fully qualified model class')
        ;
    }
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            AbstractAdmin::class,
            'sonata-project/admin-bundle'
        );
    }
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
    }
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $params = $this->getParameters($input);
        $files = $this->getFiles($params);
        foreach($files as $temppath => $filepath) {
            $generator->generateFile($filepath, $temppath, $params);
            $generator->writeChanges();
        }
    }
    public function getParameters(InputInterface $input): array
    {
        $modelClass = $input->getArgument('model');
        $class = str_replace('\\Entity\\', '\\Admin\\', $modelClass).'Admin';
        $file = str_replace('\\', '/', $class).'.php';
        $file = preg_replace('`^App`', 'src', $file);
        $parts = explode('\\', $class);
        $fields = $this->manager->getClassMetadata($modelClass)->getFieldNames();
        $associations = $this->manager->getClassMetadata($modelClass)->getAssociationNames();
        $code = '';
        foreach ($fields as $field) {
            $code .= str_repeat('    ', 3).'->add(\''.$field.'\')'."\n";
        }
        foreach ($associations as $field) {
            $code .= str_repeat('    ', 3).'->add(\''.$field.'\')'."\n";
        }
        $code = ltrim($code);
        return [
            'className' => $class,
            'fileName' => $file,
            'classBasename' => array_pop($parts),
            'namespace' => implode('\\', $parts),
            'fields' => $fields,
            'associations' => $associations,
            'code' => $code,
        ];
    }
    public function getFiles(array $params): array
    {
        $originalFile = __DIR__.'/../../vendor/sonata-project/admin-bundle/src/Resources/skeleton/Admin.php.twig';
        $original = file_get_contents($originalFile);
        $new = substr($original, strpos($original, 'namespace'));
        $new = '<?= "<?php\n" ?>'."\n\n".$new;
        $new = str_replace(['{{- ', '{{ ', ' }}'], ['<?= $', '<?= $', ' ?>'], $new);
        $newFile = sys_get_temp_dir().'/MakeAdmin.tpl.php';
        file_put_contents($newFile, $new);
        return [
            $newFile => $params['fileName'],
        ];
    }
    public function writeNextStepsMessage(array $params, ConsoleStyle $io)
    {
    }
}