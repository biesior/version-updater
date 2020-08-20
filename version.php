<?php
declare(strict_types=1);

const EOL = PHP_EOL;
const EOLx2 = EOL . EOL;
const EOLx3 = EOL . EOL . EOL;
class VersionUpdater
{


    private $currentVersionFile = 'current_version.json';
    // TODO to handle with init, set update methods and keep it in current_version.json
    private $repository = 'https://github.com/biesior/version-updater/tree/';
    private $title = 'Version updater for project' . EOLx2;
    private $debug = false;
    private $emulateGLaDOS = true;
    private $colorsEnabled = true;
    // TODO to handle with init, set update methods and keep it in current_version.json
    private $isVersionDisplayOnWebAllowed = true;


    /**
     * VersionUpdater constructor.
     */
    public function __construct()
    {
        $env = $this->getCurrentEnv();
        if ($env == 'web' && count($_GET) == 0 && count($_POST) == 0) {
            if (!$this->isVersionDisplayOnWebAllowed) {
                die('These data are protected. Bye!');
            }
            $currentVersionDisplayed = 'No current version';
            $currentVersion = $this->getCurrentVersionFromFile(true);
            if (is_array($currentVersion)) {
                $currentVersion = json_encode($currentVersion, JSON_PRETTY_PRINT);
            }
            header('Content-Type: application/json');
            die($currentVersion);
        } elseif ($env != 'cli') {
            die(PHP_EOL . 'This script can be only executed in the CLI, bye!' . EOLx2);
        };

//        echo EOLx2;
//        echo $this->____cliActions() . EOLx2;
//        echo $this->____helperMethods() . EOLx2;
//        echo $this->____publicStaticMethods().EOLx2;
//        echo $this->____methodsForGettingCurrentVersion() . EOLx2;
//        echo EOLx2;
//        die();

        $parameters = array(
            'c' => 'clean',
            'x' => 'extract-colors',
            'debug'
        );
        $options = getopt(implode('', array_keys($parameters)), $parameters);

        if (array_key_exists('debug', $options)) {
            $this->debug = true;
        }
        if (array_key_exists('x', $options)) {
            $this->colorsEnabled = false;
        }


        if (isset($options['c']) || isset($options['clean'])) {
            system('clear;');
            echo ("\e[2;36m^--- For previous output scroll up \e[0m") . EOLx2;
        }

        $this->dispatcher();
    }

    /**
     * Methods for handling dispatcher requests (doing class' logic) starts here
     *
     * DO NOT move this method in the structure without reason.
     *
     * @return string Formatted filename:number + phpdoc
     * @throws ReflectionException
     * @internal This method returns the filename and line number where group of specific methods starts
     */
    private function ____cliActions(): string
    {
        return self::describeMethodItself();
    }

