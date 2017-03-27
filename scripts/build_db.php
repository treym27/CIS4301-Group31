#!/usr/bin/env php
<?php

// connect to the database
// should replace parameters with getenv()
echo "[*] Connecting to database\n";
$guser = getenv('DBUSER');
$gpass = getenv('DBPASS');
$gconn = getenv('DBCONN');
if (!$guser || !$gpass || !$gconn) {
    die("[!] Need to set environment variables: DBUSER, DBPASS, DBCONN\n");
}

$connection = oci_connect($username = $guser,
                          $password = $gpass,
                          $connection_string = $gconn);

// check if the connection failed
// may need to connect to the vpn if it did
if (! $connection) {
    $err = oci_error();
    die("[!] oci_connect failed: ".$err."\n");
}

// check for command line arg about deleting
if ($argc >= 2 && $argv[1] == "delete") {
    $statement = oci_parse($connection, "delete from account");
    oci_execute($statement);
    oci_free_statement($statement);
    $statement = oci_parse($connection, "delete from transaction");
    oci_execute($statement);
    oci_free_statement($statement);
    // should reset the sequence counter here
    echo "[*] Database deleted\n";
    oci_close($connection);
    exit();
}

// insert the administrator into the database first
echo "[*] Inserting admin\n";
$admin_pwd = password_hash("admin", PASSWORD_DEFAULT);
$admin_insert = "insert into account values('admin', '".$admin_pwd."', 'administrator', '000-00-0000', '1-JAN-2017', '1-JAN-2017', 0, 's', 'c', 'ST', 1, 0, 'genderless')";
$statement = oci_parse($connection, $admin_insert);
oci_execute($statement);
oci_free_statement($statement);

// generate accounts from the names file
echo "[*] Inserting users\n";
$count = 0;
$name_file = fopen("scripts/data/male_names.txt", "r") or die("Unable to open file!\n");
while ((list ($fname, $lname) = fscanf($name_file, "%s %s")) != false) {
    echo "\r\t[*] Insert #".(++$count);
    $insert_cmd = "insert into account values('".$fname.".".$lname."@gmail.com', '";
    $insert_cmd = $insert_cmd.password_hash($fname, PASSWORD_DEFAULT)."', '";
    $insert_cmd = $insert_cmd.$fname." ".$lname."', '";
    $insert_cmd = $insert_cmd.mt_rand(100,999)."-".mt_rand(10,99)."-".mt_rand(1000,9999)."', '";
    $insert_cmd = $insert_cmd.mt_rand(1,31)."-JAN-".mt_rand(1950,2000)."', '";
    $insert_cmd = $insert_cmd.mt_rand(1,31)."-JAN-".mt_rand(2015,2017)."', ";
    $insert_cmd = $insert_cmd."0, '";
    $insert_cmd = $insert_cmd."street', '";
    $insert_cmd = $insert_cmd."city', '";
    $insert_cmd = $insert_cmd."ST', ";
    $insert_cmd = $insert_cmd."0, ";
    $insert_cmd = $insert_cmd.mt_rand(1000000000,9999999999).", '";
    $insert_cmd = $insert_cmd."male')";
    // echo $insert_cmd."\n";
    $statement = oci_parse($connection, $insert_cmd);
    oci_execute($statement);
    oci_free_statement($statement);
}
fclose($name_file);
// female names
$name_file = fopen("scripts/data/female_names.txt", "r") or die("Unable to open file!\n");
while ((list ($fname, $lname) = fscanf($name_file, "%s %s")) != false) {
    echo "\r\t[*] Insert #".(++$count);
    $insert_cmd = "insert into account values('".$fname.".".$lname."@gmail.com', '";
    $insert_cmd = $insert_cmd.password_hash($fname, PASSWORD_DEFAULT)."', '";
    $insert_cmd = $insert_cmd.$fname." ".$lname."', '";
    $insert_cmd = $insert_cmd.mt_rand(100,999)."-".mt_rand(10,99)."-".mt_rand(1000,9999)."', '";
    $insert_cmd = $insert_cmd.mt_rand(1,31)."-JAN-".mt_rand(1950,2000)."', '";
    $insert_cmd = $insert_cmd.mt_rand(1,31)."-JAN-".mt_rand(2015,2017)."', ";
    $insert_cmd = $insert_cmd."0, '";
    $insert_cmd = $insert_cmd."street', '";
    $insert_cmd = $insert_cmd."city', '";
    $insert_cmd = $insert_cmd."ST', ";
    $insert_cmd = $insert_cmd."0, ";
    $insert_cmd = $insert_cmd.mt_rand(1000000000,9999999999).", '";
    $insert_cmd = $insert_cmd."female')";
    // echo $insert_cmd."\n";
    $statement = oci_parse($connection, $insert_cmd);
    oci_execute($statement);
    oci_free_statement($statement);
}
fclose($name_file);
echo "\n";

$statement = oci_parse($connection, "select email_address from account");
oci_execute($statement);
$trans_count = 0;
echo "[*] Inserting transactions\n";
while (($row = oci_fetch_object($statement)) != false) {
    if ($row->EMAIL_ADDRESS === 'admin')
        continue;

    echo "\r\t[*] Transactions #".++$trans_count;
    for ($i = 0; $i < 200; $i++) {
        $tstate = oci_parse($connection, "insert into transactions values(seq_transaction.nextval, )");
        oci_execute($tstate);
        oci_free_statement($tstate);
    }
}
echo "\n";
oci_free_statement($statement);

// check to see what is in the database
$statement = oci_parse($connection, "select * from account");
oci_execute($statement);
$aftercount = -1;
while (($row = oci_fetch_object($statement)) != false) {
    ++$aftercount;
}
oci_free_statement($statement);

// make sure everything checks out
if ($aftercount != $count) {
    echo "[!] Database build failed\n";
} else {
    echo "[+] Database build complete\n";
}

// all done
oci_close($connection);
?>
