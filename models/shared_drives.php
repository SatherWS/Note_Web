<?php
    class Storage {
        // get the available storage in /mnt folder
        public function getDirSpace() {
            $gigs = [];
            $parent = "/mnt";
            $drives = scandir($parent);
            $n = 2;
            while ($n < count($drives)) {
                $bytes = disk_free_space($parent."/".$drives[$n]);
                $gb = $bytes / 1E+9;
                array_push($gigs, $gb);
                $n++;
            }
            return $gigs;
        }
    }

    $test = new Storage();
    $drive = $test -> getDirSpace();

    foreach ($drives as $d) {
        echo $d."\n";
    }

?>