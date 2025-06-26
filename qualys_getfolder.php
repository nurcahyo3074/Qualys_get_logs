<!DOCTYPE html>
<html>
<head>
    <title>Run SSH Command in Real Time</title>
</head>
<body>
    <h2>Execute Remote Script (Real-Time)</h2>
    <form method="POST">
        Enter Hostname: <input type="text" name="hostname" required>
        <input type="submit" name="submit" value="Run Command">
    </form>
    <hr>

<?php
if (isset($_POST['submit'])) {
    // Turn off output buffering
    @ini_set('output_buffering', 'off');
    @ini_set('zlib.output_compression', false);
    @ob_implicit_flush(true);
    ob_end_flush();

    $hostname = escapeshellarg(trim($_POST['hostname']));  // Sanitize input
    #$command = "ssh marifian@mytpmls1276 /aaahome/marifian/scripts/qualys/get_qualysfolder_direct.sh $hostname";
    $sshCommand = "ssh -i /var/www/.ssh/id_rsa marifian@mytpmls1276 /aaahome/marifian/scripts/qualys/get_qualysfolder_direct.sh $hostname";

    echo "<strong>Running command:</strong> <code>$command</code><br><br>";
    echo "<pre>";

    $descriptorspec = [
        1 => ["pipe", "w"], // stdout
        2 => ["pipe", "w"]  // stderr
    ];

    $process = proc_open($command, $descriptorspec, $pipes);

    if (is_resource($process)) {
        while (!feof($pipes[1]) || !feof($pipes[2])) {
            if (!feof($pipes[1])) {
                echo htmlspecialchars(fgets($pipes[1]));
            }
            if (!feof($pipes[2])) {
                echo htmlspecialchars(fgets($pipes[2]));
            }
            flush();
            usleep(100000); // 0.1 sec
        }
        fclose($pipes[1]);
        fclose($pipes[2]);
        $return_value = proc_close($process);

        echo "</pre>";
        echo "<br><strong>Command finished with exit code $return_value.</strong>";
    } else {
        echo "❌ Failed to start process.";
    }
}
?>
</body>
</html>
