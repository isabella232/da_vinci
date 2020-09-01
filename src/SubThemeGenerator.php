<?php

declare(strict_types = 1);

namespace Drupal\da_vinci;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * SubThemeGenerator class.
 */
class SubThemeGenerator {

  /**
   * Fs variable.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fs;

  /**
   * Finder variable.
   *
   * @var \Symfony\Component\Finder\Finder
   */
  protected $finder;

  /**
   * MachineNameOld variable.
   *
   * @var string
   */
  protected $machineNameOld = '';

  /**
   * Dir variable.
   *
   * @var string
   */
  protected $dir = '';

  /**
   * Implements getDir().
   */
  public function getDir(): string {
    return $this->dir;
  }

  /**
   * Set value to the variable dir.
   *
   * @param string $dir
   *   Directory where a Da Vinci starter kit already copied to.
   *
   * @return $this
   */
  public function setDir(string $dir) {
    $this->dir = $dir;

    return $this;
  }

  /**
   * MachineName variable.
   *
   * @var string
   */
  protected $machineName = '';

  /**
   * Implements getMachineName().
   */
  public function getMachineName(): string {
    if (!$this->machineName) {
      return basename($this->getDir());
    }

    return $this->machineName;
  }

  /**
   * Implements setMachineName().
   */
  public function setMachineName(string $machineName) {
    $this->machineName = $machineName;

    return $this;
  }

  /**
   * Name variable.
   *
   * @var string
   */
  protected $name = '';

  /**
   * Implements getName().
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Implements setName().
   */
  public function setName(string $name) {
    $this->name = $name;

    return $this;
  }

  /**
   * Description variable.
   *
   * @var string
   */
  protected $description = '';

  /**
   * Implements getDescription().
   */
  public function getDescription(): string {
    return $this->description;
  }

  /**
   * Implements setDescription().
   */
  public function setDescription(string $description) {
    $this->description = $description;

    return $this;
  }

  /**
   * Implements __construct().
   */
  public function __construct() {
    $this->fs = new Filesystem();
    $this->finder = new Finder();
  }

  /**
   * Implements generate().
   */
  public function generate() {
    return $this
      ->initMachineNameOld()
      ->modifyFileContents()
      ->renameFiles();
  }

  /**
   * Implements initMachineNameOld().
   */
  protected function initMachineNameOld() {
    $dstDir = $this->getDir();
    $infoFiles = glob("$dstDir/*.info.yml");

    $this->machineNameOld = basename(reset($infoFiles), '.info.yml');

    return $this;
  }

  /**
   * Implements modifyFileContents().
   */
  protected function modifyFileContents() {
    $replacementPairs = $this->getFileContentReplacementPairs();
    foreach ($this->getFilesToMakeReplacements() as $fileName) {
      $this->modifyFileContent($fileName, $replacementPairs);
    }

    return $this;
  }

  /**
   * Implements renameFiles().
   */
  protected function renameFiles() {
    $machineNameNew = $this->getMachineName();
    if ($this->machineNameOld === $machineNameNew) {
      return $this;
    }

    foreach ($this->getFileNamesToRename() as $fileName) {
      $this->fs->rename($fileName, str_replace($this->machineNameOld, $machineNameNew, $fileName));
    }

    return $this;
  }

  /**
   * Implements modifyFileContent().
   */
  protected function modifyFileContent(string $fileName, array $replacementPairs) {
    if (!$this->fs->exists($fileName)) {
      return $this;
    }

    $this->fs->dumpFile(
      $fileName,
      strtr($this->fileGetContents($fileName), $replacementPairs)
    );

    return $this;
  }

  /**
   * Implements getFileNamesToRename().
   */
  protected function getFileNamesToRename(): array {
    // Find all files within the theme that match STARTERKIT.
    return array_keys(iterator_to_array($this->finder->files()->name("*{$this->machineNameOld}*")->in($this->getDir())));
  }

  /**
   * Implements getFileContentReplacementPairs().
   */
  protected function getFileContentReplacementPairs(): array {
    return [
      'My theme' => $this->getName(),
      'mytheme_description' => $this->getDescription(),
      'mytheme' => $this->getMachineName(),
      "\nhidden: true\n" => "\n",
    ];
  }

  /**
   * Implements getFilesToMakeReplacements().
   */
  private function getFilesToMakeReplacements(): array {
    return array_keys(iterator_to_array($this->finder->files()->in($this->getDir())));
  }

  /**
   * Implements fileGetContents().
   */
  protected function fileGetContents(string $fileName): string {
    $content = file_get_contents($fileName);
    if ($content === FALSE) {
      throw new RuntimeException("Could not read file '$fileName'", 1);
    }

    return $content;
  }

}