    private function dispatcher()
    {


        // @see:
        $parameters = [
            'h'  => 'help',
            'c'  => 'clean',
            'x',
            'patch',
            'minor',
            'major',
            'i:' => 'init:',
            'repository:',
            'folder:',
            'm:' => 'mode:',
            'n:' => 'new-version:',
            's:' => 'state:',
            'p:' => 'part:',
            'v:' => 'version:',
            'kill::',
            'debug',

        ];

        $hints = [
            'h'           => ['short' => 'h', 'long' => 'help', 'hint' => 'Displaying this help'],
            'c'           => ['short' => 'c', 'long' => 'clean', 'hint' => 'If set console will be cleaned for better output'],
            'i:'          => ['short' => 'i', 'long' => 'init', 'hint' => 'Create new version by default it will be `0.0.1-alpha` || - you can change it immediately using -m `set` or `update`'],
            'repository:' => ['long' => 'repository', 'hint' => "Repository URL ie \e[32mhttps://github.com/biesior/version-updater/\e[0m"],
            'm:'          => ['short' => 'm', 'long' => 'mode', 'hint' => 'Mode can be `set` or `update` || - When mode is `set` params `-n` or `--new-version` and `-s` or `--state` are required || - When mode is `update` param `-p, --part` is required'],
            'n:'          => ['short' => 'n', 'long' => 'new-version', 'hint' => 'Version which should be set like 1.2.3'],
            's:'          => ['short' => 's', 'long' => 'state', 'hint' => 'State which should be set like alpha, beta , stable'],
            'p:'          => ['short' => 'p', 'long' => 'part', 'hint' => 'Part to update allowed `major`, `minor`, `patch`'],
            'v:'          => ['short' => 'v', 'long' => 'version', 'hint' => 'Displays current version of the project'],
            'x'           => ['short' => 'x', 'hint' => "Extract colors, i.e. if you want to write the output to file like || \e[32mphp version.php --kill > output-color.txt\e[0m || \e[32mphp version.php --kill -x > output-mono.txt\e[0m"],
            'patch'       => ['long' => 'patch', 'hint' => 'is shortcut for `php version.php -m update -p patch`'],
            'minor'       => ['long' => 'minor', 'hint' => 'is shortcut for `php version.php -m update -p minor`'],
            'major'       => ['long' => 'major', 'hint' => 'is shortcut for `php version.php -m update -p major`'],
            'kill::'      => ['long' => 'kill', 'hint' => "(\e[31mdestructive!\e[0m) Deletes version file, you will need to start from beginning"],
//            'debug'         => ['long' => 'debug', 'hint' => 'If used looo...ot of debug may be displayed, use only for development'],

        ];

        $options = getopt(implode('', array_keys($parameters)), $parameters);

        $help = $this->getOptValue($options, 'h', 'help');
        $init = $this->getOptValue($options, 'i', 'init');
        $mode = $this->getOptValue($options, 'm', 'mode');
        $newVersion = $this->getOptValue($options, 'n', 'new-version');
        $state = $this->getOptValue($options, 's', 'state');
        $part = $this->getOptValue($options, 'p', 'part');
        $tree = $this->getOptValue($options, 't', 'tree');

        $relesePatch = $this->getOptValue($options, null, 'patch');
        $releseMinor = $this->getOptValue($options, null, 'minor');
        $releseMajor = $this->getOptValue($options, null, 'major');
        $kill = $this->getOptValue($options, null, 'kill');

        if ($this->debug) {
            echo EOL . 'Debug resolved options' . EOL;
            print_r([
                'help'          => $help,
                'mode'          => $mode,
                'init'          => $init,
                'new-version'   => $newVersion,
                'state'         => $state,
                'part'          => $part,
                'release_patch' => $relesePatch,
                'release_minor' => $releseMinor,
                'release_major' => $releseMajor,
                'kill'          => $kill,
            ]);
            echo EOL . 'Debug options' . EOL;
            print_r($options);
        }

        if (!is_null($help)) {
            $this->showHelp($parameters, $hints);
        } elseif (!is_null($init)) {
            $this->init($init, $tree);
        } elseif (!is_null($kill)) {
            $this->kill($kill);
        } elseif (!is_null($relesePatch)) {
            $this->update('patch', null);
        } elseif (!is_null($releseMinor)) {
            $this->update('minor', null);
        } elseif (!is_null($releseMajor)) {
            $this->update('major', null);
        } elseif (!is_null($mode)) {
            if ($mode == 'set') {
                if (is_null($newVersion) || is_null($state)) {
                    die("\e[1;31mIf `mode` is `set`, params `-n` or `--new-version` and `-s` or `--state` are required. Bye!\e[0m" . EOLx2);
                }
                $version = $options['n'];
                $state = $options['s'];
                $this->set($version, $state);
            } elseif ($mode = 'update') {
                if (is_null($part)) {
                    die("\e[1;31mIf `mode` is `update`, param`-p` or `--part` is required. Bye!\e[0m" . EOLx2);
                }
                echo 'Make update';
            } else {
                die(sprintf("\e[1;31mMode %s is not supported, check the help with -h parameter. Bye!\e[0m", $options['m']) . EOLx2);
            }
        } else {
            die(EOL . $this->title . "\e[1;31mInvalid parameters, check the help with -h parameter, \n\nBye!\e[0m" . EOLx2);
        }

    }


