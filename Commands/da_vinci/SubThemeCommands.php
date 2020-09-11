<?php

declare(strict_types = 1);

namespace Drush\Commands\da_vinci;

use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\CommandError;
use Drupal\da_vinci\SubThemeGenerator;
use Drush\Commands\DrushCommands;
use Exception;
use FilesystemIterator;
use Robo\Contract\BuilderAwareInterface;
use Robo\State\Data as RoboStateData;
use Robo\TaskAccessor;
use Symfony\Component\Filesystem\Filesystem;
use Robo\Task\Archive\loadTasks as ArchiveTaskLoader;
use Robo\Task\Filesystem\loadTasks as FilesystemTaskLoader;
use Symfony\Component\Finder\Finder;

/**
 * Drush 9+ commands for the Da Vinci module.
 */
class SubThemeCommands extends DrushCommands implements BuilderAwareInterface {

  use TaskAccessor;
  use ArchiveTaskLoader;
  use FilesystemTaskLoader;

  /**
   * SubThemeCreator variable.
   *
   * @var \Drupal\da_vinci\SubThemeGenerator
   */
  protected $subThemeCreator;

  /**
   * Fs variable.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fs;

  /**
   * {@inheritdoc}
   */
  public function __construct(?SubThemeGenerator $subThemeCreator = NULL, ?Filesystem $fs = NULL) {
    $this->subThemeCreator = $subThemeCreator ?: new SubThemeGenerator();
    $this->fs = $fs ?: new Filesystem();

    parent::__construct();
  }

  /**
   * Creates a Da Vinci sub-theme.
   *
   * @command da_vinci:subtheme
   * @aliases subtheme
   *
   * @bootstrap full
   *
   * @option string $machine-name
   *   The machine-readable name of your sub-theme. This will be auto-generated
   *   from the human-readable name if omitted.
   * @option string $description
   *   The description of your sub-theme
   * @option string $destination
   *   The destination of your sub-theme
   * @option string $starterkit
   *   The name or url of the starter kit to use.
   *
   * @usage drush da_vinci:subtheme 'My Theme'
   *   Creates a sub-theme called "My Theme", using the default options.
   * @usage drush da_vinci:subtheme 'My Theme' --machine_name=my_theme
   *   Creates a sub-theme called "My Theme" with a specific machine name.
   *
   * @davinciArgLabel name
   * @davinciOptionMachineName machine-name
   */
  public function generateSubTheme(
    string $name,
    array $options = [
      'machine-name' => '',
      'description' => '',
      'destination' => '',
      'starterkit' => 'default',
    ]
  ) {
    $starterkit = $options['starterkit'];

    // @todo Use extension service.
    $davinciDir = drupal_get_path('theme', 'da_vinci');
    $srcDir = "$davinciDir/starterkits/{$starterkit}";
    $dstDir = "{$options['destination']}/{$options['machine-name']}";

    $cb = $this->collectionBuilder();
    $cb->getState()->offsetSet('srcDir', $srcDir);

    $cb->addCode(function (RoboStateData $data) use ($dstDir): int {
      $logger = $this->logger();
      $logger->debug(
        'copy Da Vinci starterkit {starterkit} from <info>{srcDir}</info> to <info>{dstDir}</info>',
        [
          'srcDir' => $data['srcDir'],
          'dstDir' => $dstDir,
        ]
      );

      try {
        $this->fs->mirror($data['srcDir'], $dstDir);
      }
      catch (Exception $e) {
        $this->logger()->error($e->getMessage());

        return 1;
      }

      return 0;
    });

    $cb->addCode(function () use ($name, $options, $dstDir): int {
      $logger = $this->logger();
      $logger->debug(
        'customize Da Vinci starterkit {starterkit} in <info>{dstDir}</info> directory',
        [
          'dstDir' => $dstDir,
        ]
      );

      $this
        ->subThemeCreator
        ->setDir($dstDir)
        ->setMachineName($options['machine-name'])
        ->setName($name)
        ->setDescription($options['description'])
        ->generate();

      return 0;
    });

    return $cb;
  }

  /**
   * Implements onHookValidateDaVinciGenerateSubTheme().
   *
   * @hook validate da_vinci:subtheme
   */
  public function onHookValidateDaVinciGenerateSubTheme(CommandData $commandData): ?CommandError {
    $input = $commandData->input();

    if (!$input->getOption('starterkit')) {
      $input->setOption('starterkit', 'default');
    }

    if (!$input->getOption('description')) {
      $input->setOption('description', $this->getDefaultDescription());
    }

    $machineName = $input->getOption('machine-name');
    if (!$machineName) {
      $machineName = $this->convertLabelToMachineName($input->getArgument('name'));
      $input->setOption('machine-name', $machineName);
    }

    $destination = $input->getOption('destination');
    if (!$destination) {
      $destination = $this->getDefaultDestination();
      $input->setOption('destination', $destination);
    }

    $dstDir = "$destination/$machineName";
    if ($this->fs->exists($dstDir) && !$this->isDirEmpty($dstDir)) {
      return new CommandError("Destination directory '$dstDir' not empty", 1);
    }

    return NULL;
  }

