#!/usr/bin/env php
<?php
/**
 * @author Laurent Bachelier <laurent@bachelier.name>
 */

error_reporting(E_ALL|E_STRICT);

require(dirname(__FILE__).'/lib/FileTools.php');
require(dirname(__FILE__).'/lib/MultiConfig.php');
require(dirname(__FILE__).'/lib/Symfony.php');
require(dirname(__FILE__).'/lib/Argument.php');

/**
 * @pram string $project_path Absolute project path
 * @param string $target The destination of the symlink
 * @param string $link The relative path of the symlink to create
 * @param boolean $relative Try to use a relative destination
 * @return boolean Success
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 */
function replace_symlink($project_path, $target, $link, $relative = true)
{
  if ($relative)
  {
    $target = FileTools::calculateRelativeDir($project_path.'/'.$link, $target);
  }

  $success = FileTools::symlink($target, $project_path.'/'.$link);

  log_message('  '.$link.' => '.$target.($success ? '' : ' ...FAILED!'));
}

/**
 * @param string $message
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 */
function log_message($message)
{
  echo $message."\n";
}

/**
 * Find plugins with a "web" directory
 * @param string $project_path
 * @return array Plugin names
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 */
function find_plugins($project_path)
{
  $plugins = array();
  foreach (new DirectoryIterator($project_path."/plugins") as $file)
  {
    $name = $file->getFilename();
    if ($file->isDir()
        && preg_match('/^[^\.].+Plugin$/', $name)
        && is_dir($project_path.'/plugins/'.$name.'/web')
    )
    {
      $plugins[] = $name;
    }
  }

  return $plugins;
}


$options = MultiConfig::get();

$project_path = Symfony::getProjectPath();

log_message("Using symfony version ".$options['want']);
$symlinks = array();
if ($options['genconf'])
{
  $symlinks[$options['genconf']] = $options['path'].'/genconf.php';
}

$sf_path = $options['sf_path'][$options['want']];
foreach (array(
    'symfony_symlink' => '',
    'lib_symlink' => 'lib',
    'data_symlink' => 'data',
    'web_symlink' => 'data/web/sf',
  )
  as $option => $relpath)
{
  $link = $options[$option];
  if ($link)
  {
    $target = $sf_path.'/'.$relpath;
    if (!is_dir($target))
    {
      throw new Exception($target.' is not a directory');
    }
    $symlinks[$link] = $target;
  }
}

log_message('Creating symbolic links...');
foreach ($symlinks as $link => $target)
{
  replace_symlink($project_path, $target, $link, $options['relative']);
}
if ($options['do_plugins'])
{
  if (version_compare($options['want'], '1.2') >= 0)
  {
    log_message('Creating symbolic links for plugins... (calling symfony)');
    system('/usr/bin/env php symfony plugin:publish-assets');
  }
  else
  {
    log_message('Creating symbolic links for plugins... (internal method)');
    foreach (find_plugins($project_path) as $name)
    {
      $link = 'web/'.$name;
      $target = $project_path.'/plugins/'.$name.'/web';
      // Ignore if there is a real directory with this name
      if (is_link($project_path.'/'.$link) || !is_dir($project_path.'/'.$link))
      {
        replace_symlink($project_path, $target, $link, $options['relative']);
      }
    }
  }
}