    private function showHelp($parameters, $hints)
    {
        $out = EOL . 'This CLI sets or updates version according to schema and updates required files' . EOLx2;

        $longest = 0;
        $toDisplay = [];
        foreach ($hints as $hint) {
            $keys = [];
            if (array_key_exists('short', $hint)) {
                $keys[] = '-' . self::removeColon($hint['short']);
            }
            if (array_key_exists('long', $hint)) {
                $keys[] = '--' . self::removeColon($hint['long']);
            };
            $key = implode(', ', $keys);
            $keyLen = self::stringLength($key);
            if ($keyLen > $longest) $longest = $keyLen;
            $toDisplay[$key] = $hint;
        }
//        print_r($toDisplay);
        foreach ($toDisplay as $param => $hint) {
            $out .= self::fillToLeft($param, $longest + 6);
            $singleHint = $hint['hint'];
            $hintParts = explode('||', $singleHint);
            if (count($hintParts) > 1) {
                foreach ($hintParts as $numb => $line) {
                    if ($numb == 0) {
                        $out .= trim($line) . EOL;
                    } else {
                        $out .= str_repeat(' ', $longest + 6) . trim($line) . EOL;
                    }
                }

                $out .= EOL;
            } else {
                $out .= $singleHint . EOLx2;
            }
        }

        if (!$this->colorsEnabled) {
            $out = self::removeAnsi($out);
        }
        echo $out . EOLx2;

    }

