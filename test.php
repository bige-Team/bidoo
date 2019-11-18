<?php
$shm_id = shmop_open(0xff3, "c", 0644, 1);//8 -> size(bytes), c -> create shared memory
shmop_write($shm_id, 3, 0);
shmop_delete($shm_id);

$shm_id = shmop_open(0xff3, "w", 0, 0);
$res = shmop_read($shm_id, 0, 1);
echo $res . "\n";
?>