<?php

/**
 * This file is part of Gitonomy.
 *
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 * (c) Julien DIDIER <genzo.wm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Format_Checker\Parse;

class DiffParse extends BaseParse
{

    protected $diffs = array();

    protected function doParse()
    {
        while (!$this->isFinished()) {
            // 1. title
            $vars = $this->consumeRegexp('/diff --git (a\/.*) (b\/.*)\n/');
            $oldName = $vars[1];
            $newName = $vars[2];
            $oldIndex = null;
            $newIndex = null;
            $oldMode = null;
            $newMode = null;

            // 2. mode
            if ($this->expects('new file mode ')) {
                $newMode = $this->consumeTo("\n");
                $this->consumeNewLine();
                $oldMode = null;
            }
            if ($this->expects('old mode ')) {
                $oldMode = $this->consumeTo("\n");
                $this->consumeNewLine();
                $this->consume('new mode ');
                $newMode = $this->consumeTo("\n");
                $this->consumeNewLine();
            }
            if ($this->expects('deleted file mode ')) {
                $oldMode = $this->consumeTo("\n");
                $newMode = null;
                $this->consumeNewLine();
            }

            if ($this->expects('similarity index ')) {
                $this->consumeRegexp('/\d{1,3}%\n/');
                $this->consume('rename from ');
                $this->consumeTo("\n");
                $this->consumeNewLine();
                $this->consume('rename to ');
                $this->consumeTo("\n");
                $this->consumeNewLine();
            }

            // 4. File informations
            $isBinary = false;
            if ($this->expects('index ')) {
                $oldIndex = $this->consumeHash();
                $this->consume('..');
                $newIndex = $this->consumeHash();
                if ($this->expects(' ')) {
                    $vars = $this->consumeRegexp('/\d{6}/');
                    $newMode = $oldMode = $vars[0];
                }
                $this->consumeNewLine();

                if ($this->expects('--- ')) {
                    $oldName = $this->consumeTo("\n");
                    $this->consumeNewLine();
                    $this->consume('+++ ');
                    $newName = $this->consumeTo("\n");
                    $this->consumeNewLine();
                } elseif ($this->expects('Binary files ')) {
                    $vars = $this->consumeRegexp('/(.*) and (.*) differ\n/');
                    $isBinary = true;
                    $oldName = $vars[1];
                    $newName = $vars[2];
                }
            }

            $oldName = $oldName === '/dev/null' ? null : substr($oldName, 2);
            $newName = $newName === '/dev/null' ? null : substr($newName, 2);
            $oldIndex = preg_match('/^0+$/', $oldIndex) ? null : $oldIndex;
            $newIndex = preg_match('/^0+$/', $newIndex) ? null : $newIndex;
            $file = array(
                'file_name'=>$newName ? $newName : $oldName,
            );

            // 5. Diff
            while ($this->expects('@@ ')) {
                $vars = $this->consumeRegexp('/-(\d+)(?:,(\d+))? \+(\d+)(?:,(\d+))?/');
                $rangeOldStart = $vars[1];
                $rangeOldCount = $vars[2];
                $rangeNewStart = $vars[3];
                $rangeNewCount = isset($vars[4]) ? $vars[4] : $vars[2]; // @todo Ici, t'as pris un gros raccourci mon loulou
                $this->consume(' @@');
                $this->consumeTo("\n");
                $this->consumeNewLine();

                // 6. Lines
                $add_lines = $del_lines = '';
                while (true) {
                    if ($this->expects(' ')) {
                        $this->consumeTo("\n");
                    } elseif ($this->expects('+')) {
                        $add_lines .= $this->consumeTo("\n")."\n";
                    } elseif ($this->expects('-')) {
                        $del_lines .= $this->consumeTo("\n")."\n";
                    } elseif ($this->expects("\ No newline at end of file")) {
                        // Ignore this case...
                    } else {
                        break;
                    }
                    $this->consumeNewLine();
                }

                $file['changes'][] = array(
                    'range_start'=>$rangeNewStart,
                    'range_count'=>$rangeNewCount,
                    'add_content'=>$add_lines,
                    'del_content'=>$del_lines,
                );
            }

            $this->diffs[] = $file;
        }
        return $this->diffs;
    }

    public function getDiffs(){
        return $this->diffs;
    }
}
