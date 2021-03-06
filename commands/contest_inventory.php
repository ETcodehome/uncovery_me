<?php
    require_once('includes/mysql.inc.php');

    $sql = "SELECT id FROM minecraft_srvr.`contest_contests` WHERE `status` = 'active';";
    $D = umc_mysql_fetch_all($sql);
    $contest_id = $D[0]['id'];
    echo "Making inventory of Contest $contest_id:\n";

    $lot_name = "con_" . $contest_id . "_%";
    $sql2 = "SELECT * FROM minecraft_worldguard.region_cuboid LEFT JOIN world ON region_cuboid.world_id=world.id WHERE region_id LIKE '$lot_name';";
    $D = $entry = umc_mysql_fetch_all($sql2);
    foreach ($D as $entry) {
        $world_name = $entry['name'];
        $lot_name = $entry['region_id'];
        echo"Processing lot $lot_name in $world_name...";
        // ./minecount -wc -b "-16,0,-16 -1,256,-1" ../../world world-blocks.json^M
        $blocks = "{$entry[min_x]},{$entry[min_y]},{$entry[min_z]} {$entry[max_x]},{$entry[max_y]},{$entry[max_z]}";

        $server_path = $UMC_PATH_MC . '/server';
        $tool_path = $server_path . '/minecount/minecount -wc -b "' . $blocks . '"';
        $world_path = $server_path . '/bukkit/'. $world_name;
        $data_path = $server_path . '/minecount/output/' . $lot_name . '.json';
        $cmd = "$tool_path $world_path $data_path";
        exec($cmd);
        echo "Done!\n";
    }

?>