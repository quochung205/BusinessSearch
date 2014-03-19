<?php

include 'init.php';


$res = $DB->query('SELECT * FROM `info`')->rows();

foreach ($res as $r)
{
	$r = get_object_vars($r);
	list($r['address_city'], $r['address_zipcode']) = array_pad(explode(' CA ', $r['address_csz']), 2, '');
	list($r['agent_address_city'], $r['agent_address_zipcode']) = array_pad(explode(' CA ', $r['agent_address_csz']), 2, '');
	$id = $r['id'];
	unset($r['id']);
	foreach ($r as $i => $v)
	{
        $v = preg_replace('(\s+)', ' ', $v);
        $v = htmlspecialchars_decode($v);
        $v = str_replace('&#39;', '\'', $v);
        $r[$i] = $v;
	}

	$DB->update('info', $r, '`id` = ' . $id);
}