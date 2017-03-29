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
    $statement = oci_parse($connection, "delete from makes");
    oci_execute($statement);
    oci_free_statement($statement);
    $statement = oci_parse($connection, "delete from account");
    oci_execute($statement);
    oci_free_statement($statement);
    $statement = oci_parse($connection, "delete from transaction");
    oci_execute($statement);
    oci_free_statement($statement);
    $statement = oci_parse($connection, "drop sequence seq_transaction");
    oci_execute($statement);
    oci_free_statement($statement);
    $statement = oci_parse($connection, "create sequence seq_transaction minvalue 1 start with 1 increment by 1 cache 10");
    oci_execute($statement);
    oci_free_statement($statement);
    $statement = oci_parse($connection, "drop sequence seq_sm");
    oci_execute($statement);
    oci_free_statement($statement);
    $statement = oci_parse($connection, "create sequence seq_sm minvalue 1 start with 1 increment by 1 cache 10");
    oci_execute($statement);
    oci_free_statement($statement);
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
    if (mt_rand(1,100) === 50)
        $insert_cmd = $insert_cmd."1, '";
    else
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
    if (mt_rand(1,100) === 50)
        $insert_cmd = $insert_cmd."1, '";
    else
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
$sm_count = 0;
echo "[*] Inserting transactions\n";
while (($row = oci_fetch_object($statement)) != false) {
    if ($row->EMAIL_ADDRESS === 'admin')
        continue;

    // 2 times a month, 12 months, 6 years
    $salary = mt_rand(500,2000);
    $rent = mt_rand(500,1000);
    for ($i = 0; $i < 12*6; $i++) {
        $t0 = "insert into transaction values(seq_transaction.nextval, TIMESTAMP '";
        $t1 = $t0.intval(2005+($i/6))."-".($i%12 + 1)."-".(21)." 00:00:00.00', 'Salary Payment', 'complete', ";
        $t0 = $t0.intval(2005+($i/6))."-".($i%12 + 1)."-".(7)." 00:00:00.00', 'Salary Payment', 'complete', ";
        $t0 = $t0.intval($salary*(1+(0.02*intval($i/6)))).")";
        $t1 = $t1.intval($salary*(1+(0.02*intval($i/6)))).")";
        // echo $t0."\n";
        // echo $t0."\n";

        // first salary payment
        $tstate = oci_parse($connection, $t0);
        oci_execute($tstate);
        echo "\r\t[*] Transactions #".++$trans_count;
        $tstate = oci_parse($connection, "insert into makes values('admin', '".$row->EMAIL_ADDRESS."', ".$trans_count.", null)");
        oci_execute($tstate);

        // generate a random number to decide if we should social media post
        if (mt_rand(1,100) <= 10) {
            $sm_count++;
            $sm = "insert into social_media_post values(seq_sm.nextval, TIMESTAMP '".intval(2005+($i/6))."-".($i%12 + 1)."-".(21)." 00:00:00.00', 'I just got paid!!!!')";
            $smstate = oci_parse($connection, $sm);
            oci_execute($smstate);

            $tstate = oci_parse($connection, $t1);
            oci_execute($tstate);
            echo "\r\t[*] Trans+smpost #".++$trans_count;
            $tstate = oci_parse($connection, "insert into makes values('admin', '".$row->EMAIL_ADDRESS."', ".$trans_count.", ".$sm_count.")");
            oci_execute($tstate);
        } else {
            $tstate = oci_parse($connection, $t1);
            oci_execute($tstate);
            echo "\r\t[*] Transactions #".++$trans_count;
            $tstate = oci_parse($connection, "insert into makes values('admin', '".$row->EMAIL_ADDRESS."', ".$trans_count.", null)");
            oci_execute($tstate);
        }

        // rent payment
        $tstate = oci_parse($connection, "insert into transaction values(seq_transaction.nextval, TIMESTAMP '".intval(2005+($i/6))."-".($i%12 + 1)."-".(1)." 00:00:00.00', 'Rent Payment', 'complete', ".$rent.")");
        oci_execute($tstate);
        echo "\r\t[*] Transactions #".++$trans_count;
        $tstate = oci_parse($connection, "insert into makes values('".$row->EMAIL_ADDRESS."', 'admin', ".$trans_count.", null)");
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
