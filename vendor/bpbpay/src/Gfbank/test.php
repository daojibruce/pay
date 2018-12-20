#!/usr/bin/env php
<?php
function with($obj){
    return $obj;
}
require "Rsasha1.php";
require "transaction.php";

$t = Bpbpay\Gfbank\transaction::init();
$t->subaccountList();