  /**
   * Implements onHookValidateDaVinciArgLabel().
   *
   * @hook validate @davinciArgLabel
   *
   * @return null|\Consolidation\AnnotatedCommand\CommandError
   *   Return null or CommandError.
   */
  public function onHookValidateDaVinciArgLabel(CommandData $commandData): ?CommandError {
    $annotationKey = 'davinciArgLabel';
    $annotationData = $commandData->annotationData();
    if (!$annotationData->has($annotationKey)) {
      return NULL;
    }

    $commandErrors = [];
    $argNames = $this->parseMultiValueAnnotation($annotationData->get($annotationKey));
    foreach ($argNames as $argName) {
      $commandErrors[] = $this->onHookValidateDaVinciArgLabelSingle($commandData, $argName);
    }

    return $this->aggregateCommandErrors($commandErrors);
  }

  /**
   * Implements onHookValidateDaVinciArgLabelSingle().
   */
  protected function onHookValidateDaVinciArgLabelSingle(CommandData $commandData, string $argName): ?CommandError {
    $label = $commandData->input()->getArgument($argName);
    if (strlen($label) === 0) {
      return NULL;
    }

    if (!preg_match('/^[^\t\r\n]+$/ui', $label)) {
      return new CommandError("Tabs and new line characters are not allowed in argument '$argName'.");
    }

    return NULL;
  }

  /**
   * Implements onHookValidateDaVinciOptionMachineName().
   *
   * @hook validate @davinciOptionMachineName
   */
  public function onHookValidateDaVinciOptionMachineName(CommandData $commandData) {
    $annotationKey = 'davinciOptionMachineName';
    $annotationData = $commandData->annotationData();
    if (!$annotationData->has($annotationKey)) {
      return NULL;
    }

    $commandErrors = [];
    $optionNames = $this->parseMultiValueAnnotation($annotationData->get($annotationKey));
    foreach ($optionNames as $optionName) {
      $commandErrors[] = $this->onHookValidateDaVinciOptionMachineNameSingle($commandData, $optionName);
    }

    return $this->aggregateCommandErrors($commandErrors);
  }

  /**
   * Implements onHookValidateDaVinciOptionMachineNameSingle().
   */
  protected function onHookValidateDaVinciOptionMachineNameSingle(CommandData $commandData, $optionName): ?CommandError {
    $machineNames = $commandData->input()->getOption($optionName);
    if (!is_array($machineNames)) {
      $machineNames = strlen($machineNames) !== 0 ? [$machineNames] : [];
    }

    $invalidMachineNames = [];
    foreach ($machineNames as $machineName) {
      if (!preg_match('/^[a-z][a-z0-9_]*$/', $machineName)) {
        $invalidMachineNames[] = $machineName;
      }
    }

    if ($invalidMachineNames) {
      return new CommandError("Following machine-names are invalid in option '$optionName': " . implode(', ', $invalidMachineNames));
    }

    return NULL;
  }

  /**
   * Implements parseMultiValueAnnotation().
   */
  protected function parseMultiValueAnnotation(string $value): array {
    return $this->explodeCommaSeparatedList($value);
  }

  /**
   * Implements explodeCommaSeparatedList().
   */
  protected function explodeCommaSeparatedList(string $items): array {
    return array_filter(
      preg_split('/\s*,\s*/', trim($items)),
      'mb_strlen'
    );
  }

  /**
   * Implements aggregateCommandErrors().
   *
   * @param \Consolidation\AnnotatedCommand\CommandError[] $commandErrors
   *   CommandError.
   */
  protected function aggregateCommandErrors(array $commandErrors): ?CommandError {
    $errorCode = 0;
    $messages = [];
    /** @var \Consolidation\AnnotatedCommand\CommandError $commandError */
    foreach (array_filter($commandErrors) as $commandError) {
      $messages[] = $commandError->getOutputData();
      $errorCode = max($errorCode, $commandError->getExitCode());
    }

    if ($messages) {
      return new CommandError(implode(PHP_EOL, $messages), $errorCode);
    }

    return NULL;
  }

  /**
   * Implements convertLabelToMachineName().
   */
  protected function convertLabelToMachineName(string $label): string {
    return mb_strtolower(preg_replace('/[^a-z0-9_]+/ui', '_', $label));
  }

  /**
   * Implements getDefaultDestination().
   */
  protected function getDefaultDestination(): string {
    if ($this->fs->exists('./themes/contrib') || $this->fs->exists('./themes/custom')) {
      return './themes/custom';
    }

    return './themes';
  }

  /**
   * Implements getDefaultDescription().
   */
  protected function getDefaultDescription(): string {
    return 'Da Vinci custom subtheme.';
  }

  /**
   * Implements isDirEmpty().
   */
  protected function isDirEmpty(string $dir): bool {
    return !(new FilesystemIterator($dir))->valid();
  }

  /**
   * Implements getDirectDescendants().
   */
  protected function getDirectDescendants(string $dir): Finder {
    return (new Finder())
      ->in($dir)
      ->depth('0');
  }

  /**
   * Implements getFileNameFromUrl().
   */
  protected function getFileNameFromUrl(string $url): string {
    return pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_BASENAME);
  }

  /**
   * Implements getTopLevelDir().
   */
  protected function getTopLevelDir(string $parentDir): string {
    $directDescendants = $this->getDirectDescendants($parentDir);
    $iterator = $directDescendants->getIterator();
    $iterator->rewind();
    /** @var \Symfony\Component\Finder\SplFileInfo $firstFile */
    $firstFile = $iterator->current();
    if ($directDescendants->count() === 1 && $firstFile->isDir()) {
      return $firstFile->getPathname();
    }

    return '';
  }

}