    private function init($projectName, $tree = null)
    {


        if (file_exists($this->currentVersionFile)) {

            $projectName = $this->getCurrentProjectName();
            $currentVersion = $this->getCurrentVersionToString();
            $lastUpdated = $this->getCurrentLastUpdated();

            die (sprintf(
                    "File \e[32m%s\e[0m already exists. 
            \nCurrent version of \e[32m%s\e[0m project is \e[32m%s\e[0m last updated at \e[32m%s\e[0m 
            \nIf you want to re-initialize the project's version remove this file first.
            \nInstead maybe you want update current version with --mode `set` or `update`.\nCheck the help for more details with command: \e[32mphp version.php --help\e[0m
             ",
                    $this->currentVersionFile,
                    $projectName,
                    $currentVersion,
                    $lastUpdated)
                . EOLx2);
        }

        $now = new DateTime('now');
        $lastUpdate = $now->format('Y-m-d H:i:s');
        $now2 = new DateTime('now');
        $lastUpdateLink = $now2->format('Y-m-d+H:i:s');

        $vers = [
            'project_name' => $projectName, 'version' => '0.0.1', 'state' => 'alpha', 'last_update' => $lastUpdate,
        ];

        if (!is_null($tree)) {
            $vers['working_tree'] = $tree;
        }

        file_put_contents($this->currentVersionFile, json_encode($vers, JSON_PRETTY_PRINT));
        if (!file_exists('README.md')) {
            $fileTemplate = "## `{$projectName}` project

[![State](https://img.shields.io/static/v1?label=beta&message=1.0.0&color=blue)](https://github.com/biesior/box-drawer/tree/1.0.0-beta) <!-- __VERSION_LINE__ -->
![Updated](https://img.shields.io/static/v1?label=upated&message={$lastUpdateLink}&color=lightgray) <!-- __UPDATED_LINE__ -->";

            file_put_contents('README.md', $fileTemplate);

        }
        echo sprintf("Versioning for project \e[32m%s\e[0m was initialized with version \e[32m0.0.1-alpha\e[0m!", $projectName) . EOLx2;
    }

    private function kill($force = false)
    {
        $out = '';

        if (file_exists($this->currentVersionFile)) {
            $projectName = $this->getCurrentProjectName();
            $currentVersionLong = $this->getCurrentVersionToString();
            $lastUpdated = $this->getCurrentLastUpdated();
        } else {
            $out = "File \e[1;32m{$this->currentVersionFile}\e[0m doesn't exist, nothing to kill.\n\nBye!" . EOLx2;
            if (!$this->colorsEnabled) {
                $out = self::removeAnsi($out);
            }
            die($out);
        }
        $renameTo = 'zzz_unused_' . time() . '_' . $this->currentVersionFile;
        if (!in_array($force, ['soft', 'hard'])) {
            $out .= 'You are trying to remove ' . $this->currentVersionFile . ' from your project and disable this functionality in it' . EOLx2;
            $out .= "Of course it's your choice and if you are sure repeat this command with \e[32mforce\e[0m value, like" . EOLx2;
            $out .= "\e[32mphp version.php --kill=soft\e[0m \n\n    to rename `{$this->currentVersionFile}` to `{$renameTo}` or \n\n\e[32mphp version.php --kill=hard\e[0m \n\n    to remove it totally";
            if (!$this->colorsEnabled) {
                $out = self::removeAnsi($out);
            }
            die($out . EOLx2);
        }
        if (file_exists($this->currentVersionFile)) {

            $projectName = $this->getCurrentProjectName();
            $currentVersionLong = $this->getCurrentVersionToString();
            $currentVersionShort = $this->getCurrentVersion();
            $currentState = $this->getCurrentState();
            $lastUpdated = $this->getCurrentLastUpdated();
        }

        $out .= sprintf("Versioning for project \e[32m%s\e[0m was killed, last known version was \e[32m%s\e[0m", $projectName, $currentVersionLong) . EOLx2;

        if ($force == 'soft') {
            rename($this->currentVersionFile, $renameTo);
            $out .= sprintf("File \e[1;32m%s\e[0m was renamed to \e[1;32m%s\e[0m", $this->currentVersionFile, $renameTo);
        } elseif ($force == 'hard') {
            unlink($this->currentVersionFile);
            $out .= sprintf("File \e[1;32m%s\e[0m was \e[1;31mdeleted\e[0m", $this->currentVersionFile);
        }
        $out .= EOLx2;

        $out .= "The functionality is disabled now." . EOL;
        if ($force == 'soft') {
            $out .= "\nYou can manually restore it by renaming the backup file to \e[1;32m{$this->currentVersionFile}\e[0m." . EOL;
        }
        $out .= "To recreate it with last known version, initialize it again and set last known version and state like:" . EOLx2;

        $out .= sprintf("\e[1;32mphp version.php -i=\"%s\"\e[0m", $projectName) . EOL;
        $out .= sprintf("\e[1;32mphp version.php -m set -n %s -s %s\e[0m", $currentVersionShort, $currentState) . EOL;


        $out .= EOL . "Bye \e[31m;(\e[0m" . EOLx2;


        if (!$this->colorsEnabled) {
            $out = self::removeAnsi($out);
        }
        echo $out;
    }

    private function set($version, $toState)
    {
        $output = '';

        $now = new DateTime('now');
        $lastUpdate = $now->format('Y-m-d+H:i:s');
        $newVersionFull = $version . ($toState == 'stable' ? '' : '-' . $toState);
        $vers = [
            'project_name' => $this->getCurrentProjectName(), 'version' => $version, 'state' => $toState, 'last_update' => $lastUpdate,
        ];
        file_put_contents($this->currentVersionFile, json_encode($vers));

        $repository = $this->repository . $newVersionFull;

        $output .= EOLx2;
        $this->searchAndUpdate(
            'README.md',
            "[![State](https://img.shields.io/static/v1?label=%s&message=%s&color=blue)](%s)",
            '<!-- __VERSION_LINE__ -->',
            $toState, $version, $repository
        );

        $output .= EOLx2;
        $this->searchAndUpdate(
            'README.md',
            "![Updated](https://img.shields.io/static/v1?label=upated&message=%s&color=lightgray)",
            '<!-- __UPDATED_LINE__ -->',
            $lastUpdate
        );
        $output .= EOLx2;


        // tagging before commit has no sense
//        $newTagName = $this->getCurrentVersionToString();
//        $tagCmd = "git tag {$newTagName}";
//        system($tagCmd);


        if (!$this->colorsEnabled) {
            $output = self::removeAnsi($output);
        }

        $output .= "Please push your update to GitHub and don't forget to publish new release!" . EOLx2;
        $output .= 'Bye!' . EOLx2;

    }

    private function update($part, $toState = null)
    {

        if (is_null($toState)) {
            $currentState = $this->getCurrentState();
            if (!is_null($currentState)) {
                $toState = $currentState;
            } else {
                die ("\e[31mState couldn't be retrieved. Please fix your " . $this->currentVersionFile . " fole and retry.\n\nBye!\e[0m" . EOLx2);
            }
        }

        $output = '';
        $currentData = $this->getCurrentVersionFromFile();
        $oldVersion = $currentData['version'];
        $oldState = $currentData['state'];
        $oldVersionFull = $oldVersion . ($oldState == 'stable' ? '' : '-' . $oldState);
        $newVersion = $this->calculateRise($oldVersion, $part, $toState, true);
        $newVersionFull = $newVersion . ($toState == 'stable' ? '' : '-' . $toState);
        $revertCommand = "php version.php -m set -n {$oldVersion} -s {$oldState}";
        $this->set($newVersion, $toState);
        $output .= (PHP_EOL .
                sprintf(
                    "Your version was updated from \e[0;32m%s\e[0m to \e[0;32m%s\e[0m

To revert this change please run: \e[0;32m%s\e[0m 
",
                    $oldVersionFull, $newVersionFull, $revertCommand
                )
            ) . PHP_EOL;;

        if (!$this->colorsEnabled) {
            $output = self::removeAnsi($output);
        }
        echo $output;

    }


    /**
     * Non-public methods for repeating logic a.k.a helpers starts here
     *
     * DO NOT move this method in the structure without reason.
     *
     * @return string Formatted filename:number + phpdoc
     * @throws ReflectionException
     * @internal This method returns the filename and line number where group of specific methods starts
     */
    private function ____helperMethods(): string
    {
        return self::describeMethodItself();
    }

    private function searchAndUpdate(string $filename, string $sprintfStr, string $lineEndsWith, ...$params)
    {

        $replaced = '';
        if (count($params) == 1) {
            $replaced = sprintf($sprintfStr, $params[0]);
        } else if (count($params) == 2) {
            $replaced = sprintf($sprintfStr, $params[0], $params[1]);
        } elseif (count($params) == 3) {
            $replaced = sprintf($sprintfStr, $params[0], $params[1], $params[2]);
        } else {
            throw new Exception(
                sprintf('Method searchAndUpdate requires max 3 additional parameters %s given', count($params)),
                1597754663
            );
        }

        $txt = file($filename);

        foreach ($txt as $lineNo => $line) {

            if (self::endsWith(trim($line), trim($lineEndsWith))) {
                $txt[$lineNo] = $replaced . ' ' . $lineEndsWith . EOL;
                echo sprintf("Changed line \e[32m%s\e[0m of \e[32m%s\e[0m to:", $lineNo, $filename) . EOL;
                echo "\e[36m" . $replaced . EOLx2 . "\e[0m";
            };
        }
        file_put_contents($filename, implode('', $txt));


    }

    private function calculateRise($currentVersion, $part, $state, $returnNewVersion = false)
    {
//        var_dump($currentVersion);
        $vp = explode('.', $currentVersion);


        if (count($vp) != 3 || !in_array($state, ['alpha', 'beta', 'stable'])) {
            throw new Exception('Invalid format for version, wrong  state, aborting', 1597705999);
        };
        $vp[0] = intval($vp[0]);
        $vp[1] = intval($vp[1]);
        $vp[2] = intval($vp[2]);
        switch ($part) {
            case 'patch':
                $vp[2]++;
                break;
            case 'minor':
                $vp[1]++;
                $vp[2] = 0;
                break;
            case 'major':
                $vp[0]++;
                $vp[1] = 0;
                $vp[2] = 0;
                break;
        }
        $newVersion = implode('.', $vp);


        if ($returnNewVersion) {
            return $newVersion;
        }
        $newState = ($state == 'stable') ? '' : '-' . $state;

        return [
            sprintf("Generate new \e[36m%s%s\e[0m with ", $newVersion, $newState),
            sprintf("\e[36mphp version.php update %s\e[0m", $state)
        ];
    }

    /**
     * Public static methods starts here
     *
     * DO NOT move this method in the structure without reason.
     *
     * @return string Formatted filename:number + phpdoc
     * @throws ReflectionException
     * @internal This method returns the filename and line number where group of specific methods starts
     */
    private function ____publicStaticMethods(): string
    {
        return self::describeMethodItself();
    }

    /**
     * Returns string's length
     *
     * @param string $variable
     * @param bool   $removeAnsi
     *
     * @return bool|false|int
     */
    public static function stringLength($variable, $removeAnsi = true)
    {
        if ($removeAnsi) {
            $variable = self::removeAnsi($variable);
        }
        return mb_strlen($variable);
    }

    public static function startsWith($haystack, $needle)
    {
        $length = self::stringLength($needle, true);
        return substr($haystack, 0, $length) === $needle;
    }

    public static function endsWith($haystack, $needle)
    {
        $length = self::stringLength($needle, true);
        if (!$length) {
            return true;
        }

        $ressubstr = substr($haystack, -$length);

        $res = $ressubstr === $needle;

        return $res;
    }

    /**
     *  TODO improve phpdoc
     *
     * @param string  $value
     * @param integer $minLen
     * @param string  $withChar
     *
     * @return string
     */
    public static function fillToLeft($value, $minLen, $withChar = ' '): string
    {
        $len = self::stringLength($value);
        if ($len < $minLen) {
            $diff = $minLen - $len;
            return ($value . str_repeat($withChar, $diff));
        } else {
            return $value;
        }
    }

    /**
     * Fill to right
     *
     * @param string  $value
     * @param integer $minLen
     * @param string  $withChar
     *
     * @return string
     */
    public static function fillToRight($value, int $minLen, string $withChar = ' '): string
    {
        $len = self::stringLength($value);
        if ($len < $minLen) {
            $diff = $minLen - $len;
            return (str_repeat($withChar, $diff) . $value);
        } else {
            return $value;
        }
    }


    public static function removeAnsi($value)
    {
        return preg_replace('#\\e[[][^A-Za-z]*[A-Za-z]#', '', $value);
    }


    public static function removeColon(string $value): string
    {
        return str_replace(':', '', $value);
    }


    /**
     * Methods for getting current version data starts here
     *
     * DO NOT move this method in the structure without reason.
     *
     * @return string Formatted filename:number + phpdoc
     * @throws ReflectionException
     * @internal This method returns the filename and line number where group of specific methods starts
     */
    private function ____methodsForGettingCurrentVersion(): string
    {
        return self::describeMethodItself();
    }

    /**
     * @param bool $shyData
     *
     * @return array
     */
    protected function getCurrentVersionFromFile($shyData = false): array
    {
        if (!file_exists($this->currentVersionFile)) {
            if ($shyData) die('No data about current version or data are invalid');
            die(sprintf("there is no `%s` file, please create it with \e[36mphp version.php --init\e[0m command", $this->currentVersionFile) . EOLx2);
        }
        $currentData = json_decode(file_get_contents($this->currentVersionFile), true);
        if (is_null($currentData)) {
            if ($shyData) die('No data about current version or data are invalid');
            die(sprintf("\e[31mInvalid data in `%s` file.\e[0m\n\nPlease fix it or remove the file and initialize your version again with: \e[32mphp version.php --init\e[0m", $this->currentVersionFile) . EOLx2);
        };
        return $currentData;
    }

    protected function getCurrentVersionToString()
    {
        $cv = $this->getCurrentVersionFromFile();
        return $cv['version'] . ($cv['state'] == 'stable' ? '' : '-' . $cv['state']);
    }

    protected function getCurrentVersion()
    {
        $cv = $this->getCurrentVersionFromFile();
        return trim($cv['version']);
    }

    protected function getCurrentLastUpdated()
    {
        $cv = $this->getCurrentVersionFromFile();
        return $cv['last_update'];
    }

    protected function getCurrentProjectName()
    {
        $cv = $this->getCurrentVersionFromFile();
        return $cv['project_name'];
    }

    protected function getCurrentState()
    {
        $cv = $this->getCurrentVersionFromFile();
        return $cv['state'];
    }

    /**
     * @return string
     */
    protected function getCurrentEnv(): string
    {
        return (php_sapi_name() == 'cli') ? 'cli' : 'web';
    }

    private function getOptValue(array $options, string $short = null, string $long = null)
    {
        if (!is_null($short) && array_key_exists($short, $options)) {
            return $options[$short];
        } elseif (!is_null($long) && array_key_exists($long, $options)) {
            return $options[$long];
        } else {
            return null;
        }
    }

    /**
     * TODO improve phpdoc
     *
     * @param string $methodName Method which should be described, if null backtrace is used to find calling methodname
     *
     * @return string
     * @throws ReflectionException
     * @internal Used for debug only
     */
    private function describeMethodItself($methodName = null)
    {
        if (is_null($methodName)) {
            $methodName = debug_backtrace()[1]['function'];
        }
        $method = new \ReflectionMethod(VersionUpdater::class, $methodName);
        $file = $method->getFileName();
        $line = $method->getStartLine();
        $phpdoc = $method->getDocComment();
        $displayName = str_replace('____', '', $methodName);


        return sprintf("`%s` starts at `%s:%d`\n\nphpdoc:\n\n     %s", $displayName, $file, $line, $phpdoc);
    }
}


$versionUpdater = new VersionUpdater();
