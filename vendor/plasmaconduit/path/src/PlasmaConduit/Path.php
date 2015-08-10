<?php
namespace PlasmaConduit;

class Path {

    /**
     * This function takes a variable amount of strings and joins
     * them together so that they form a valid file path.
     *
     * @param {String ...} $peice - The peices of the file path
     * @returns {String}          - The final file path
     */
    static public function join() {
        $peices = array_filter(func_get_args(), function($value) {
            return $value;
        });
        return self::normalize(implode("/", $peices));
    }

    /**
     * This function takes a valid file path and nomalizes it into
     * the simplest form possible.
     *
     * @param {String} $path - The path to normalize
     * @returns {String}     - The normailized path
     */
    static public function normalize($path) {
        if (!strlen($path)) {
            return ".";
        }

        $isAbsolute    = $path[0];
        $trailingSlash = $path[strlen($path) - 1];

        $up     = 0;
        $peices = array_values(array_filter(explode("/", $path), function($n) {
                    return !!$n;
                }));
        for ($i = count($peices) - 1; $i >= 0; $i--) {
            $last = $peices[$i];
            if ($last == ".") {
                array_splice($peices, $i, 1);
            } else if ($last == "..") {
                array_splice($peices, $i, 1);
                $up++;
            } else if ($up) {
                array_splice($peices, $i, 1);
                $up--;
            }
        }

        $path = implode("/", $peices);

        if (!$path && !$isAbsolute) {
            $path = ".";
        }

        if ($path && $trailingSlash == "/") {
            $path .= "/";
        }

        return ($isAbsolute == "/" ? "/" : "") . $path;
    }

}