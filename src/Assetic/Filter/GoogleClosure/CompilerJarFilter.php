<?php

namespace Assetic\Filter\GoogleClosure;

use Assetic\Asset\AssetInterface;

/*
 * This file is part of the Assetic package.
 *
 * (c) Kris Wallsmith <kris.wallsmith@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Filter for the Google Closure Compiler JAR.
 *
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class CompilerJarFilter extends BaseCompilerFilter
{
    private $jarPath;
    private $javaPath;

    public function __construct($jarPath, $javaPath = '/usr/bin/java')
    {
        $this->jarPath = $jarPath;
        $this->javaPath = $javaPath;
    }

    public function filterDump(AssetInterface $asset)
    {
        $cleanup = array();

        $options = array(
            $this->javaPath,
            '-jar',
            $this->jarPath,
        );

        if (null !== $this->compilationLevel) {
            $options[] = '--compilation_level';
            $options[] = $this->compilationLevel;
        }

        if (null !== $this->jsExterns) {
            $cleanup[] = $externs = tempnam(sys_get_temp_dir(), 'assetic_google_closure_compiler');
            file_put_contents($externs, $this->jsExterns);
            $options[] = '--externs';
            $options[] = $externs;
        }

        if (null !== $this->externsUrl) {
            $cleanup[] = $externs = tempnam(sys_get_temp_dir(), 'assetic_google_closure_compiler');
            file_put_contents($externs, file_get_contents($this->externsUrl));
            $options[] = '--externs';
            $options[] = $externs;
        }

        if (null !== $this->excludeDefaultExterns) {
            $options[] = '--use_only_custom_externs';
        }

        if (null !== $this->formatting) {
            $options[] = '--formatting';
            $options[] = $this->formatting;
        }

        if (null !== $this->useClosureLibrary) {
            $options[] = '--manage_closure_dependencies';
        }

        $options[] = '--js';
        $options[] = $cleanup[] = $input = tempnam(sys_get_temp_dir(), 'assetic_google_closure_compiler');
        file_put_contents($input, $asset->getBody());

        // todo: check for a valid return code
        $output = shell_exec(implode(' ', array_map('escapeshellarg', $options)));

        // cleanup temp files
        array_map('unlink', $cleanup);

        $asset->setBody($output);
    }
}
